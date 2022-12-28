<?php

namespace App\Command;

use App\Repository\SittingRepository;
use App\Repository\StructureRepository;
use App\Service\Seance\SittingManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

#[AsCommand(name: 'purge:sitting')]
class PurgeSittingsCommand extends Command
{
    public function __construct(
        private readonly SittingRepository $sittingRepository,
        private readonly StructureRepository $structureRepository,
        private readonly SittingManager $sittingManager,
        string $name = null
    ) {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this
            ->addArgument('structureId', InputArgument::REQUIRED, 'id de la structure')
            ->addArgument('before', InputArgument::REQUIRED, 'before date')
            ->setDescription('delete sittings before')
            ->setHelp('delete sittings before');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $structure = $this->structureRepository->find($input->getArgument('structureId'));
        if (!$structure) {
            throw new NotFoundHttpException('structure does not exist');
        }

        $beforeString = $input->getArgument('before');
        $before = new \DateTimeImmutable($beforeString);

        $sittings = $this->sittingRepository->findSittingsBefore($before, $structure);
        $numberSittings = count($sittings);

        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion(
            "Confirmez-vous vouloir purger les seances d'avant le {$before->format('d/m/y')} de la structure {$structure->getName()} ? \n" .
             "({$numberSittings} Séances)(y/n)",
            false
        );

        if (!$helper->ask($input, $output, $question)) {
            $io->text('Operation annulée');

            return Command::SUCCESS;
        }

        foreach ($sittings as $sitting) {
            $this->sittingManager->delete($sitting);
        }

        $io->success('Séances supprimées');

        return Command::SUCCESS;
    }
}
