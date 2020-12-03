<?php

namespace App\Controller\Connector;

use App\Form\Connector\LsmessageConnectorType;
use App\Repository\Connector\LsmessageConnectorRepository;
use App\Service\Connector\LsmessageConnectorManager;
use APY\BreadcrumbTrailBundle\Annotation\Breadcrumb;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Breadcrumb("Configuration des connecteurs", routeName="connector_index")
 *
 */
class LsmessageConnectorController extends AbstractController
{
    /**
     * @Route("/connector/lsmessage", name="lsmessage_connector")
     * @IsGranted("ROLE_MANAGE_CONNECTORS")
     * @Breadcrumb("Lsmessage")
     */
    public function edit(LsmessageConnectorRepository $lsmessageConnectorRepository, LsmessageConnectorManager $lsmessageConnectorManager,
                         Request $request): Response
    {
        $connector = $lsmessageConnectorRepository->findOneBy(['structure' => $this->getUser()->getStructure()]);

        $form = $this->createForm(LsmessageConnectorType::class, $connector);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $lsmessageConnectorManager->save($form->getData());
            $this->addFlash('success', 'Le connecteur a bien été modifié');
            return $this->redirectToRoute('connector_index');
        }

        return $this->render('connector/lsmessage.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
