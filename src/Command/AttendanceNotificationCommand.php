<?php

namespace App\Command;

use App\Service\RecapNotificationMail\RecapNotificationService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'attendance:notification')]
class AttendanceNotificationCommand extends Command
{
    public function __construct(
        private readonly RecapNotificationService $recapNotificationService,
        string                                    $name = null
    ) {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Send one mail with all data for presence, absence or procuration')
            ->setHelp(' docker exec -it fpm-idelibre bin/console attendance:notification');
    }


    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $this->recapNotificationService->sendRecapNotifications();

        $io->success('OK');

        return Command::SUCCESS;
    }
}
