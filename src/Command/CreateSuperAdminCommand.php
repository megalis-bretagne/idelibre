<?php

namespace App\Command;

use App\Entity\User;
use App\Security\Password\PasswordStrengthMeter;
use App\Service\role\RoleManager;
use App\Service\User\UserManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(name: 'admin:create:superadmin')]
class CreateSuperAdminCommand extends Command
{
    public function __construct(
        private readonly ParameterBagInterface $bag,
        private readonly EntityManagerInterface $em,
        private readonly UserManager $userManager,
        private readonly PasswordStrengthMeter $passwordStrengthMeter,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly RoleManager $roleManager,
        string $name = null,
    ) {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Create first user "superadmin"')
            ->setHelp('Create superadmin')
            ->addArgument('argPassword', InputArgument::OPTIONAL, 'Password')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        if ($this->isAlreadyCreate()) {
            $io->text('already existe in bdd');

            return 0;
        }
        $argPassword = $input->getArgument('argPassword');
        $minimumEntropy = $this->bag->get('minimumEntropyForUserWithRoleHigh');

        if ($argPassword) {
            $io->note(sprintf('Vous avez passé un mot de passe pour l\'utilisateur "superadmin" : %s', $argPassword));

            $successPassword = $this->passwordStrengthMeter->checkEntropy($argPassword, $minimumEntropy);

            if (false === $successPassword) {
                $io->error(sprintf('Le mot de passe pour l\'utilisateur ne correspond pas à l\'entropie définie de : %s', $minimumEntropy));

                return 0;
            }
            $password = $argPassword;
        } else {
            $password = $this->passwordStrengthMeter->generatePassword();
        }
        $roleSuperAdmin = $this->roleManager->getStructureAdminRole();
        $user = new User();
        $user->setUsername('superadmin')
            ->setFirstName('Admin')
            ->setLastName('SUPER')
            ->setEmail('superadmin@example.fr')
            ->setRole($roleSuperAdmin)
            ->setPassword($this->passwordHasher->hashPassword($user, $password))
        ;
        $this->userManager->save($user, '', null);

        $io->success(sprintf('L\'utilisateur "superadmin" a bien été enregistré avec le mot de passe : %s', $password));

        return Command::SUCCESS;
    }

    private function isAlreadyCreate(): bool
    {
        $pdo = $this->em->getConnection()->getNativeConnection();

        $statement = $pdo->prepare('select * from "user"');
        $statement->execute();
        $count = $statement->rowCount();

        return $count > 0;
    }
}
