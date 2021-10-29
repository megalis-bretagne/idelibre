<?php

namespace App\Controller;

use App\Entity\ApiUser;
use App\Form\ApiUserType;
use App\Repository\ApiUserRepository;
use App\Sidebar\Annotation\Sidebar;
use APY\BreadcrumbTrailBundle\Annotation\Breadcrumb;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Sidebar(active: ['connector-nav'])]
class ApiUserController extends AbstractController
{
    /**
     * @Breadcrumb("Clé d API")
     */
    #[Route(path: '/apikey', name: 'apiUser_index')]
    #[IsGranted(data: 'ROLE_MANAGE_API_USER')]
    public function index(PaginatorInterface $paginator, ApiUserRepository $apiUserRepository, Request $request): Response
    {
        $apiUsers = $paginator->paginate(
            $apiUserRepository->findByStructure($this->getUser()->getStructure()),
            $request->query->getInt('page', 1),
            20,
            [
                'defaultSortFieldName' => ['au.name'],
                'defaultSortDirection' => 'asc',
            ]
        );

        return $this->render('apiUser/index.html.twig', [
            'apiUsers' => $apiUsers,
        ]);
    }

    #[Route(path: '/apikey/add', name: 'apiUser_add')]
    public function add(Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(ApiUserType::class, null, ['structure' => $this->getUser()->getStructure()]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($form->getData());
            $em->flush();
            $this->addFlash('success', 'La Clé d\'api a été ajoutée');

            return $this->redirectToRoute('apiUser_index');
        }

        return $this->render('apiUser/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/apikey/edit/{id}', name: 'apiUser_edit')]
    public function edit(ApiUser $apiUser, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(ApiUserType::class, $apiUser, ['structure' => $this->getUser()->getStructure()]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($form->getData());
            $em->flush();
            $this->addFlash('success', 'La Clé d\'api a été ajoutée');

            return $this->redirectToRoute('apiUser_index');
        }

        return $this->render('apiUser/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }


    #[Route(path: '/apikey/delete', name: 'apiUser_delete')]
    public function delete(): Response
    {
        return new Response();
    }

}
