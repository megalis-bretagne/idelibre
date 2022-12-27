<?php

namespace App\Command;

use App\Repository\StructureRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;

#[AsCommand(name: 'createdir:token')]
class CreateTokenDirectory extends Command
{
    public function __construct(
        private readonly FileSystem $fileSystem,
        private readonly ParameterBagInterface $bag,
        private readonly StructureRepository $structureRepository,
        string $name = null
    ) {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this
            ->setDescription('create Token dir if not exists')
            ->setHelp('create Token dir if not exists');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $structures = $this->structureRepository->findAll();

        $year = (new \DateTimeImmutable())->format('Y');
        foreach ($structures as $structure) {
            $tokenStructureDirectory = "{$this->bag->get('token_directory')}{$structure->getId()}/$year/";
            if (!$this->fileSystem->exists($tokenStructureDirectory)) {
                dump($tokenStructureDirectory . ' : created');
                $this->fileSystem->mkdir($tokenStructureDirectory);
            }
        }

        $io->success('token dir create');

        return Command::SUCCESS;
    }
}
