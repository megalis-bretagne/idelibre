<?php

namespace App\Sidebar\Listener;

use App\Sidebar\Annotation\Sidebar;
use App\Sidebar\State\SidebarState;
use Doctrine\Common\Annotations\Reader;
use ReflectionClass;
use ReflectionException;
use RuntimeException;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Twig\Environment;

class SidebarListener
{
    private Environment $twig;
    private Reader $annotationReader;
    private array $activeNavs = [];
    private SidebarState $sidebarState;

    public function __construct(Environment $twig, Reader $annotationReader, SidebarState $sidebarState)
    {
        $this->twig = $twig;
        $this->annotationReader = $annotationReader;
        $this->sidebarState = $sidebarState;
    }

    public function onKernelRequest(ControllerEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        // Retourne un callable dnc un tableau sous cette forme [class, methode]
        $controllers = $event->getController();
        if (!is_array($controllers)) {
            return;
        }

        $this->handleAnnotation($controllers);

        $this->sidebarState->addActiveNavs($this->activeNavs);
    }

    private function handleAnnotation(iterable $controllers)
    {
        list($controller, $method) = $controllers;

        try {
            $controller = new ReflectionClass($controller);
        } catch (ReflectionException $e) {
            throw new RuntimeException('Failed to read annotation!');
        }

        $this->setActiveNav($this->annotationReader->getClassAnnotations($controller));

        $method = $controller->getMethod($method);
        $this->setActiveNav($this->annotationReader->getMethodAnnotations($method));
    }

    private function setActiveNav(array $annotations)
    {
        foreach ($annotations as $annotation) {
            if (!($annotation instanceof Sidebar)) {
                continue;
            }
            if ($annotation->reset) {
                $this->activeNavs = [];
            }
            $this->activeNavs = array_merge($this->activeNavs, $annotation->active);
        }
    }
}
