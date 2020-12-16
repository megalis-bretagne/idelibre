<?php

namespace App\Subscriber;

use App\Annotation\Sidebar;
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

    public function __construct(Environment $twig, Reader $annotationReader)
    {
        $this->twig = $twig;
        $this->annotationReader = $annotationReader;
    }

    public function onKernelRequest(ControllerEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        // retourne un callable dnc un tableau sous cette forme [class, methode]
        $controllers = $event->getController();
        if (!is_array($controllers)) {
            return;
        }

        $this->handleAnnotation($controllers);

        $this->twig->addGlobal('sidebar', $this->activeNavs);
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

    private function handleClassAnnotation(ReflectionClass $controller)
    {
        $annotation = $this->annotationReader->getClassAnnotation($controller, Sidebar::class);

        if ($annotation instanceof Sidebar) {
            $this->activeNavs = array_merge($this->activeNavs, $annotation->active);
        }
    }

    private function handleMethodAnnotation(ReflectionClass $controller, $method)
    {
        $method = $controller->getMethod($method);
        $annotations = $this->annotationReader->getMethodAnnotations($method);

        foreach ($annotations as $annotation) {
            if (!($annotation instanceof Sidebar)) {
                continue;
            }
            if ($annotation->reset) {
                $this->activeNavs = [];
            }
            $this->activeNavs = array_merge($this->activeNavs, $annotation->active);
        }

        dd($this->activeNavs);
    }
}
