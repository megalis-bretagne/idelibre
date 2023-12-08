<?php

namespace App\Controller;

use App\Entity\Gdpr\DataControllerGdpr;
use App\Entity\Structure;
use App\Form\DataControllerGdprType;
use App\Form\GdprHostingType;
use App\Service\Gdpr\DataControllerManager;
use App\Service\Gdpr\GdprManager;
use App\Sidebar\Annotation\Sidebar;
use APY\BreadcrumbTrailBundle\Annotation\Breadcrumb;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Sidebar(active: ['platform-nav', 'gdpr-nav'])]
#[Breadcrumb(title: 'Notice RGPD', routeName: 'gdpr_notice')]
class GdprController extends AbstractController
{
    #[Route(path: '/gdpr/notice', name: 'gdpr_notice')]
    #[Sidebar(reset: true)]
    #[IsGranted('ROLE_DEFAULT')]
    public function notice(GdprManager $gdprManager): Response
    {
        /** @var Structure $structure */
        $structure = $this->getUser()->getStructure();

        return $this->render('gdpr/notice.html.twig', [
            'gdprHosting' => $gdprManager->getGdpr(),
            'dataController' => $structure ? $structure->getDataControllerGdpr() : new DataControllerGdpr(),
        ]);
    }

    #[Route(path: '/gdpr/editHosting', name: 'gdpr_edit')]
    #[IsGranted('ROLE_SUPERADMIN')]
    #[Breadcrumb(title: 'Modifier')]
    public function editHosting(GdprManager $gdprManager, Request $request): Response
    {
        $form = $this->createForm(GdprHostingType::class, $gdprManager->getGdpr());
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $gdprManager->save($form->getData());

            $this->addFlash('success', 'Vos informations RGPD ont été mises à jour');

            return $this->redirectToRoute('gdpr_notice');
        }

        return $this->render('gdpr/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/gdpr/editController', name: 'gdpr_controller_edit')]
    #[IsGranted('ROLE_MANAGE_GDPR')]
    #[Sidebar(active: ['gdpr-data-controller-nav'], reset: true)]
    #[Breadcrumb(title: 'Modifier')]
    public function editDataController(Request $request, DataControllerManager $dataControllerManager): Response
    {
        /** @var Structure $structure */
        $structure = $this->getUser()->getStructure();

        $form = $this->createForm(DataControllerGdprType::class, $structure->getDataControllerGdpr());
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $dataControllerManager->save($form->getData(), $structure);

            $this->addFlash('success', 'Vos informations RGPD ont été mises à jour');

            return $this->redirectToRoute('gdpr_notice');
        }

        return $this->render('gdpr/editDataController.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
