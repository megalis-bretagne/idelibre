<?php

namespace App\Command;

use App\Repository\SittingRepository;
use App\Repository\StructureRepository;
use App\Service\Seance\SittingManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class PurgeDataCommand extends Command
{
    protected static $defaultName = 'purge:structures';

    private SittingRepository $sittingRepository;
    private StructureRepository $structureRepository;
    private SittingManager $sittingManager;

    public function __construct(
        SittingRepository $sittingRepository,
        StructureRepository $structureRepository,
        SittingManager $sittingManager,
        string $name = null
    ) {
        parent::__construct($name);
        $this->sittingRepository = $sittingRepository;
        $this->structureRepository = $structureRepository;
        $this->sittingManager = $sittingManager;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('delete sittings before')
            ->setHelp('delete sittings before');
    }

    /**
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $structures = $this->structureRepository->findAll();
        foreach ($structures as $structure) {
            $delay = $structure->getConfiguration()->getSittingSuppressionDelay();
            $before = new \DateTimeImmutable('-' . $delay);
            $toRemoveSittings = $this->sittingRepository->findSittingsBefore($before, $structure);

            $this->removeSittings($toRemoveSittings);
        }

        $io = new SymfonyStyle($input, $output);
        $io->success('Séances supprimées');

        return Command::SUCCESS;
    }

    private function removeSittings(iterable $sittings): void
    {
        foreach ($sittings as $sitting) {
            $this->sittingManager->delete($sitting);
        }
    }
}
