<?php

namespace App\Controller;

use App\Service\Gdpr\GdprManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class GdprController extends AbstractController
{
    /**
     * @Route("/gdpr/notice", name="gdpr_notice")
     */
    public function notice(GdprManager $gdprManager)
    {
        return $this->render('gdpr/notice.html.twig', [
            'gdpr' => $gdprManager->getGdpr()
        ]);
    }


    /**
     * @Route("/gdpr/edit", name="gdpr_edit")
     */
    public function edit(GdprManager $gdprManager)
    {

            $gdprManager->getGdpr()

    }



}
