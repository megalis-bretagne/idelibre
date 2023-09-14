<?php

namespace App\Command;

use App\Service\role\RoleManager;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'initBdd:add_role')]
class CreateNewRoleDeputy extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly RoleManager $roleManager,
        string $name = null,
    ) {
        parent::__construct($name);
    }

    /**
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if ($this->alreadyExistLsvoteConnector()) {
            $io->text('Le rôle "Suppléant existe déja"');
            return Command::SUCCESS;
        }

        $io->text('Création du rôle "Suppléant"');
        $this->roleManager->createNotAdminRole("Deputy", "Suppléant", true);
        $io->success("Le nouveau rôle a été ajouté avec succès");
        return Command::SUCCESS;
    }

    private function alreadyExistLsvoteConnector(): bool
    {
        $pdo = $this->entityManager->getConnection()->getNativeConnection();
        $statement = $pdo->prepare("select * from role where name='Deputy'");
        $statement->execute();

        return $statement->rowCount() > 0;
    }
}
