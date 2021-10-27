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
        if (!$event->isMainRequest()) {
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
        $this->setActiveNav($controller->getAttributes(Sidebar::class));

        try {
            $method = $controller->getMethod($method);
        } catch (ReflectionException $e) {
            throw new RuntimeException('Failed to read annotation!');
        }

        $this->setActiveNav($method->getAttributes(Sidebar::class));
    }

    private function setActiveNav(array $attributes)
    {
        /** @var \ReflectionAttribute $attribute */
        foreach ($attributes as $attribute) {
            if ($attribute->getName() !== Sidebar::class) {
                continue;
            }
            $sidebar = $attribute->newInstance();
            if ($sidebar->reset) {
                $this->activeNavs = [];
            }
            $this->activeNavs = array_merge($this->activeNavs, $sidebar->active);
        }
    }
}
