<?php

namespace App\Controller;

use App\Entity\Type;
use App\Repository\TypeRepository;
use App\Service\ClientNotifier\ClientNotifier;
use App\Service\Email\EmailData;
use App\Service\Email\EmailServiceInterface;
use App\Service\ServiceInfo\ServiceInfo;
use App\Sidebar\Annotation\Sidebar;
use APY\BreadcrumbTrailBundle\Annotation\Breadcrumb;
use Doctrine\ORM\EntityManagerInterface;
use Libriciel\LshorodatageApiWrapper\LsHorodatageException;
use Libriciel\LshorodatageApiWrapper\LshorodatageInterface;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Validator\Exception\ValidatorException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

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
    ): Response {
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

    #[Route('/check/serial', name: 'check_serial')]
    public function serial(DenormalizerInterface $denormalizer, TypeRepository $typeRepository, EntityManagerInterface $em, ValidatorInterface $validator): JsonResponse
    {
        $format = 'application/json';
        $type = Type::class;
        $data = ['name' => ''];

        $typeObj = $typeRepository->findAll()[0];
        dump($typeObj);
        $context = ['object_to_populate' => $typeObj, 'groups' => ['type:write']];

        $updatedType = $denormalizer->denormalize($data, $type, $format, $context);

        $res = $validator->validate(($updatedType));

        if ($res) {
            throw new ValidatorException('mon message');
        }

        $em->persist($updatedType);
        $em->flush();

        dd($updatedType);
        dump($denormalizer);
        dd('ok');
    }
}
