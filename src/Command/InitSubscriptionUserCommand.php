<?php

namespace App\Command;

use App\Entity\User;
use App\Repository\SubscriptionRepository;
use App\Repository\UserRepository;
use App\Service\Subscription\SubscriptionManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Commande utilisée uniquement pour un passage en v4.2.
 */
#[AsCommand(name: 'initBdd:subscription_user')]
class InitSubscriptionUserCommand extends Command
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly SubscriptionManager $subscriptionManager,
        private readonly SubscriptionRepository $subscriptionRepository,
        string $name = null
    ) {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Add a subscription mail for user')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if ($this->alreadyExistSubscriptions()) {
            $io->text('subscriptions already init');

            return 0;
        }

        $users = $this->userRepository->findAllSecretaryAndAdmin();

        if ($users) {
            $io->info('Nombre d\'utilisateur : ' . count($users));

            /** @var User $user */
            foreach ($users as $user) {
                if (!$user->getSubscription()) {
                    $subscription = $this->subscriptionManager->add($user);
                    $this->entityManager->persist($subscription);
                }
            }
            $this->entityManager->flush();
            $io->success("import done");
        }

        return Command::SUCCESS;
    }

    private function alreadyExistSubscriptions(): bool
    {
        $subscriptionCount = $this->subscriptionRepository->count([]);

        return $subscriptionCount > 0;
    }
}
