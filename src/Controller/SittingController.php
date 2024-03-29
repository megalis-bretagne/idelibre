<?php

namespace App\Controller;

use App\Entity\Connector\LsvoteConnector;
use App\Entity\Sitting;
use App\Form\SearchType;
use App\Form\SittingType;
use App\Repository\EmailTemplateRepository;
use App\Repository\LsvoteConnectorRepository;
use App\Repository\OtherdocRepository;
use App\Repository\ProjectRepository;
use App\Repository\UserRepository;
use App\Service\Connector\Lsvote\LsvoteException;
use App\Service\Connector\LsvoteConnectorManager;
use App\Service\Connector\LsvoteResultException;
use App\Service\Convocation\ConvocationManager;
use App\Service\EmailTemplate\EmailGenerator;
use App\Service\File\Generator\FileGenerator;
use App\Service\File\Generator\UnsupportedExtensionException;
use App\Service\Pdf\PdfValidator;
use App\Service\Seance\SittingManager;
use App\Service\Util\FileUtil;
use App\Sidebar\Annotation\Sidebar;
use App\Sidebar\State\SidebarState;
use APY\BreadcrumbTrailBundle\Annotation\Breadcrumb;
use APY\BreadcrumbTrailBundle\BreadcrumbTrail\Trail;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpClient\Exception\InvalidArgumentException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Sidebar(active: ['sitting-nav'])]
#[Breadcrumb(title: 'Séances', routeName: 'sitting_index')]
class SittingController extends AbstractController
{
    public function __construct(
        private readonly ConvocationManager $convocationManager,
        private readonly SittingManager $sittingManager,
        private readonly PdfValidator $pdfValidator,
        private readonly LsvoteConnectorManager $lsvoteConnectorManager,
        private readonly SidebarState $sidebarState,
        private readonly FileGenerator $fileGenerator,
    ) {
    }

    #[Route(path: '/sitting', name: 'sitting_index')]
    #[IsGranted('ROLE_MANAGE_SITTINGS')]
    public function index(PaginatorInterface $paginator, Request $request, Trail $breadcrumbTrail): Response
    {
        $request->get('status') === Sitting::ARCHIVED ? $breadcrumbTrail->add("Classées") : $breadcrumbTrail->add("En cours");

        $formSearch = $this->createForm(SearchType::class);
        $sittings = $paginator->paginate(
            $this->sittingManager->getListSittingByStructureQuery($this->getUser(), $request->query->get('search'), $request->query->get('status')),
            $request->query->getInt('page', 1),
            $this->getParameter('limit_line_table'),
            [
                'defaultSortFieldName' => ['s.date'],
                'defaultSortDirection' => 'desc',
            ]
        );
        if ($status = $request->query->get('status')) {
            $this->sidebarState->setActiveNavs(['sitting-nav', "sitting-$status-nav"]);
        }

        return $this->render('sitting/index.html.twig', [
            'sittings' => $sittings,
            'formSearch' => $formSearch->createView(),
            'searchTerm' => $request->query->get('search') ?? '',
            'timezone' => $this->getUser()->getStructure()->getTimezone()->getName(),
        ]);
    }

    #[Route(path: '/sitting/add', name: 'sitting_add')]
    #[IsGranted('ROLE_MANAGE_SITTINGS')]
    #[Sidebar(active: ['sitting-active-nav'])]
    #[Breadcrumb(title: 'Ajouter une séance')]
    public function createSitting(Request $request): Response
    {
        $form = $this->createForm(SittingType::class, null, ['structure' => $this->getUser()->getStructure(), 'user' => $this->getUser()]);
        $form->handleRequest($request);

        $unreadablePdf = $this->pdfValidator->getListOfUnreadablePdf([
            $form->get('convocationFile')->getData(),
            $form->get('invitationFile')->getData(),
        ]);

        if (count($unreadablePdf) > 0) {
            $this->addFlash('error', 'Fichier(s) invalide(s) :  ' . implode(', ', $unreadablePdf));

            return $this->redirectToRoute('sitting_add');
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $sittingId = $this->sittingManager->save(
                $form->getData(),
                $form->get('convocationFile')->getData(),
                $form->get('invitationFile')->getData(),
                $this->getUser()->getStructure(),
                $form->get('reminder')->getData()
            );

            return $this->redirectToRoute('edit_sitting_actor', ['id' => $sittingId]);
        }

        return $this->render('sitting/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/sitting/edit/{id}/actors', name: 'edit_sitting_actor', methods: ['GET'])]
    #[IsGranted('ROLE_MANAGE_SITTINGS')]
    #[Sidebar(active: ['sitting-active-nav'])]
    #[Breadcrumb(title: 'Modification des destinataires de la séance {sitting.nameWithDate}')]
    public function editUsers(Sitting $sitting): Response
    {
        if ($sitting->getIsArchived()) {
            throw new InvalidArgumentException('Impossible de modifier une séance archivée');
        }

        return $this->render('sitting/edit_actors.html.twig', [
            'sitting' => $sitting,
            'title' => 'Modification des destinataires de la séance ' . $sitting->getNameWithDate(),
        ]);
    }

