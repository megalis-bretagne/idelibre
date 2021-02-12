<?php

namespace App\Controller;

use Libriciel\LshorodatageApiWrapper\LsHorodatage;
use Libriciel\LshorodatageApiWrapper\LshorodatageInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CheckController extends AbstractController
{
    /**
     * @Route("/check", name="check")
     */
    public function index(LshorodatageInterface $lsHorodatage, Request $request): Response
    {
        $res = $lsHorodatage->createTimestampToken('/tmp/toto');

        dd($res->getContents());

        $form = $this->createFormBuilder()
            ->add('file', FileType::class)
            ->add('token', FileType::class)
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $file */
            $file = $form->get('file')->getData();
            $token = $form->get('token')->getData();

            $lsHorodatage->setUrl('http://lshorodatage:3000');

            //  $lsHorodatageApiWrapper->setUrl('http://lshorodatage:3000');
            //  $lsHorodatageApiWrapper->setApiKey('lshorodatage');
            //  $res = $lsHorodatageApiWrapper->check();
            //  $res = $lsHorodatageApiWrapper->createTimestampToken($file);
            //  dd($res);
            //dd($lsHorodatage->ping()->getContents());
            // $lsHorodatage->createTimestampToken($file->getRealPath());

            //$lsHorodatage->readTimestampToken($file->getRealPath());

            $res = $lsHorodatage->verifyTimestampToken($file->getRealPath(), $token->getRealPath());
            dd($res);
        }

        return $this->render('check/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
