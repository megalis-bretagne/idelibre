<?php

namespace App\Controller;

use App\Entity\EmailTemplate;
use App\Form\EmailTemplateType;
use App\Repository\EmailTemplateRepository;
use App\Service\Email\EmailGenerator;
use App\Service\EmailTemplate\DefaultTemplateCreator;
use App\Service\EmailTemplate\EmailTemplateManager;
use APY\BreadcrumbTrailBundle\Annotation\Breadcrumb;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Breadcrumb("Modèle d'email", routeName="email_template_index")
 */
class EmailTemplateController extends AbstractController
{
    /**
     * @Route("/emailTemplate", name="email_template_index", methods={"GET"})
     * @IsGranted("ROLE_MANAGE_EMAIL_TEMPLATES")
     */
    public function index(EmailTemplateRepository $repository, PaginatorInterface $paginator, Request $request): Response
    {
        $emailTemplates = $paginator->paginate(
            $repository->findAllByStructure($this->getUser()->getStructure()),
            $request->query->getInt('page', 1),
            20,
            [
                'defaultSortFieldName' => ['et.name'],
                'defaultSortDirection' => 'asc',
            ]
        );

        return $this->render('email_template/index.html.twig', [
            'templates' => $emailTemplates,
        ]);
    }

    /**
     * @Route("/emailTemplate/add", name="email_template_add")
     * @IsGranted("ROLE_MANAGE_EMAIL_TEMPLATES")
     * @Breadcrumb("Ajouter")
     */
    public function add(Request $request, EmailTemplateManager $templateManager, DefaultTemplateCreator $defaultTemplateCreator, EntityManagerInterface $em): Response
    {
        /*
                $defaultTemplateCreator->initDefaultTemplates($this->getUser()->getStructure());
                $em->flush();

                dd('OK');
        */
        $form = $this->createForm(EmailTemplateType::class, null, ['structure' => $this->getUser()->getStructure()]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $templateManager->save($form->getData(), $this->getUser()->getStructure());
            $this->addFlash('success', 'Votre modèle d\'email a été enregistré');
            return $this->redirectToRoute('email_template_index');
        }

        return $this->render('email_template/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/emailTemplate/edit/{id}", name="email_template_edit", methods={"GET","POST"})
     * @IsGranted("MANAGE_EMAIL_TEMPLATES", subject="emailTemplate")
     * @Breadcrumb("Modifier {emailTemplate.name}")
     */
    public function edit(Request $request, EmailTemplate $emailTemplate, EmailTemplateManager $templateManager): Response
    {
        $form = $this->createForm(EmailTemplateType::class, $emailTemplate, ['structure' => $this->getUser()->getStructure()]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $templateManager->save($form->getData(), $this->getUser()->getStructure());
            $this->addFlash('success', 'Votre template d\'email a été modifié');
            return $this->redirectToRoute('email_template_index');
        }

        return $this->render('email_template/edit.html.twig', [
            'email_template' => $emailTemplate,
            'form' => $form->createView(),
        ]);
    }


    /**
     * @Route("/emailTemplate/delete/{id}", name="email_template_delete", methods={"DELETE"})
     * @IsGranted("MANAGE_EMAIL_TEMPLATES", subject="emailTemplate")
     */
    public function delete(EmailTemplate $emailTemplate, EmailTemplateManager $emailTemplateManager, Request $request): Response
    {
        $emailTemplateManager->delete($emailTemplate);
        $this->addFlash('success', 'Le modèle d\'email a bien été supprimé');
        return $this->redirectToRoute('email_template_index', [
            'page' => $request->get('page')
        ]);
    }

    /**
     * @Route("/emailTemplate/preview/{id}", name="email_template_preview", methods={"GET"})
     * @IsGranted("MANAGE_EMAIL_TEMPLATES", subject="emailTemplate")
     * @Breadcrumb("Visualiser {emailTemplate.name}")
     */
    public function preview(EmailTemplate $emailTemplate): Response
    {
        return $this->render('email_template/preview.html.twig', [
            'emailTemplate' => $emailTemplate,
        ]);
    }

    /**
     * @Route("/emailTemplate/iframe/preview/{id}", name="email_template_iframe_preview", methods={"GET"})
     * @IsGranted("MANAGE_EMAIL_TEMPLATES", subject="emailTemplate")
     */
    public function iframePreview(EmailTemplate $emailTemplate, EmailGenerator $generator): Response
    {
        $emailData = $generator->generateNotification($emailTemplate, [
            '#linkUrl#' => '<a href="#">Accéder aux dossiers</a>',
            '#reinitLink#' => '<a href="#">Réinitialiser le mot de passe</a>',
            '#typeseance#' => 'Conseil municipal',
            '#dateseance#' => '05/12/2020',
            '#heureseance#' => '20h30',
            '#lieuseance#' => 'Salle du conseil',
            '#prenom#' => 'Thomas',
            '#nom#' => 'Dupont',
            '#titre#' => 'Monsieur le Maire',
            '#civilite#' => 'Monsieur'
        ]);

        return new Response($emailData->getContent());
    }
}