    #[Route(path: '/sitting/edit/{id}/projects', name: 'edit_sitting_project')]
    #[Sidebar(active: ['sitting-active-nav'])]
    #[Breadcrumb(title: 'Modification des projets de la séance {sitting.nameWithDate}')]
    public function editProjects(Sitting $sitting): Response
    {
        if ($sitting->getIsArchived()) {
            throw new InvalidArgumentException('Impossible de modifier une séance archivée');
        }

        return $this->render('sitting/edit_projects.html.twig', [
            'sitting' => $sitting,
            'title' => 'Modification des projets de la séance ' . $sitting->getNameWithDate(),
        ]);
    }

    #[Route(path: '/sitting/edit/{id}', name: 'edit_sitting_information')]
    #[IsGranted('MANAGE_SITTINGS', subject: 'sitting')]
    #[Sidebar(active: ['sitting-active-nav'])]
    #[Breadcrumb(title: 'Modification des informations de la séance {sitting.nameWithDate}')]
    public function editInformation(Sitting $sitting, Request $request): Response
    {
        if ($sitting->getIsArchived()) {
            throw new InvalidArgumentException('Impossible de modifier une séance archivée');
        }
        $form = $this->createForm(SittingType::class, $sitting, [
            'structure' => $this->getUser()->getStructure(),
        ]);
        $form->handleRequest($request);

        $unreadablePdf = $this->pdfValidator->getListOfUnreadablePdf([
            $form->get('convocationFile')->getData(),
            $form->get('invitationFile')->getData(),
        ]);

        if (count($unreadablePdf) > 0) {
            $this->addFlash('error', 'Fichier(s) invalide(s) :  ' . implode(', ', $unreadablePdf));

            return $this->redirectToRoute('sitting_add');
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $this->sittingManager->update(
                $form->getData(),
                $form->get('convocationFile')->getData(),
                $form->get('invitationFile')->getData(),
            );

            $this->addFlash('success', 'Modifications enregistrées');

            return $this->redirectToRoute('sitting_show_information', ['id' => $sitting->getId()]);
        }

        return $this->render('sitting/edit_information.html.twig', [
            'form' => $form->createView(),
            'sitting' => $sitting,
            'title' => 'Modification des informations de la séance ' . $sitting->getNameWithDate(),
        ]);
    }

    #[Route(path: '/sitting/edit/{id}/cancel', name: 'edit_sitting_information_cancel')]
    #[IsGranted('MANAGE_SITTINGS', subject: 'sitting')]
    public function editInformationCancel(Sitting $sitting): Response
    {
        $this->addFlash('success', 'Modifications annulées');

        return $this->redirectToRoute('edit_sitting_information', ['id' => $sitting->getId()]);
    }

    #[Route(path: '/sitting/delete/{id}', name: 'sitting_delete', methods: ['DELETE'])]
    #[IsGranted('MANAGE_SITTINGS', subject: 'sitting')]
    public function delete(Sitting $sitting, Request $request): Response
    {
        $this->sittingManager->delete($sitting);
        $this->addFlash('success', 'La séance a bien été supprimée');
        $referer = $request->headers->get('referer');

        return $referer ? $this->redirect($referer) : $this->redirectToRoute('sitting_index');
    }

    #[Route(path: '/sitting/show/{id}/information', name: 'sitting_show_information', methods: ['GET'])]
    #[IsGranted('MANAGE_SITTINGS', subject: 'sitting')]
    #[Breadcrumb(title: 'Détail {sitting.nameWithDate}')]
    public function showInformation(Sitting $sitting, ParameterBagInterface $bag): Response
    {
        $this->sidebarState->setActiveNavs(['sitting-nav', $this->activeSidebarNav($sitting->getIsArchived())]);

        return $this->render('sitting/details_information.html.twig', [
            'isAlreadySent' => $this->sittingManager->isAlreadySent($sitting),
            'sitting' => $sitting,
            'isActiveLsvote' => $this->lsvoteConnectorManager->getLsvoteConnector($sitting->getStructure())->getActive(),
            'isLsvoteResults' => !empty($sitting->getLsvoteSitting()?->getResults()),
            'isSentLsvote' => !empty($sitting->getLsvoteSitting()),
            'timezone' => $sitting->getStructure()->getTimezone()->getName(),
            'isTotalSizeTooBig' => $this->sittingManager->getAllFilesSize($sitting) > intval($bag->get('maximum_size_pdf_zip_generation')),
            'totalFilesSize' => $this->sittingManager->getAllFilesSize($sitting),
        ]);
    }

