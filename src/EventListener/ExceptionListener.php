<?php

namespace App\EventListener;

use Throwable;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\KernelEvents;

class ExceptionListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [KernelEvents::EXCEPTION => 'onKernelException'];
    }

    public function onKernelException(ExceptionEvent $event)
    {
        if (!$this->isJsonContentTypeOrAccept($event)) {
            return;
        }

        $response = new JsonResponse(
            ['message' => $event->getThrowable()->getMessage()],
            $this->getStatusCode($event->getThrowable())
        );
        $response->headers->set('Content-Type', 'application/problem+json');

        $event->setResponse($response);
    }

    private function getStatusCode(Throwable $throwable)
    {
        if ($throwable instanceof HttpException) {
            return $throwable->getStatusCode();
        }

        return $throwable->getCode() > 100 ? $throwable->getCode() : 500;
    }

    private function isJsonContentTypeOrAccept(ExceptionEvent $event)
    {
        return 'json' === $event->getRequest()->getContentType() || in_array('application/json', $event->getRequest()->getAcceptableContentTypes());
    }
}
