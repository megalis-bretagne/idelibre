<?php

namespace App\Service\Email;

use App\Entity\Sitting;
use Eluceo\iCal\Domain\Entity\Calendar;
use Eluceo\iCal\Domain\Entity\Event;
use Eluceo\iCal\Domain\ValueObject\Date as IcalDate;
use Eluceo\iCal\Domain\ValueObject\DateTime;
use Eluceo\iCal\Domain\ValueObject\EmailAddress;
use Eluceo\iCal\Domain\ValueObject\Organizer;
use Eluceo\iCal\Domain\ValueObject\SingleDay;
use Eluceo\iCal\Domain\ValueObject\TimeSpan;
use Eluceo\iCal\Presentation\Factory\CalendarFactory;
use Ramsey\Uuid\Uuid;

class IcalGenerator
{
    public const CONTENT_TYPE = 'text/calendar';

    public function generate(Sitting $sitting): string
    {
        $mutableDateTime = new \DateTime();
        $mutableDateTime->setTimestamp($sitting->getDate()->getTimestamp());

        // 1. Create Event domain entity
        $event = (new Event())
            ->setSummary($sitting->getName())
            ->setDescription($sitting->getName())
            ->setOccurrence(
                new TimeSpan(
                    new DateTime($sitting->getDate(), true),
                    new DateTime(date_add($mutableDateTime, date_interval_create_from_date_string('2 hours')), true )
                )
            )
        ->setOrganizer(new Organizer( new EmailAddress($sitting->getStructure()->getReplyTo()) ,$sitting->getStructure()->getName()));

        $calendar = new Calendar([$event]);
        $calendar->setProductIdentifier('idelibre');


        $componentFactory = new CalendarFactory();
        $calendarComponent = $componentFactory->createCalendar($calendar);

        $fileName = '/tmp/' . Uuid::NAMESPACE_DNS;

        file_put_contents($fileName, $calendarComponent);

        return $fileName;
    }
}
