<?php

namespace App\Controller;

use App\Entity\Sitting;
use App\Form\SearchType;
use App\Form\SittingType;
use App\Repository\EmailTemplateRepository;
use App\Repository\LsvoteConnectorRepository;
use App\Repository\OtherdocRepository;
use App\Repository\ProjectRepository;
use App\Service\Connector\LsvoteConnectorManager;
use App\Service\EmailTemplate\EmailGenerator;
use App\Service\File\Generator\FileGenerator;
use App\Service\File\Generator\UnsupportedExtensionException;
use App\Service\Pdf\PdfValidator;
use App\Service\Seance\SittingManager;
use App\Sidebar\Annotation\Sidebar;
use App\Sidebar\State\SidebarState;
use APY\BreadcrumbTrailBundle\Annotation\Breadcrumb;
use Knp\Component\Pager\PaginatorInterface;
use phpDocumentor\Reflection\Types\This;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpClient\Exception\InvalidArgumentException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;

#[Sidebar(active: ['sitting-nav'])]
#[Breadcrumb(title: 'Séances', routeName: 'sitting_index')]
class SittingController extends AbstractController
{
    #[Route(path: '/sitting', name: 'sitting_index')]
    #[IsGranted(data: 'ROLE_MANAGE_SITTINGS')]
    public function index(PaginatorInterface $paginator, Request $request, SittingManager $sittingManager, SidebarState $sidebarState): Response
    {
        $formSearch = $this->createForm(SearchType::class);
        $sittings = $paginator->paginate(
            $sittingManager->getListSittingByStructureQuery($this->getUser(), $request->query->get('search'), $request->query->get('status')),
            $request->query->getInt('page', 1),
            $this->getParameter('limit_line_table'),
            [
                'defaultSortFieldName' => ['s.date'],
                'defaultSortDirection' => 'desc',
            ]
        );
        if ($status = $request->query->get('status')) {
            $sidebarState->setActiveNavs(['sitting-nav', "sitting-$status-nav"]);
        }

        return $this->render('sitting/index.html.twig', [
            'sittings' => $sittings,
            'formSearch' => $formSearch->createView(),
            'searchTerm' => $request->query->get('search') ?? '',
            'timezone' => $this->getUser()->getStructure()->getTimezone()->getName(),
        ]);
    }

