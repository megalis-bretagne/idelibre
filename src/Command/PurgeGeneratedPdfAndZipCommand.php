<?php

namespace App\Command;

use App\Repository\SittingRepository;
use App\Repository\StructureRepository;
use App\Service\File\Generator\FileGenerator;
use App\Service\Seance\SittingManager;
use DateTimeImmutable;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

// bin/console purge:generatedZipPdf "30/06/2020"
#[AsCommand(name: 'purge:generatedZipPdf')]
class PurgeGeneratedPdfAndZipCommand extends Command
{
    public function __construct(
        private readonly SittingRepository   $sittingRepository,
        private readonly StructureRepository $structureRepository,
        private readonly FileGenerator       $fileGenerator,
        string                               $name = null
    ) {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this
            ->setDescription('delete sittings before')
            ->setHelp('delete sittings before')
            ->addArgument('before', InputArgument::REQUIRED, 'before date')
        ;
    }

    /**
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $beforeString = $input->getArgument('before');
        $before = DateTimeImmutable::createFromFormat('d/m/yy', $beforeString);

        $structures = $this->structureRepository->findAll();
        foreach ($structures as $structure) {
            $toRemoveSittings = $this->sittingRepository->findSittingsBefore($before, $structure);

            $this->removeGeneratedZipAndPdf($toRemoveSittings);
        }

        $io = new SymfonyStyle($input, $output);
        $io->success('Séances supprimées');

        return Command::SUCCESS;
    }

    private function removeGeneratedZipAndPdf(iterable $sittings): void
    {
        foreach ($sittings as $sitting) {
            $this->fileGenerator->deleteFullSittingFile($sitting, 'pdf');
            $this->fileGenerator->deleteFullSittingFile($sitting, 'zip');
        }
    }
}
