<?php

namespace App\Command;

use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;


class ExportCsvCommand extends Command
{
    protected static $defaultName = 'export:user';

    public function __construct(
    ) {
        parent::__construct();
    }


    protected function configure(): void
    {
        $this
            ->addArgument('structureId', InputArgument::REQUIRED, 'id de la structure')
            ->setDescription('Generate export CSV for user')
            ->setHelp('Use -u user for user data')
        ;
    }

    private function runQuery(string $query)
    {
        $psqlCmd = "psql --dbname=" . getenv('DATABASE_URL') . " -c " . "\"" . $query . "\"";
        exec($psqlCmd, $out, $resultCode);

        if ($resultCode != 0) {
            throw new Exception("erreur dans le sql : " . $query);
        }
        dump($out);
    }


    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $structureId = $input->getArgument('structureId');
        $fileSystem = new Filesystem();

        $pathDir = '/data/files/export/' . $structureId;

        $fileSystem->remove($pathDir);
        $fileSystem->mkdir($pathDir);

        if (!$structureId) {
            $io->note('structureId is required');
            return Command::FAILURE;
        }

        $this->exportUsers($structureId, $pathDir);

        return Command::SUCCESS;
    }

    protected function exportUsers(string $structureId, string $pathDir): void
    {
        $path = $pathDir . '/user.csv';
        $query = "copy(select * from " .'\"user\"' . " where structure_id ='$structureId') to '$path' delimiter ',' csv HEADER ENCODING 'UTF8';";

        $this->runQuery($query);
    }
}
