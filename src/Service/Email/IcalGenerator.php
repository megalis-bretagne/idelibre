<?php

namespace App\Service\Email;

use App\Entity\Sitting;
use Eluceo\iCal\Domain\Entity\Calendar;
use Eluceo\iCal\Domain\Entity\Event;
use Eluceo\iCal\Domain\ValueObject\DateTime;
use Eluceo\iCal\Domain\ValueObject\EmailAddress;
use Eluceo\iCal\Domain\ValueObject\Organizer;
use Eluceo\iCal\Domain\ValueObject\TimeSpan;
use Eluceo\iCal\Presentation\Factory\CalendarFactory;
use Ramsey\Uuid\Uuid;

class IcalGenerator
{
    public const CONTENT_TYPE = 'text/calendar';

    public function generate(Sitting $sitting): string
    {
        $timezone = $sitting->getStructure()->getTimezone()->getName();

        $endDateTimeWithTz = $this->getEndDatetimeWithTz($sitting->getDate(), 90, $timezone);
        $startDateTimeWithTz = $this->getStartDatetimeWithTz($sitting->getDate(), $timezone);

        $event = (new Event())
            ->setSummary($sitting->getName())
            ->setDescription($sitting->getName())
            ->setOccurrence(
                new TimeSpan(
                    new DateTime($startDateTimeWithTz, true),
                    new DateTime($endDateTimeWithTz, true)
                )
            )
            ->setOrganizer(new Organizer(new EmailAddress($sitting->getStructure()->getReplyTo()), $sitting->getStructure()->getName()));

        $calendar = new Calendar([$event]);
        $calendar->setProductIdentifier('idelibre');

        $componentFactory = new CalendarFactory();
        $calendarComponent = $componentFactory->createCalendar($calendar);

        $fileName = '/tmp/' . Uuid::NAMESPACE_DNS;

        file_put_contents($fileName, $calendarComponent);

        return $fileName;
    }

    public function getEndDatetimeWithTz(\DateTimeInterface $sittingDateTime, int $durationInMinutes, string $timezoneName): \DateTime
    {
        $mutableDateTime = new \DateTime();
        $mutableDateTime->setTimestamp($sittingDateTime->getTimestamp());
        $mutableDateTime->setTimezone(new \DateTimeZone($timezoneName));

        return date_add($mutableDateTime, date_interval_create_from_date_string("$durationInMinutes minutes"));
    }

    public function getStartDatetimeWithTz(\DateTimeInterface $sittingDateTime, string $timezoneName): \DateTime
    {
        $mutableDateTime = new \DateTime();
        $mutableDateTime->setTimestamp($sittingDateTime->getTimestamp());

        return $mutableDateTime->setTimezone(new \DateTimeZone($timezoneName));
    }
}
