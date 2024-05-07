<?php

namespace App\Controller\Easy;

use App\Repository\SittingRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class EasySittingController extends AbstractController
{

    #[Route(path: '/easy/sitting', name: 'easy_sitting_index')]
    #[IsGranted('ROLE_ACTOR')]
    public function index(PaginatorInterface $paginator, Request $request, SittingRepository $sittingRepository): Response
    {

        $sittings = $paginator->paginate(
            $sittingRepository->findActiveSittingByUser($this->getUser()),
            $request->query->getInt('page', 1),
            $this->getParameter('limit_line_table'),
            [
                'defaultSortFieldName' => ['s.date'],
                'defaultSortDirection' => 'desc',
            ]
        );


        return $this->render('easy/sitting/index.html.twig', [
            'sittings' => $sittings,
            'timezone' => $this->getUser()->getStructure()->getTimezone()->getName(),
        ]);
    }



}