<?php

namespace App\Command;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use function file_get_contents;

class InitBddCommand extends Command
{
    // bin/console initBdd /home/rdubourget/workspace/uploadFile/docker-ressources/minimum.sql
    protected static $defaultName = 'initBdd';
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager, string $name = null)
    {
        parent::__construct($name);
        $this->entityManager = $entityManager;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Add a short description for your command')
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description');
    }

    /**
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        if ($this->isAlreadyInit()) {
            $io->text('already init bdd');

            return 0;
        }

        $io->text('Beginning import');

        $file = $input->getArgument('arg1');
        $sql = file_get_contents($file);
        $pdo = $this->entityManager->getConnection()->getWrappedConnection();
        $pdo->beginTransaction();
        try {
            $pdo->exec($sql);
            $pdo->commit();
        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e;
        }

        $io = new SymfonyStyle($input, $output);
        $io->success('mimnimum import done');

        return 0;
    }

    private function isAlreadyInit(): bool
    {
        $pdo = $this->entityManager->getConnection()->getWrappedConnection();
        $statement = $pdo->prepare('select * from "user"');
        $statement->execute();
        $count = $statement->rowCount();

        return $count > 0;
    }
}
