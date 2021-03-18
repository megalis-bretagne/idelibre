<?php

namespace App\Controller;

use App\Entity\Structure;
use App\Repository\SittingRepository;
use App\Sidebar\Annotation\Sidebar;
use APY\BreadcrumbTrailBundle\Annotation\Breadcrumb;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Sidebar(active={"board-nav"})
 */
class BoardController extends AbstractController
{
    /**
     * @Breadcrumb("Tableau de bord", routeName="board_index")
     * @Route("/board", name="board_index")
     * @IsGranted("ROLE_MANAGE_SITTINGS")
     */
    public function index(SittingRepository $sittingRepository, PaginatorInterface $paginator, Request $request): Response
    {
        /** @var Structure $structure */
        $structure = $this->getUser()->getStructure();

        $sittings = $paginator->paginate(
            $sittingRepository->findActiveFromStructure($structure),
            $request->query->getInt('page', 1),
            20,
            [
                'defaultSortFieldName' => ['s.date'],
                'defaultSortDirection' => 'desc',
            ]
        );

        return $this->render('board/index.html.twig', [
            'sittings' => $sittings,
            'timezone' => $structure->getTimezone()->getName(),
        ]);
    }
}