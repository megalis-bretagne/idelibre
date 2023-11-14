<?php

namespace App\Controller;

use App\Entity\Party;
use App\Form\PartyType;
use App\Repository\PartyRepository;
use App\Service\Party\PartyManager;
use App\Sidebar\Annotation\Sidebar;
use APY\BreadcrumbTrailBundle\Annotation\Breadcrumb;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Sidebar(active: ['party-nav'])]
#[Breadcrumb(title: 'Groupes politiques')]
class PartyController extends AbstractController
{
    #[Route(path: '/party/index', name: 'party_index')]
    #[IsGranted('ROLE_MANAGE_PARTIES')]
    public function index(PartyRepository $partyRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $parties = $paginator->paginate(
            $partyRepository->findByStructure($this->getUser()->getStructure()),
            $request->query->getInt('page', 1),
            $this->getParameter('limit_line_table'),
            [
                'defaultSortFieldName' => ['p.name'],
                'defaultSortDirection' => 'asc',
            ]
        );

        return $this->render('party/index.html.twig', [
            'parties' => $parties,
        ]);
    }

    #[Route(path: '/party/add', name: 'party_add')]
    #[IsGranted('ROLE_MANAGE_PARTIES')]
    #[Breadcrumb(title: 'Ajouter')]
    public function add(Request $request, PartyManager $partyManager): Response
    {
        $form = $this->createForm(PartyType::class, null, ['structure' => $this->getUser()->getStructure()]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $partyManager->save($form->getData(), $this->getUser()->getStructure());
            $this->addFlash('success', 'Votre groupe politique a été ajouté');

            return $this->redirectToRoute('party_index');
        }

        return $this->render('party/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/party/edit/{id}', name: 'party_edit')]
    #[IsGranted('MANAGE_PARTIES', subject: 'party')]
    #[Breadcrumb(title: 'Modification du groupe politique {party.name}')]
    public function edit(Party $party, Request $request, PartyManager $partyManager): Response
    {
        $form = $this->createForm(PartyType::class, $party, ['structure' => $this->getUser()->getStructure()]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $partyManager->update($form->getData(), $this->getUser()->getStructure());
            $this->addFlash('success', 'Votre groupe politique a été modifié');

            return $this->redirectToRoute('party_index');
        }

        return $this->render('party/edit.html.twig', [
            'form' => $form->createView(),
            'title' => 'Modification du groupe politique ' . $party->getName(),
        ]);
    }

    #[Route(path: '/party/delete/{id}', name: 'party_delete')]
    #[IsGranted('MANAGE_PARTIES', subject: 'party')]
    public function delete(Party $party, PartyManager $partyManager, Request $request): Response
    {
        $partyManager->delete($party);
        $this->addFlash('success', 'Le groupe politique a bien été supprimé');

        return $this->redirectToRoute('party_index', [
            'page' => $request->get('page'),
        ]);
    }
}