    #[Route(path: '/sitting/add', name: 'sitting_add')]
    #[IsGranted(data: 'ROLE_MANAGE_SITTINGS')]
    #[Sidebar(active: ['sitting-active-nav'])]
    #[Breadcrumb(title: 'Ajouter')]
    public function createSitting(Request $request, SittingManager $sittingManager, PdfValidator $pdfValidator): Response
    {
        $form = $this->createForm(SittingType::class, null, ['structure' => $this->getUser()->getStructure(), 'user' => $this->getUser()]);
        $form->handleRequest($request);

        $unreadablePdf = $pdfValidator->getListOfUnreadablePdf([
            $form->get('convocationFile')->getData(),
            $form->get('invitationFile')->getData(),
        ]);

        if (count($unreadablePdf) > 0) {
            $this->addFlash('error', 'Fichier(s) invalide(s) :  ' . implode(', ', $unreadablePdf));

            return $this->redirectToRoute('sitting_add');
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $sittingId = $sittingManager->save(
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
    #[IsGranted(data: 'ROLE_MANAGE_SITTINGS')]
    #[Sidebar(active: ['sitting-active-nav'])]
    #[Breadcrumb(title: 'Modifier {sitting.nameWithDate}')]
    public function editUsers(Sitting $sitting): Response
    {
        if ($sitting->getIsArchived()) {
            throw new InvalidArgumentException('Impossible de modifier une séance archivée');
        }

        return $this->render('sitting/edit_actors.html.twig', [
            'sitting' => $sitting,
        ]);
    }

    #[Route(path: '/sitting/edit/{id}/projects', name: 'edit_sitting_project')]
    #[Sidebar(active: ['sitting-active-nav'])]
    #[Breadcrumb(title: 'Modifier {sitting.nameWithDate}')]
    public function editProjects(Sitting $sitting): Response
    {
        if ($sitting->getIsArchived()) {
            throw new InvalidArgumentException('Impossible de modifier une séance archivée');
        }

        return $this->render('sitting/edit_projects.html.twig', [
            'sitting' => $sitting,
        ]);
    }

    #[Route(path: '/sitting/edit/{id}', name: 'edit_sitting_information')]
    #[IsGranted(data: 'MANAGE_SITTINGS', subject: 'sitting')]
    #[Sidebar(active: ['sitting-active-nav'])]
    #[Breadcrumb(title: 'Modifier {sitting.nameWithDate}')]
    public function editInformation(Sitting $sitting, Request $request, SittingManager $sittingManager, PdfValidator $pdfValidator): Response
    {
        if ($sitting->getIsArchived()) {
            throw new InvalidArgumentException('Impossible de modifier une séance archivée');
        }
        $form = $this->createForm(SittingType::class, $sitting, ['structure' => $this->getUser()->getStructure()]);
        $form->handleRequest($request);

        $unreadablePdf = $pdfValidator->getListOfUnreadablePdf([
            $form->get('convocationFile')->getData(),
            $form->get('invitationFile')->getData(),
        ]);

        if (count($unreadablePdf) > 0) {
            $this->addFlash('error', 'Fichier(s) invalide(s) :  ' . implode(', ', $unreadablePdf));

            return $this->redirectToRoute('sitting_add');
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $sittingManager->update(
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
        ]);
    }

    #[Route(path: '/sitting/edit/{id}/cancel', name: 'edit_sitting_information_cancel')]
    #[IsGranted(data: 'MANAGE_SITTINGS', subject: 'sitting')]
    public function editInformationCancel(Sitting $sitting): Response
    {
        $this->addFlash('success', 'Modifications annulées');

        return $this->redirectToRoute('edit_sitting_information', ['id' => $sitting->getId()]);
    }

    #[Route(path: '/sitting/delete/{id}', name: 'sitting_delete', methods: ['DELETE'])]
    #[IsGranted(data: 'MANAGE_SITTINGS', subject: 'sitting')]
    public function delete(Sitting $sitting, SittingManager $sittingManager, Request $request): Response
    {
        $sittingManager->delete($sitting);
        $this->addFlash('success', 'La séance a bien été supprimée');
        $referer = $request->headers->get('referer');

        return $referer ? $this->redirect($referer) : $this->redirectToRoute('sitting_index');
    }

    #[Route(path: '/sitting/show/{id}/information', name: 'sitting_show_information', methods: ['GET'])]
    #[IsGranted(data: 'MANAGE_SITTINGS', subject: 'sitting')]
    #[Breadcrumb(title: 'Détail {sitting.nameWithDate}')]
    public function showInformation(Sitting $sitting, SittingManager $sittingManager, SidebarState $sidebarState): Response
    {
        $sidebarState->setActiveNavs(['sitting-nav', $this->activeSidebarNav($sitting->getIsArchived())]);

        return $this->render('sitting/details_information.html.twig', [
            'isAlreadySent' => $sittingManager->isAlreadySent($sitting),
            'sitting' => $sitting,
            'timezone' => $sitting->getStructure()->getTimezone()->getName(),
        ]);
    }

    #[Route(path: '/sitting/show/{id}/actors', name: 'sitting_show_actors', methods: ['GET'])]
    #[IsGranted(data: 'MANAGE_SITTINGS', subject: 'sitting')]
    #[Breadcrumb(title: 'Détail {sitting.nameWithDate}')]
    public function showActors(Sitting $sitting, EmailTemplateRepository $emailTemplateRepository, SidebarState $sidebarState, EmailGenerator $emailGenerator): Response
    {
        $sidebarState->setActiveNavs(['sitting-nav', $this->activeSidebarNav($sitting->getIsArchived())]);

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
        ]);
    }

    #[Route(path: '/sitting/show/{id}/projects', name: 'sitting_show_projects', methods: ['GET'])]
    #[IsGranted(data: 'MANAGE_SITTINGS', subject: 'sitting')]
    #[Breadcrumb(title: 'Détail {sitting.nameWithDate}')]
    public function showProjects(Sitting $sitting, ProjectRepository $projectRepository, OtherdocRepository $otherdocRepository, SidebarState $sidebarState, SittingManager $sittingManager, ParameterBagInterface $bag): Response
    {
        $sidebarState->setActiveNavs(['sitting-nav', $this->activeSidebarNav($sitting->getIsArchived())]);

        return $this->render('sitting/details_projects.html.twig', [
            'sitting' => $sitting,
            'projects' => $projectRepository->getProjectsWithAssociatedEntities($sitting),
            'totalSize' => $sittingManager->getProjectsAndAnnexesTotalSize($sitting),
            'otherdocs' => $otherdocRepository->getOtherdocsWithAssociatedEntities($sitting),
            'otherdocsTotalSize' => $sittingManager->getOtherDocsTotalSize($sitting),
            'isProjectsSizeTooBig' => $sittingManager->getProjectsAndAnnexesTotalSize($sitting) > intval($bag->get('maximum_size_pdf_zip_generation')),
        ]);
    }

    /**
     * @throws UnsupportedExtensionException
     */
    #[Route(path: '/sitting/zip/{id}', name: 'sitting_zip', methods: ['GET'])]
    #[IsGranted(data: 'MANAGE_SITTINGS', subject: 'sitting')]
    public function getZipSitting(Sitting $sitting, FileGenerator $fileGenerator): Response
    {
        $zipPath = $fileGenerator->genFullSittingDirPath($sitting, 'zip');
        $response = new BinaryFileResponse($zipPath);
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $fileGenerator->createPrettyName($sitting, 'zip')
        );
        $response->headers->set('X-Accel-Redirect', $zipPath);

        return $response;
    }

