<?php

namespace App\Controller;

use App\Form\SearchType;
use App\Repository\SittingRepository;
use APY\BreadcrumbTrailBundle\Annotation\Breadcrumb;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Breadcrumb("Seance", routeName="sitting_index")
 *
 */
class SittingController extends AbstractController
{
    /**
     * @Route("/sitting/index", name="sitting_index")
     * @IsGranted("ROLE_MANAGE_SEANCES")
     */
    public function index(SittingRepository $sittingRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $formSearch = $this->createForm(SearchType::class);

        $sittings = $paginator->paginate(
            $sittingRepository->findByStructure($this->getUser()->getStructure(), $request->query->get('search')),
            $request->query->getInt('page', 1),
            20,
            [
                'defaultSortFieldName' => ['s.date'],
                'defaultSortDirection' => 'desc',
            ]
        );

        return $this->render('sitting/index.html.twig', [
            'sittings' => $sittings,
            'formSearch' => $formSearch->createView(),
            'searchTerm' => $request->query->get('search') ?? ''
        ]);

    }

    /**
     * @Route("/sitting/add", name="sitting_add")
     * @IsGranted("ROLE_MANAGE_SEANCES")
     */
    public function add()
    {
    }


    /**
     * @Route("/sitting/edit/{id}", name="sitting_edit")
     * @IsGranted("ROLE_MANAGE_SEANCES")
     */
    public function edit()
    {
    }


    /**
     * @Route("/sitting/delete/{id}", name="sitting_delete", methods={"DELETE"})
     * @IsGranted("ROLE_MANAGE_SEANCES")
     */
    public function delete()
    {
    }
}
