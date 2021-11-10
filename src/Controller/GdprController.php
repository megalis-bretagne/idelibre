<?php

namespace App\Controller;

use App\Form\GdprHostingType;
use App\Service\Gdpr\GdprManager;
use App\Sidebar\Annotation\Sidebar;
use APY\BreadcrumbTrailBundle\Annotation\Breadcrumb;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Breadcrumb("Notice RGPD")
 */
#[Sidebar(active: ['platform-nav', 'gdpr-nav'])]
class GdprController extends AbstractController
{
    #[Route(path: '/gdpr/notice', name: 'gdpr_notice')]
    #[Sidebar(reset: true)]
    public function notice(GdprManager $gdprManager): Response
    {
        return $this->render('gdpr/notice.html.twig', [
            'gdpr' => $gdprManager->getGdpr(),
        ]);
    }

    /**
     * @Breadcrumb("Modifier")
     */
    #[Route(path: '/gdpr/edit', name: 'gdpr_edit')]
    #[IsGranted(data: 'ROLE_SUPERADMIN')]
    public function edit(GdprManager $gdprManager, Request $request): Response
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
}
