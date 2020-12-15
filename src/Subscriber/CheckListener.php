<?php

namespace App\Subscriber;

use App\Annotation\Model;
use Doctrine\Common\Annotations\Reader;
use ReflectionClass;
use ReflectionException;
use RuntimeException;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Twig\Environment;

class CheckListener
{
    private Environment $twig;
    private Reader $annotationReader;

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


        $myVar = 'foo'; // Process data
        $this->twig->addGlobal('myVar', $myVar);
    }

    private function handleAnnotation(iterable $controllers)
    {
        list($controller, $method) = $controllers;

        try {
            $controller = new ReflectionClass($controller);
        } catch (ReflectionException $e) {
            throw new RuntimeException('Failed to read annotation!');
        }

        $this->handleClassAnnotation($controller);
        $this->handleMethodAnnotation($controller, $method);

        dd('over');

    }

    private function handleClassAnnotation(ReflectionClass $controller)
    {
        $annotation = $this->annotationReader->getClassAnnotation($controller, Model::class);

        if ($annotation instanceof Model) {
            dump($annotation);
        }
    }

    private function handleMethodAnnotation(ReflectionClass $controller, $method)
    {
        $method = $controller->getMethod($method);
        $annotation = $this->annotationReader->getMethodAnnotation($method, Model::class);

        if ($annotation instanceof Model) {
            dump($annotation);
        }
    }
}
