<?php

namespace App\Controller;

use App\Form\SearchType;
use App\Repository\TypeRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TypeController extends AbstractController
{
    /**
     * @Route("/admin", name="admin_index")
     * @IsGranted("ROLE_MANAGE_STRUCTURES")
     *
     */
    public function index(TypeRepository $typeRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $formSearch = $this->createForm(SearchType::class);

        $admins = $paginator->paginate(
            $typeRepository->findByStructure($this->getUser()->getStructure()),
            $request->query->getInt('page', 1),
            20,
            [
                'defaultSortFieldName' => ['t.name'],
                'defaultSortDirection' => 'asc',
            ]
        );

        return $this->render('admin/index.html.twig', [
            'users' => $admins,
            'formSearch' => $formSearch->createView(),
            'searchTerm' => $request->query->get('search') ?? ''
        ]);
    }
}
