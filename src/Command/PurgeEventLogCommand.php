<?php

namespace App\Command;

use App\Repository\EventLogRepository;
use App\Repository\SittingRepository;
use App\Repository\StructureRepository;
use App\Service\Seance\SittingManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'purge:eventLog')]
class PurgeEventLogCommand extends Command
{

    public function __construct(
        private readonly StructureRepository $structureRepository,
        private readonly EventLogRepository $eventLogRepository,
        string $name = null
    ) {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this
            ->setDescription('delete eventLogs before date ')
            ->setHelp('delete eventLogs before');
    }


    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $structures = $this->structureRepository->findAll();
        foreach ($structures as $structure) {
            $delay = '6 months';
            $before = new \DateTimeImmutable('-' . $delay);
            $toRemoveEventLogs = $this->eventLogRepository->findSittingsBefore($before, $structure);
            $toRemoveEventLogIds = array_map(fn($eventLog) => $eventLog->getId(), $toRemoveEventLogs);
            $this->eventLogRepository->removeEventLogByIds($toRemoveEventLogIds);
        }

        $io = new SymfonyStyle($input, $output);
        $io->success('Séances supprimées');

        return Command::SUCCESS;
    }


}
