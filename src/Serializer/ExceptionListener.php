<?php

namespace App\Serializer;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
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
            $event->getThrowable()->getCode() > 100 ? $event->getThrowable()->getCode() : 500
        );
        $response->headers->set('Content-Type', 'application/problem+json');


        $event->setResponse($response);

    }

    private function isJsonContentTypeOrAccept(ExceptionEvent $event)
    {
        return in_array('application/json', $event->getRequest()->getAcceptableContentTypes()) ||
            $event->getRequest()->getContentType() === 'application/json' ;
    }

}
