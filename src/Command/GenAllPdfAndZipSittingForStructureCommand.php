<?php

namespace App\Command;

use App\Command\ServiceCmd\GenZipAndPdf;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class GenAllPdfAndZipSittingForStructureCommand extends Command
{
    protected static $defaultName = 'gen:all_zip_pdf';
    private GenZipAndPdf $genZipAndPdf;

    public function __construct(GenZipAndPdf $genZipAndPdf, string $name = null)
    {
        parent::__construct($name);
        $this->genZipAndPdf = $genZipAndPdf;
    }

    protected function configure(): void
    {
        $this
            ->addArgument('structureId', InputArgument::REQUIRED, 'id de la structure')
            ->setDescription('Generate sitting zip and pdf (active sittings)')
            ->setHelp('Generate sitting zip and pdf (active sittings)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $structureId = $input->getArgument('structureId');

        $this->genZipAndPdf->genAllTimeZipPdfByStructureId($structureId);

        $io->success('OK');

        return Command::SUCCESS;
    }
}
