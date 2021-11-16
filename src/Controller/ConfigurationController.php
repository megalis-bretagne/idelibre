<?php

namespace App\Controller;

use App\Entity\Structure;
use App\Form\ConfigurationType;
use App\Service\Configuration\ConfigurationManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ConfigurationController extends AbstractController
{

    #[Route('/configuration', name: 'configuration_index')]
    #[IsGranted('ROLE_MANAGE_CONFIGURATION')]
    public function index(ConfigurationManager $configurationManager): Response
    {
        /** @var Structure $structure */
        $structure = $this->getUser()->getStructure();

        return $this->render('configuration/index.html.twig', [
            'configuration' => $structure->getConfiguration()
        ]);
    }



    #[Route('/configuration/edit', name: 'configuration_edit')]
    #[IsGranted('ROLE_MANAGE_CONFIGURATION')]
    public function edit(ConfigurationManager $configurationManager, Request $request): Response
    {
        /** @var Structure $structure */
        $structure = $this->getUser()->getStructure();

        $form = $this->createForm(ConfigurationType::class, $structure->getConfiguration(), ['structure' => $structure]);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $configurationManager->update($form->getData());
            $this->addFlash('success', 'La configuration a été mise à jour');
            return $this->redirectToRoute('configuration_index');
        }


        return $this->render('configuration/edit.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
