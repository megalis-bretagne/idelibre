<?php

namespace App\Command;

use App\Command\ServiceCmd\GenZipAndPdf;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'gen:zip_pdf')]
class GenPdfAndZipSittingCommand extends Command
{
    public function __construct(private readonly GenZipAndPdf $genZipAndPdf, string $name = null)
    {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this
           ->setDescription('Generate sitting zip and pdf (active sittings)')
           ->setHelp('Generate sitting zip and pdf (active sittings)')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $this->genZipAndPdf->genAllZipPdf();

        $io->success('OK');

        return Command::SUCCESS;
    }
}
