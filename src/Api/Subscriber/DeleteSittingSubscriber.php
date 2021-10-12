<?php

namespace App\Api\Subscriber;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Entity\Sitting;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;

//plus pour une action à faire en cas de champ modifé etc..
final class DeleteSittingSubscriber implements EventSubscriberInterface
{
    private $mailer;

    public function __construct(\Swift_Mailer $mailer)
    {
        $this->mailer = $mailer;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::VIEW => ['deleteSitting', EventPriorities::POST_WRITE],
        ];
    }

    public function deleteSitting(ViewEvent $event): void
    {
        return;
        dump('deleteSitting');
        /** @var Sitting $sitting */
        $sitting = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();

        dump($sitting);
        dump($method);

        if (!$sitting instanceof Sitting || Request::METHOD_DELETE !== $method) {
            return;
        }

        //thing to delete when sitting is deleted
        dump($sitting->getConvocationFile());
    }
}
