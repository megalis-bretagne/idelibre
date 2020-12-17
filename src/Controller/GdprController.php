<?php

namespace App\Controller;

use App\Annotation\Sidebar;
use App\Form\GdprType;
use App\Service\Gdpr\GdprManager;
use APY\BreadcrumbTrailBundle\Annotation\Breadcrumb;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Breadcrumb("Notice RGPD")
 * @Sidebar(active={"platform-nav","gdpr-nav"})
 */
class GdprController extends AbstractController
{
    /**
     * @Route("/gdpr/notice", name="gdpr_notice")
     * @Sidebar(reset=true)
     */
    public function notice(GdprManager $gdprManager): Response
    {
        return $this->render('gdpr/notice.html.twig', [
            'gdpr' => $gdprManager->getGdpr(),
        ]);
    }

    /**
     * @Route("/gdpr/edit", name="gdpr_edit")
     * @IsGranted("ROLE_SUPERADMIN")
     * @Breadcrumb("Modifier")
     */
    public function edit(GdprManager $gdprManager, Request $request): Response
    {
        $form = $this->createForm(GdprType::class, $gdprManager->getGdpr());
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