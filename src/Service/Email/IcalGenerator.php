<?php


namespace App\Service\Email;


use App\Entity\Sitting;
use DateTimeImmutable;
use Eluceo\iCal\Domain\Entity\Calendar;
use Eluceo\iCal\Domain\Entity\Event;
use Eluceo\iCal\Domain\ValueObject\Date as IcalDate;
use Eluceo\iCal\Domain\ValueObject\SingleDay;
use Eluceo\iCal\Presentation\Factory\CalendarFactory;
use Ramsey\Uuid\Uuid;

class IcalGenerator
{
    const CONTENT_TYPE = 'text/calendar';

    public function generate(Sitting $sitting): string
    {
        // 1. Create Event domain entity
        $event = (new Event())
            ->setSummary($sitting->getName())
            ->setDescription($sitting->getName())
            ->setOccurrence(
                new SingleDay(
                    new IcalDate($sitting->getDate())
                )
            );

        $calendar = new Calendar([$event]);
        $calendar->setProductIdentifier("idelibre");

        $componentFactory = new CalendarFactory();
        $calendarComponent = $componentFactory->createCalendar($calendar);

        $fileName = '/tmp/' . Uuid::NAMESPACE_DNS;

        file_put_contents($fileName, $calendarComponent);

        return $fileName;
    }
}
