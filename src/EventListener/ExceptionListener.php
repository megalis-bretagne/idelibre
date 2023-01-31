<?php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Throwable;

class ExceptionListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::EXCEPTION => 'onKernelException'];
    }

    public function onKernelException(ExceptionEvent $event)
    {
        if (!$this->shouldReturnJson($event)) {
            return;
        }

        $response = new JsonResponse(
            ['message' => $event->getThrowable()->getMessage()],
            $this->getStatusCode($event->getThrowable())
        );
        $response->headers->set('Content-Type', 'application/problem+json');

        $event->setResponse($response);
    }

    private function getStatusCode(Throwable $throwable): int
    {
        if ($throwable instanceof HttpException) {
            return $throwable->getStatusCode();
        }

        return $throwable->getCode() > 100 ? $throwable->getCode() : 500;
    }

    private function shouldReturnJson(ExceptionEvent $event): bool
    {
        if (str_starts_with($event->getRequest()->getPathInfo(), '/api/v2')) {
            return true;
        }

        return 'json' === $event->getRequest()->getContentTypeFormat() || in_array('application/json', $event->getRequest()->getAcceptableContentTypes());
    }
}
