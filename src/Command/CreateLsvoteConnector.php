<?php

namespace App\Command;

use App\Repository\StructureRepository;
use App\Service\Connector\LsvoteConnectorManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\ResultSetMapping;
use Exception;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'initBdd:connector_lsvote')]
class CreateLsvoteConnector extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly StructureRepository    $structureRepository,
        private readonly LsvoteConnectorManager $lsvoteConnectorManager,
        string                                  $name = null,
    ) {
        parent::__construct($name);
    }

    /**
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $structures = $this->structureRepository->findAll();

        if ($this->alreadyExistLsvoteConnector()) {
            $io->text('Le connecteur lsvote est déjà présent sur votre application');
            return Command::SUCCESS;
        }

        foreach ($structures as $structure) {
            $io->text('Chargement du connecteur lsvote');
            $this->lsvoteConnectorManager->createConnector($structure);
            $io->success("Le connecteur lsvote s'est installé avec succès");
        }
        return Command::SUCCESS;
    }

    private function alreadyExistLsvoteConnector(): bool
    {
        $pdo = $this->entityManager->getConnection()->getNativeConnection();
        $statement = $pdo->prepare("select * from connector where name='lsvote'");
        $statement->execute();

        return $statement->rowCount() > 0;
    }
}