    #[Route(path: '/sitting/pdf/{id}', name: 'sitting_full_pdf', methods: ['GET'])]
    #[IsGranted(data: 'MANAGE_SITTINGS', subject: 'sitting')]
    public function getFullPdfSitting(Sitting $sitting, FileGenerator $fileGenerator): Response
    {
        $pdfPath = $fileGenerator->genFullSittingDirPath($sitting, 'pdf');
        $response = new BinaryFileResponse($pdfPath);
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $fileGenerator->createPrettyName($sitting, 'pdf')
        );
        $response->headers->set('X-Accel-Redirect', $pdfPath);

        return $response;
    }

    #[Route(path: '/sitting/archive/{id}', name: 'sitting_archive', methods: ['POST'])]
    #[IsGranted(data: 'MANAGE_SITTINGS', subject: 'sitting')]
    public function archiveSitting(Sitting $sitting, SittingManager $sittingManager, Request $request): Response
    {
        $sittingManager->archive($sitting);
        $this->addFlash('success', 'La séance a été classée');
        $referer = $request->headers->get('referer');

        return $referer ? $this->redirect($referer) : $this->redirectToRoute('sitting_index');
    }

    #[Route(path: '/sitting/unarchive/{id}', name: 'sitting_unarchive', methods: ['POST'])]
    #[IsGranted(data: 'ROLE_SUPERADMIN')]
    public function unArchiveSitting(Sitting $sitting, SittingManager $sittingManager, Request $request): Response
    {
        $sittingManager->unArchive($sitting);
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

    #[Route(path: '/sitting/{id}/sendLsvote', name: 'sitting_sendLsvote', methods: ['GET'])]
    #[IsGranted(data: 'ROLE_SUPERADMIN')]
    public function sendToLsvote(Sitting $sitting, LsvoteConnectorManager $lsvoteConnectorManager, LsvoteConnectorRepository $lsvoteConnectorRepository): Response
    {
        $connector = $lsvoteConnectorRepository->findOneBy(["structure" => $this->getUser()->getStructure()]);
        $url = $connector->getUrl();
        $apiKey = $connector->getApiKey();

        $lsvoteConnectorManager->createSitting($url, $apiKey, $sitting);

        return $this->redirectToRoute('sitting_index', []);
    }

    public function deleteLsvoteSitting(Sitting $sitting, LsvoteConnectorManager $lsvoteConnectorManager)
    {
        $lsvoteConnectorManager->deleteSitting($sitting);
        return $this->redirectToRoute('sitting_index');
    }

}
