<?php

namespace App\Controller;

use App\Entity\EmailTemplate;
use App\Form\EmailTemplateType;
use App\Repository\EmailTemplateRepository;
use App\Service\Email\EmailData;
use App\Service\EmailTemplate\EmailGenerator;
use App\Service\EmailTemplate\EmailTemplateManager;
use App\Sidebar\Annotation\Sidebar;
use APY\BreadcrumbTrailBundle\Annotation\Breadcrumb;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Sidebar(active: ['email-template-nav'])]
#[Breadcrumb(title: "Modèles d'email", routeName: 'email_template_index')]
class EmailTemplateController extends AbstractController
{
    #[Route(path: '/emailTemplate', name: 'email_template_index', methods: ['GET'])]
    #[IsGranted(data: 'ROLE_MANAGE_EMAIL_TEMPLATES')]
    public function index(EmailTemplateRepository $repository, PaginatorInterface $paginator, Request $request): Response
    {
        $emailTemplates = $paginator->paginate(
            $repository->findAllByStructure($this->getUser()->getStructure()),
            $request->query->getInt('page', 1),
            $this->getParameter('limit_line_table'),
            [
                'defaultSortFieldName' => ['et.name'],
                'defaultSortDirection' => 'asc',
            ]
        );

        return $this->render('email_template/index.html.twig', [
            'templates' => $emailTemplates,
        ]);
    }

    #[Route(path: '/emailTemplate/add', name: 'email_template_add')]
    #[IsGranted(data: 'ROLE_MANAGE_EMAIL_TEMPLATES')]
    #[Breadcrumb(title: 'Ajouter')]
    public function add(Request $request, EmailTemplateManager $templateManager): Response
    {
        $form = $this->createForm(EmailTemplateType::class, null, ['structure' => $this->getUser()->getStructure()]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $templateManager->save($form->getData());
            $this->addFlash('success', 'Votre modèle d\'email a été enregistré');

            return $this->redirectToRoute('email_template_index');
        }

        return $this->render('email_template/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/emailTemplate/edit/{id}', name: 'email_template_edit', methods: ['GET', 'POST'])]
    #[IsGranted(data: 'MANAGE_EMAIL_TEMPLATES', subject: 'emailTemplate')]
    #[Breadcrumb(title: 'Modifier {emailTemplate.name}')]
    public function edit(Request $request, EmailTemplate $emailTemplate, EmailTemplateManager $templateManager): Response
    {
        $form = $this->createForm(EmailTemplateType::class, $emailTemplate, ['structure' => $this->getUser()->getStructure()]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $templateManager->save($form->getData());
            $this->addFlash('success', 'Votre modèle d\'email a été modifié');

            return $this->redirectToRoute('email_template_index');
        }

        return $this->render('email_template/edit.html.twig', [
            'email_template' => $emailTemplate,
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/emailTemplate/delete/{id}', name: 'email_template_delete', methods: ['DELETE'])]
    #[IsGranted(data: 'MANAGE_EMAIL_TEMPLATES', subject: 'emailTemplate')]
    public function delete(EmailTemplate $emailTemplate, EmailTemplateManager $emailTemplateManager, Request $request): Response
    {
        $emailTemplateManager->delete($emailTemplate);
        $this->addFlash('success', 'Le modèle d\'email a bien été supprimé');

        return $this->redirectToRoute('email_template_index', [
            'page' => $request->get('page'),
        ]);
    }

    #[Route(path: '/emailTemplate/preview/{id}', name: 'email_template_preview', methods: ['GET'])]
    #[IsGranted(data: 'MANAGE_EMAIL_TEMPLATES', subject: 'emailTemplate')]
    #[Breadcrumb(title: 'Visualiser {emailTemplate.name}')]
    public function preview(EmailTemplate $emailTemplate, EmailGenerator $generator, ): Response
    {
        $emailData = $generator->generateFromTemplate($emailTemplate, [
            '#linkUrl#' => '<a href="#">Accéder aux dossiers</a>',
            '#urlseance#' => '<a href="#">idelibre.example.fr/idelibre_client</a>',
            '#reinitLink#' => '<a href="#">Réinitialiser le mot de passe</a>',
            '#typeseance#' => 'Conseil municipal',
            '#dateseance#' => '05/12/2020',
            '#heureseance#' => '20h30',
            '#lieuseance#' => 'Salle du conseil',
            '#prenom#' => 'Thomas',
            '#nom#' => 'Dupont',
            '#titre#' => 'Monsieur le Maire',
            '#civilite#' => 'Monsieur',
        ]);
        $subject = $emailData->getSubject();

        return $this->render('email_template/preview.html.twig', [
            'emailTemplate' => $emailTemplate,
            'subject' => $subject,
        ]);
    }

    #[Route(path: '/emailTemplate/iframe/preview/{id}', name: 'email_template_iframe_preview', methods: ['GET'])]
    #[IsGranted(data: 'MANAGE_EMAIL_TEMPLATES', subject: 'emailTemplate')]
    public function iframePreview(EmailTemplate $emailTemplate, EmailGenerator $generator): Response
    {
        $recapitulatif = $this->tableExample();

        $emailData = $generator->generateFromTemplate($emailTemplate, [
            '#linkUrl#' => '<a href="#">Accéder aux dossiers</a>',
            '#urlseance#' => '<a href="#">idelibre.example.fr/idelibre_client</a>',
            '#reinitLink#' => '<a href="#">Réinitialiser le mot de passe</a>',
            '#typeseance#' => 'Conseil municipal',
            '#dateseance#' => '05/12/2020',
            '#heureseance#' => '20h30',
            '#lieuseance#' => 'Salle du conseil',
            '#prenom#' => 'Thomas',
            '#nom#' => 'Dupont',
            '#titre#' => 'Monsieur le Maire',
            '#civilite#' => 'Monsieur',
            '#recapitulatif#' => $recapitulatif,
        ]);
        $content = $emailData->getContent();
        if (EmailData::FORMAT_TEXT === $emailData->getFormat()) {
            $content = htmlspecialchars($content);
            $content = nl2br($content);
        }

        return new Response($content);
    }

    public function tableExample() {
        return "<style>
                table {
                    border:1px solid black
                }
                thead, tr, td, th {
                    border-bottom: 1px solid black;
                    border-left: 1px solid black;
                }
                td, tr {
                    padding:12px
                }
                td {
                    border-left: 1px solid black;
                }
                td:first-child {
                    border-left:none
                }
                tbody > tr:last-child > td {
                    border-bottom: none;
                }
            </style>
            <legend>Séance Conseil Municipal du 23/12/2022 à 12:00</legend>
            <table>
                <thead>
                    <tr>
                        <th>Utilisateur</th>
                        <th>Statut</th>
                        <th>Mandataire</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Michel DURANT</td>
                        <td>Présent</td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>Emilie DIL</td>
                        <td>Non renseigné</td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>Jean-Paul LABA</td>
                        <td>Absent</td>
                        <td>Eric POLO</td>
                    </tr>
                </tbody>
            </table>
            <br />
            <legend>Séance Assemblée Générale du 05/01/2023 à 14:00</legend>
            <table>
                <thead>
                    <tr>
                        <th>Utilisateur</th>
                        <th>Statut</th>
                        <th>Mandataire</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Michel DURANT</td>
                        <td>Non renseigné</td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>Emilie DIL</td>
                        <td>Présent</td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>Jean-Paul LABA</td>
                        <td>Présent</td>
                        <td></td>
                    </tr>
                </tbody>
            </table>";
    }
}
