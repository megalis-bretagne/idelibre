<?php

namespace App\Command;

use App\Repository\StructureRepository;
use App\Service\NotificationMail\NotificationDataProvider;
use App\Service\NotificationMail\NotificationFormater;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Commande utilisée uniquement pour un passage en v4.2.0.
 */
#[AsCommand(name: 'initBdd:email_template_recap')]
class InitEmailTemplateRecapitulatifCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly StructureRepository $structureRepository,
        private NotificationDataProvider $notificationDataProvider,
        string $name = null
    ) {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this
            ->setDescription('insert into emailTemplate table')
        ;
    }

    /**
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        if (!$this->isInit()) {
            $io->text('database is not initialized, nothing to update');

            return 0;
        }

        if ($this->alreadyExistDataRecapIntoEmailTemplateForStructureId()) {
            $io->text('Recapitulatif email_template already set');

            return 0;
        }

        $io->text('Beginning update');
        $structures = $this->structureRepository->findAll();
        foreach ($structures as $structure) {
            $pdo = $this->entityManager->getConnection()->getNativeConnection();
            $structureId = "'" . $structure->getId() . "'";
            $sqlInsertIntoEmailTemplateTable = "INSERT INTO email_template (id, structure_id, name, content, subject, is_default, category, is_attachment, format) VALUES
 (UUID_GENERATE_V4(), $structureId, 'Récapitulatif par défaut', 'Bonjour #civilite# #nom# #prenom#, </br>
</br>
Ce mail est un récapitulatif des présents/absents pour les différentes séances en cours.</br>
</br>
#recapitulatif#
</br>
</br>
Cordialement,', 'Récapitulatif des absences/présences aux séances', true, 'recapitulatif', false, 'html');";

            $pdo->beginTransaction();
            try {
                $pdo->exec($sqlInsertIntoEmailTemplateTable);
                $pdo->commit();
            } catch (Exception $e) {
                $pdo->rollBack();
                throw $e;
            }
        }

        $io->success('insert into email_template table done');
        $io->text('Ending Update ');

        return Command::SUCCESS;
    }

    private function isInit(): bool
    {
        $pdo = $this->entityManager->getConnection()->getNativeConnection();

        try {
            $pdo->exec('select * from "user"');
        } catch (Exception) {
            return false;
        }

        return true;
    }

    private function alreadyExistDataRecapIntoEmailTemplateForStructureId(): bool
    {
        $pdo = $this->entityManager->getConnection()->getNativeConnection();
        $statement = $pdo->prepare("select * from email_template where category= 'recapitulatif';");
        $statement->execute();
        $count = $statement->rowCount();

        return $count > 0;
    }
}