    #[Route(path: '/sitting/show/{id}/actors', name: 'sitting_show_actors', methods: ['GET'])]
    #[IsGranted('MANAGE_SITTINGS', subject: 'sitting')]
    #[Breadcrumb(title: 'Détail {sitting.nameWithDate}')]
    public function showActors(Sitting $sitting, EmailTemplateRepository $emailTemplateRepository, EmailGenerator $emailGenerator): Response
    {
        $this->sidebarState->setActiveNavs(['sitting-nav', $this->activeSidebarNav($sitting->getIsArchived())]);

        $emailTemplate = $emailTemplateRepository->findOneByStructureAndCategory($sitting->getStructure(), 'convocation');
        $emailTemplateBySittingType = $emailTemplateRepository->findOneByStructureAndCategoryAndType($sitting->getStructure(), $sitting->getType(), 'convocation');
        $emailTemplateInvitation = $emailTemplateRepository->findOneByStructureAndCategory($sitting->getStructure(), 'invitation');

        $subjectGenerated = $emailGenerator->generateEmailTemplateSubject($sitting, $emailTemplate->getSubject());
        $subjectInvitation = $emailGenerator->generateEmailTemplateSubject($sitting, $emailTemplateInvitation->getSubject());

        $subjectBySittingTypeGenerated = '';
        if (isset($emailTemplateBySittingType)) {
            $subjectBySittingTypeGenerated = $emailGenerator->generateEmailTemplateSubject($sitting, $emailTemplateBySittingType->getSubject());
        }

        return $this->render('sitting/details_actors.html.twig', [
            'sitting' => $sitting,
            'emailTemplate' => $emailTemplate,
            'emailTemplateBySittingType' => $emailTemplateBySittingType,
            'emailTemplateInvitation' => $emailTemplateInvitation,
            'subjectGenerated' => $subjectGenerated,
            'subjectBySittingTypeGenerated' => $subjectBySittingTypeGenerated,
            'subjectInvitation' => $subjectInvitation,
            'isActiveLsvote' => $this->lsvoteConnectorManager->getLsvoteConnector($sitting->getStructure())->getActive(),
            'convocationNotAnswered' => $this->convocationManager->countConvocationNotanswered($sitting->getConvocations())
        ]);
    }

    #[Route(path: '/sitting/show/{id}/projects', name: 'sitting_show_projects', methods: ['GET'])]
    #[IsGranted('MANAGE_SITTINGS', subject: 'sitting')]
    #[Breadcrumb(title: 'Détail {sitting.nameWithDate}')]
    public function showProjects(Sitting $sitting, ProjectRepository $projectRepository, OtherdocRepository $otherdocRepository, ParameterBagInterface $bag): Response
    {
        $this->sidebarState->setActiveNavs(['sitting-nav', $this->activeSidebarNav($sitting->getIsArchived())]);

        return $this->render('sitting/details_projects.html.twig', [
            'sitting' => $sitting,
            'projects' => $projectRepository->getProjectsWithAssociatedEntities($sitting),
            'otherdocs' => $otherdocRepository->getOtherdocsWithAssociatedEntities($sitting),
            'projectsFilesSize' => $this->sittingManager->getProjectsAndAnnexesTotalSize($sitting),
            'otherdocsFilesSize' => $this->sittingManager->getOtherDocsTotalSize($sitting),
            'totalFilesSize' => $this->sittingManager->getAllFilesSize($sitting),
            'isProjectsSizeTooBig' => $this->sittingManager->getProjectsAndAnnexesTotalSize($sitting) > intval($bag->get('maximum_size_pdf_zip_generation')),
            'isOthersSizeTooBig' => $this->sittingManager->getOtherDocsTotalSize($sitting) > intval($bag->get('maximum_size_pdf_zip_generation')),
            'isTotalSizeTooBig' => $this->sittingManager->getAllFilesSize($sitting) > intval($bag->get('maximum_size_pdf_zip_generation')),
        ]);
    }

    /**
     * @throws UnsupportedExtensionException
     */
    #[Route(path: '/sitting/zip/{id}', name: 'sitting_zip', methods: ['GET'])]
    #[IsGranted('MANAGE_SITTINGS', subject: 'sitting')]
    public function getZipSitting(Sitting $sitting): Response
    {
        $zipPath = $this->fileGenerator->genFullSittingDirPath($sitting, 'zip');
        $response = new BinaryFileResponse($zipPath);
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $this->fileGenerator->createPrettyName($sitting, 'zip')
        );
        $response->headers->set('X-Accel-Redirect', $zipPath);

