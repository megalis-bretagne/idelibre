<?php

namespace App\Service\Email;

use App\Entity\Sitting;
use App\Service\Util\FileUtil;
use DateTimeInterface;
use DateTimeZone;
use Eluceo\iCal\Domain\Entity\Calendar;
use Eluceo\iCal\Domain\Entity\Event;
use Eluceo\iCal\Domain\ValueObject\DateTime;
use Eluceo\iCal\Domain\ValueObject\Location;
use Eluceo\iCal\Domain\ValueObject\TimeSpan;
use Eluceo\iCal\Presentation\Factory\CalendarFactory;
use Exception;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Filesystem\Filesystem;

class CalGenerator
{
    public const CONTENT_TYPE = 'text/calendar';
    public const DIRECTORY = '/tmp/cal/';

    public function __construct(
        private Filesystem $filesystem,
        private FileUtil $fileUtil,
        private LoggerInterface $logger
    ) {
    }

    public function generate(Sitting $sitting): ?string
    {
        if (!$sitting->getReminder() || !$sitting->getReminder()->getIsActive()) {
            return null;
        }

        $this->filesystem->mkdir(self::DIRECTORY);

        $this->randomCleanDirectory();

        $timezone = $sitting->getStructure()->getTimezone()->getName();

        $endDateTimeWithTz = $this->getEndDatetimeWithTz($sitting->getDate(), $sitting->getReminder()->getDuration(), $timezone);
        $startDateTimeWithTz = $this->getStartDatetimeWithTz($sitting->getDate(), $timezone);

        $event = (new Event())
            ->setSummary($sitting->getName())
            ->setDescription($sitting->getName())
            ->setOccurrence(
                new TimeSpan(
                    new DateTime($startDateTimeWithTz, false),
                    new DateTime($endDateTimeWithTz, false)
                )
            );

        if ($sitting->getPlace()) {
            $event->setLocation(new Location($sitting->getPlace()));
        }

        $calendar = new Calendar([$event]);
        $calendar->setProductIdentifier('idelibre');

        $componentFactory = new CalendarFactory();
        $calendarComponent = $componentFactory->createCalendar($calendar);

        $fileName = self::DIRECTORY . Uuid::NAMESPACE_DNS;

        file_put_contents($fileName, $calendarComponent);

        return $fileName;
    }

    public function getEndDatetimeWithTz(DateTimeInterface $sittingDateTime, int $durationInMinutes, string $timezoneName): \DateTime
    {
        $mutableDateTime = new \DateTime();
        $mutableDateTime->setTimestamp($sittingDateTime->getTimestamp());
        $mutableDateTime->setTimezone(new DateTimeZone($timezoneName));

        return date_add($mutableDateTime, date_interval_create_from_date_string("$durationInMinutes minutes"));
    }

    public function getStartDatetimeWithTz(DateTimeInterface $sittingDateTime, string $timezoneName): \DateTime
    {
        $mutableDateTime = new \DateTime();
        $mutableDateTime->setTimestamp($sittingDateTime->getTimestamp());

        return $mutableDateTime->setTimezone(new DateTimeZone($timezoneName));
    }

    private function randomCleanDirectory()
    {
        try {
            if (42 === random_int(0, 100)) {
                $this->fileUtil->deleteFileInDirectory(self::DIRECTORY);
            }
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }
}
