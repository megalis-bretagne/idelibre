<?php

namespace App\Controller;

use App\Service\ClientNotifier\ClientNotifier;
use App\Service\Email\EmailData;
use App\Service\Email\EmailServiceInterface;
use App\Service\ServiceInfo\ServiceInfo;
use App\Sidebar\Annotation\Sidebar;
use APY\BreadcrumbTrailBundle\Annotation\Breadcrumb;
use Libriciel\LshorodatageApiWrapper\LsHorodatageException;
use Libriciel\LshorodatageApiWrapper\LshorodatageInterface;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Sidebar(active={"platform-nav","check-nav"})
 */
class CheckController extends AbstractController
{
    /**
     * @Route("/check", name="check_index")
     * @IsGranted("ROLE_SUPERADMIN")
     * @Breadcrumb("Vérification de la plateforme")
     */
    public function index(
        ClientNotifier $clientNotifier,
        LshorodatageInterface $lshorodatage,
        LoggerInterface $logger,
        ServiceInfo $serviceInfo
    ): Response
    {
        $isNodejs = $clientNotifier->checkConnection();
        $isLshorodatage = true;
        try {
            $lshorodatage->ping();
        } catch (LsHorodatageException $e) {
            $isLshorodatage = false;
            $logger->error($e->getMessage());
        }

        return $this->render('check/index.html.twig', [
            'isNodejs' => $isNodejs,
            'isLshorodatage' => $isLshorodatage,
            'phpConfig' => $serviceInfo->getPhpConfiguration(),
        ]);
    }

    /**
     * @Route("/check/email", name="check_email", methods={"POST"})
     * @IsGranted("ROLE_SUPERADMIN")
     */
    public function testMail(Request $request, EmailServiceInterface $emailService, ParameterBagInterface $bag): Response
    {
        $email = $request->request->get('email');
        $emailData = new EmailData('Test email idelibre', 'email de verification', EmailData::FORMAT_TEXT);
        $emailData->setTo($email)->setReplyTo($bag->get('email_from'));
        $emailService->sendBatch([$emailData]);
        $this->addFlash('success', 'Email de vérification envoyé');

        return $this->redirectToRoute('check_index');
    }
}