        return $response;
    }

    #[Route(path: '/sitting/pdf/{id}', name: 'sitting_full_pdf', methods: ['GET'])]
    #[IsGranted('MANAGE_SITTINGS', subject: 'sitting')]
    public function getFullPdfSitting(Sitting $sitting): Response
    {
        $pdfPath = $this->fileGenerator->genFullSittingDirPath($sitting, 'pdf');
        $response = new BinaryFileResponse($pdfPath);
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $this->fileGenerator->createPrettyName($sitting, 'pdf')
        );
        $response->headers->set('X-Accel-Redirect', $pdfPath);

        return $response;
    }

    #[Route(path: '/sitting/archive/{id}', name: 'sitting_archive', methods: ['POST'])]
    #[IsGranted('MANAGE_SITTINGS', subject: 'sitting')]
    public function archiveSitting(Sitting $sitting, Request $request): Response
    {
        $this->sittingManager->archive($sitting);

        $this->addFlash('success', 'La séance a été classée');
        $referer = $request->headers->get('referer');

        return $referer ? $this->redirect($referer) : $this->redirectToRoute('sitting_index');
    }

    #[Route(path: '/sitting/unarchive/{id}', name: 'sitting_unarchive', methods: ['POST'])]
    #[IsGranted('ROLE_SUPERADMIN')]
    public function unArchiveSitting(Sitting $sitting, Request $request): Response
    {
        $this->sittingManager->unArchive($sitting);
        $this->addFlash('success', 'La séance a été déclassée');
        $referer = $request->headers->get('referer');

        return $referer ? $this->redirect($referer) : $this->redirectToRoute('sitting_index');
    }

    private function activeSidebarNav(bool $isArchived): string
    {
        if ($isArchived) {
            return 'sitting-archived-nav';
        }

        return 'sitting-active-nav';
    }


    #[Route(path: '/sitting/{id}/lsvote-results', name: 'sitting_lsvote_results', methods: ['GET'])]
    #[IsGranted('ROLE_MANAGE_SITTINGS')]
    public function getLsvoteResults(Sitting $sitting, Request $request): Response
    {
        try {
            $this->lsvoteConnectorManager->getLsvoteSittingResults($sitting);
        } catch (LsvoteResultException $e) {
            $this->addFlash('error', $e->getMessage());

            return $this->redirect($request->headers->get('referer'));
        }

        $this->addFlash('success', 'Les résultats ont bien été récupérés depuis lsvote');

        return $this->redirect($request->headers->get('referer'));
    }


    #[Route('/sitting/{id}/list/actors', name: 'sitting_actors_list', methods: ['GET'])]
    public function getAllActorsList(UserRepository $userRepository): Response
    {
        return $this->render('sitting/includes/_list_actors.html.twig', [
            "actors" => $userRepository->findActorsInStructure($this->getUser()->getStructure())->getQuery()->getResult(),
        ]);
    }

    #[Route('sitting/{id}/information/removeInvitation')]
    #[IsGranted('MANAGE_SITTINGS', subject: 'sitting')]
    public function removeInvitationFile(Sitting $sitting): JsonResponse
    {
        if ($this->sittingManager->isAlreadySent($sitting)) {
            throw new BadRequestException();
        }
        $this->sittingManager->removeInvitationFile($sitting);
        return $this->json(['success' => true]);
    }


    #[Route(path: '/sitting/{id}/lsvote-results/json', name: 'sitting_lsvote_results_json', methods: ['GET'])]
    #[IsGranted('ROLE_MANAGE_SITTINGS')]
    public function downloadLsvoteResultsCsv(Sitting $sitting): Response
    {
        $jsonPath = $this->lsvoteConnectorManager->createJsonFile($sitting);

        $response = new BinaryFileResponse($jsonPath);
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            'lsvote_results_' . $this->fileGenerator->createPrettyName($sitting, "json")
        );

        $response->deleteFileAfterSend();

        return $response;
    }

    /**
     * @throws LsvoteException
     */
    #[Route(path: '/sitting/{id}/lsvote-results/pdf', name: 'sitting_lsvote_results_pdf', methods: ['GET'])]
    #[IsGranted('ROLE_MANAGE_SITTINGS')]
    public function fetchLsvoteResultPdf(Sitting $sitting, LsvoteConnectorRepository $lsvoteConnectorRepository): Response
    {
        $lsvoteConnector = $lsvoteConnectorRepository->findOneBy(["structure" => $sitting->getStructure()]);
        $pdfPath = $this->lsvoteConnectorManager->fetchLsvoteResultsPdf($lsvoteConnector, $sitting);

        $response = new BinaryFileResponse($pdfPath);
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            'lsvote_results_' . $this->fileGenerator->createPrettyName($sitting, "pdf")
        );
        $response->deleteFileAfterSend();

        return $response;
    }
}
