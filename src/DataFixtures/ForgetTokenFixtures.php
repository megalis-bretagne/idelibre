<?php

namespace App\DataFixtures;

use App\Entity\ForgetToken;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ForgetTokenFixtures extends Fixture implements DependentFixtureInterface
{
    const GROUP = 'ForgetToken_';

    public function load(ObjectManager $manager)
    {
        /** @var User $user */
        $user = $this->getReference(UserFixtures::REFERENCE . 'adminLibriciel');

        $forgetToken = new ForgetToken($user);
        $forgetToken->setToken('forgetToken');

        $manager->persist($forgetToken);
        $manager->flush();
    }


    /**
     * @inheritDoc
     */
    public function getDependencies()
    {
        return [
            UserFixtures::class
        ];
    }
}
