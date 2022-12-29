<?php

namespace App\Command;

use App\Command\ServiceCmd\AttendanceNotification;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class AttendanceNotificationCommand extends Command
{
    protected static $defaultName = 'attendance:notification';
    private AttendanceNotification $attendanceNotification;

    public function __construct(
        AttendanceNotification $attendanceNotification,
        string $name = null
    ) {
        parent::__construct($name);
        $this->attendanceNotification = $attendanceNotification;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Send one mail with all datas for presence, absence or procuration')
            ->setHelp(' docker exec -it fpm-idelibre bin/console attendance:notification')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $this->attendanceNotification->genAllAttendanceNotification();

        $io->success('OK');

        return Command::SUCCESS;
    }
}
