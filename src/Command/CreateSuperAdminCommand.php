<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CreateSuperAdminCommand extends Command
{
    protected static $defaultName = 'admin:create:superadmin';

    protected function configure()
    {
        $this
            // bin/console list
            ->setDescription('Create a superAdmin')
            // bin/console command --help
            ->setHelp('Create a superadmin')

            // bin/console command parm1 param2
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            // --repeat=2
            ->addOption('no-password', 'np', InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $arg1 = $input->getArgument('arg1');

        if ($arg1) {
            $io->note(sprintf('You passed an argument: %s', $arg1));
        }

        $output->writeln([
            'User Creator',
            '============',
            '',
        ]);

        $io->title("Voici la nouvelle commande symfony");
        $io->text("Text simple");
        $io->text(['superadmin', 'admin', 'user']);

        $res = $io->choice("choississez un groupe :", ['superadmin', 'admin', 'user']);

        $io->text($res);

        $io->horizontalTable(['name', 'size', 'number'], [['toto', 'tata', 'tutu'], ['toto', 'tata', 'tutu']]);
        $io->table(['name', 'size', 'number'], [['toto', 'tata', 'tutu'], ['toto', 'tata', 'tutu']]);


        $io->listing(['name', 'size', 'number']);

        $io->block('TOTO');


        $io->caution("Be careful");


        $io->success('You have a new command! Now make it your own! Pass --help to see your options.');

        return Command::SUCCESS;
    }
}
