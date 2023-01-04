<?php

namespace App\Command;

use App\Repository\FileRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;

#[AsCommand(name: 'purge:files_cached')]
class PurgeFilesCachedCommand extends Command
{
    public function __construct(
        private readonly FileRepository $fileRepository,
        private readonly Filesystem $filesystem,
        private readonly ParameterBagInterface $bag,
        string $name = null
    ) {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Delete cached in table file')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $this->fileRepository->deleteCachedFiles();

        $this->filesystem->remove($this->bag->get('document_files_directory'));
        $this->filesystem->remove($this->bag->get('document_zip_directory'));
        $this->filesystem->remove($this->bag->get('document_full_pdf_directory'));
        //TODO : rajouter le dossier "token"

        $io->success('Cache des fichiers supprimer');

        return Command::SUCCESS;
    }
}