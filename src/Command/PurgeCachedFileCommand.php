<?php

namespace App\Command;

use App\Repository\FileRepository;
use App\Service\File\FileManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;

#[AsCommand(name: 'purge:cached_files')]
class PurgeCachedFileCommand extends Command
{
    public function __construct(
        private readonly FileRepository $fileRepository,
        private readonly FileManager $fileManager,
        private readonly Filesystem $filesystem,
        string $name = null
    ) {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Delete files on disk')
            ->setHelp('Delete files')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $files = $this->fileRepository->findAllCachedExpired();

        foreach ($files as $file) {
            $pathFile = $file->getPath();

            if (true === $this->fileManager->fileExist($pathFile)) {
                $this->filesystem->remove($pathFile);
            }

            $this->fileManager->removeCachedAt($file);

            $io->success('Fichier supprimées : ' . $pathFile);
        }

        return Command::SUCCESS;
    }
}