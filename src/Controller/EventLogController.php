<?php

namespace App\Controller;

use App\Form\SearchType;
use App\Repository\EventLogRepository;
use App\Sidebar\Annotation\Sidebar;
use APY\BreadcrumbTrailBundle\Annotation\Breadcrumb;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class EventLogController extends AbstractController
{
    #[Sidebar(active: ['event-log-nav'])]
    #[Breadcrumb(title: 'Journal des événements', routeName: 'event_log_index')]
    #[IsGranted("ROLE_SHOW_EVENT_LOG")]
    #[Route('/eventLog', name: 'event_log_index', methods: ['GET'])]
    public function index(EventLogRepository $eventLogRepository, PaginatorInterface $paginator, Request $request)
    {
        $formSearch = $this->createForm(SearchType::class);
        $eventLogs = $paginator->paginate(
            $eventLogRepository->findByStructure($this->getUser()->getStructure()->getId(), $request->query->get('search')),
            $request->query->getInt('page', 1),
            $this->getParameter('limit_line_table'),
            [
                'defaultSortFieldName' => ['el.createdAt'],
                'defaultSortDirection' => 'desc',
            ]
        );

        return $this->render('/eventLog/index.html.twig', [
            'eventLogs' => $eventLogs,
            'formSearch' => $formSearch->createView(),
            'searchTerm' => $request->query->get('search'),
            'timezone' => $this->getUser()->getStructure()->getTimezone()->getName(),
        ]);
    }
}
