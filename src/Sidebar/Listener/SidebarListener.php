<?php

namespace App\Sidebar\Listener;

use App\Sidebar\Annotation\Sidebar;
use App\Sidebar\State\SidebarState;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionException;
use Symfony\Component\HttpKernel\Event\ControllerEvent;

class SidebarListener
{
    private array $activeNavs = [];
    private SidebarState $sidebarState;

    public function __construct(SidebarState $sidebarState)
    {
        $this->sidebarState = $sidebarState;
    }

    public function onKernelRequest(ControllerEvent $event): void
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

        $this->sidebarState->setActiveNavs($this->activeNavs);
    }

    private function handleAnnotation(iterable $controllers): void
    {
        list($controller, $method) = $controllers;

        try {
            $controller = new ReflectionClass($controller);
        } catch (ReflectionException $e) {
            throw new SideBarAttributeException('Failed to read class annotation! : ' . $e->getMessage());
        }
        $this->setActiveNav($controller->getAttributes(Sidebar::class));

        try {
            $method = $controller->getMethod($method);
        } catch (ReflectionException $e) {
            throw new SideBarAttributeException('Failed to read method annotation!  ' . $e->getMessage());
        }

        $this->setActiveNav($method->getAttributes(Sidebar::class));
    }

    /**
     * @param ReflectionAttribute[] $attributes
     */
    private function setActiveNav(array $attributes): void
    {
        /** @var ReflectionAttribute $attribute */
        foreach ($attributes as $attribute) {
            if (Sidebar::class !== $attribute->getName()) {
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
