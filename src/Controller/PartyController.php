<?php

namespace App\Controller;

use App\Annotation\Sidebar;
use App\Entity\Party;
use App\Form\PartyType;
use App\Repository\PartyRepository;
use App\Service\Party\PartyManager;
use APY\BreadcrumbTrailBundle\Annotation\Breadcrumb;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Breadcrumb("Groupes politiques")
 * @Sidebar(active={"party-nav"})
 */
class PartyController extends AbstractController
{
    /**
     * @Route("/party/index", name="party_index")
     * @IsGranted("ROLE_MANAGE_PARTIES")
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
     * @IsGranted("ROLE_MANAGE_PARTIES")
     * @Breadcrumb("Ajouter")
     */
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

    /**
     * @Route("/party/edit/{id}", name="party_edit")
     * @IsGranted("MANAGE_PARTIES", subject="party")
     * @Breadcrumb("Modifier {party.name}")
     */
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
        ]);
    }

    /**
     * @Route("/party/delete/{id}", name="party_delete")
     * @IsGranted("MANAGE_PARTIES", subject="party")
     */
    public function delete(Party $party, PartyManager $partyManager, Request $request): Response
    {
        $partyManager->delete($party);
        $this->addFlash('success', 'le groupe politique a bien été supprimé');

        return $this->redirectToRoute('party_index', [
            'page' => $request->get('page'),
        ]);
    }
}
