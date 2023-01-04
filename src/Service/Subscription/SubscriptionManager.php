<?php

namespace App\Service\Subscription;

use App\Entity\Subscription;
use App\Entity\User;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;

class SubscriptionManager
{
    public function __construct(private readonly EntityManagerInterface $em)
    {
    }

    public function save(Subscription $subscription): void
    {
        $this->em->persist($subscription);
        $this->em->flush();
    }

    public function add(User $user): Subscription
    {
        return (new Subscription())
            ->setUser($user)
            ->setAcceptMailRecap(false)
            ->setCreatedAt(null)
        ;
    }
    public function update(Subscription $subscription): void
    {
        $subscription->setCreatedAt(new DateTimeImmutable());

        $this->save($subscription);
    }
}