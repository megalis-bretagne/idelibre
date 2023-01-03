<?php

namespace App\Command;

use App\Repository\FileRepository;
use App\Service\File\FileManager;
use Doctrine\ORM\EntityManagerInterface;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

#[AsCommand(name: 'send:files_to_s3')]
class SendFilesOnDiskToS3 extends Command
{
    public function __construct(
        private readonly ParameterBagInterface  $bag,
        private readonly FileManager            $fileManager,
        private readonly FileRepository         $fileRepository,
        private readonly EntityManagerInterface $em,
        string                                  $name = null
    ) {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Send files on disk to S3')
            ->setHelp('Send files');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $fileCount = $this->fileRepository->count([]);
        $io = new SymfonyStyle($input, $output);
        $batchCount = 300;
        $offset = 0;

        do {
            $io->info($offset . " / " . $fileCount);

            $files = $this->fileRepository->findBy([], ['id' => 'asc'], $batchCount, $offset);

            foreach ($files as $file) {
                try {
                    $filePath = $file->getPath();
                    $this->fileManager->transfertToS3($filePath);
                    $file->setCachedAt(new \DateTimeImmutable($this->bag->get('duration_cached_files')));
                    $this->em->persist($file);

                    $io->success('Fichier envoyé au S3 : ' . $filePath);
                } catch (\Exception $exception) {
                    $io->error($exception->getMessage());
                }
            }

            $this->em->flush();

            $offset += $batchCount;

            $this->em->clear();
        } while (\count($files) > 0);

        return Command::SUCCESS;
    }
}