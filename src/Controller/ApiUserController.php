<?php

namespace App\Controller;

use App\Entity\ApiUser;
use App\Form\ApiUserType;
use App\Repository\ApiUserRepository;
use App\Sidebar\Annotation\Sidebar;
use App\Util\TokenUtil;
use APY\BreadcrumbTrailBundle\Annotation\Breadcrumb;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Breadcrumb("Clés d'api", routeName="apiUser_index")
 */
#[Sidebar(active: ['connector-nav', 'connector-api-key-nav'])]
class ApiUserController extends AbstractController
{
    #[Route(path: '/apikey', name: 'apiUser_index', methods: ['GET'])]
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

    /**
     * @Breadcrumb("Ajouter")
     */
    #[Route(path: '/apikey/add', name: 'apiUser_add', methods: ['GET', 'POST'])]
    #[IsGranted(data: 'ROLE_MANAGE_API_USER')]
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

    /**
     * @Breadcrumb("Modifier {apiUser.name}")
     */
    #[Route(path: '/apikey/edit/{id}', name: 'apiUser_edit', methods: ['PUT', 'GET', 'POST'])]
    #[IsGranted(data: 'MANAGE_API_USERS', subject: 'apiUser')]
    public function edit(ApiUser $apiUser, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(ApiUserType::class, $apiUser, ['structure' => $this->getUser()->getStructure()]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($form->getData());
            $em->flush();
            $this->addFlash('success', 'La clé d\'api a été modifiée');

            return $this->redirectToRoute('apiUser_index');
        }

        return $this->render('apiUser/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/apikey/delete/{id}', name: 'apiUser_delete', methods: ['DELETE'])]
    #[IsGranted(data: 'MANAGE_API_USERS', subject: 'apiUser')]
    public function delete(ApiUser $apiUser, EntityManagerInterface $em): Response
    {
        $em->remove($apiUser);
        $em->flush();
        $this->addFlash('success', 'La clé d\'api a été supprimée');

        return $this->redirectToRoute('apiUser_index');
    }

    #[Route(path: '/apikey/refresh', name: 'apiUser_refresh', methods: ['GET'])]
    public function refreshApiKey(): JsonResponse
    {
        return $this->json(["apiKey" => TokenUtil::genToken()]);
    }
}
