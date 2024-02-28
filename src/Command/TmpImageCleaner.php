<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'clean:tmp-image')]
class TmpImageCleaner extends Command
{

    public function __construct(string $name = null)
    {
        parent::__construct($name);
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->text('Nettoyage des images temporaires');

        if(file_exists('/tmp/image')) {
            $io->text('Suppression du répertoire /tmp/image');
            exec('rm -rf /tmp/image/*');
        }

        return Command::SUCCESS;
    }
}
