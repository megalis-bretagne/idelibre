<?php

namespace App\Command;

use App\Command\ServiceCmd\AttendanceNotification;
use App\Service\Email\EmailNotSendException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

#[AsCommand(name: 'attendance:notification')]
class AttendanceNotificationCommand extends Command
{
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

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws EmailNotSendException
     * @throws LoaderError
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $this->attendanceNotification->genAllAttendanceNotification();

        $io->success('OK');

        return Command::SUCCESS;
    }
}
