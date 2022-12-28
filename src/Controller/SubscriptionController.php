<?php

namespace App\Controller;

use App\Form\SubscriptionType;
use App\Service\User\UserManager;
use App\Sidebar\Annotation\Sidebar;
use APY\BreadcrumbTrailBundle\Annotation\Breadcrumb;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Sidebar(active: ['subscription-nav'])]
class SubscriptionController extends AbstractController
{
    #[Route(path: '/subscription', name: 'subscription_index')]
    #[IsGranted(data: 'ROLE_MANAGE_SITTINGS')]
    #[Breadcrumb(title: 'Abonnement aux notifications', routeName: 'subscription_index')]
    public function index(): Response
    {
        $user = $this->getUser();

        return $this->render('subscription/index.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/subscription/edit/{id}', name: 'subscription_edit')]
    #[IsGranted('ROLE_MANAGE_SITTINGS')]
    public function edit(UserManager $userManager, Request $request): Response
    {
        $user = $this->getUser();
        $structure = $this->getUser()->getStructure();
        $form = $this->createForm(SubscriptionType::class, $user, ['user' => $user]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $userManager->save($form->getData(), null, $structure);
            $this->addFlash('success', 'L\'abonnement a été mise à jour');

            return $this->redirectToRoute('subscription_index');
        }

        return $this->render('subscription/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
