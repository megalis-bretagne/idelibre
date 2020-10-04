<?php

namespace App\Controller;

use App\Form\PartyType;
use App\Repository\PartyRepository;
use App\Service\Party\PartyManager;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PartyController extends AbstractController
{
    /**
     * @Route("/party/index", name="party_index")
     * @IsGranted("ROLE_MANAGE_USERS")
     */
    public function index(PartyRepository $partyRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $parties = $paginator->paginate(
            $partyRepository->findByStructure($this->getUser()->getStructure()),
            $request->query->getInt('page', 1),
            20,
            [
                'defaultSortFieldName' => ['p.name'],
                'defaultSortDirection' => 'asc',
            ]
        );

        return $this->render('party/index.html.twig', [
            'parties' => $parties,
        ]);
    }

    /**
     * @Route("/party/add", name="party_add")
     * @IsGranted("ROLE_MANAGE_USERS")
     */
    public function add(Request $request, PartyManager $partyManager): Response
    {
        $form = $this->createForm(PartyType::class, null, ['structure' => $this->getUser()->getStructure()]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $partyManager->save($form->getData(), $this->getUser()->getStructure());
        }

        return $this->render('party/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/party/edit", name="party_edit")
     */
    public function edit()
    {

    }

    /**
     * @Route("/party/delete", name="party_delete")
     */
    public function delete()
    {

    }

}
