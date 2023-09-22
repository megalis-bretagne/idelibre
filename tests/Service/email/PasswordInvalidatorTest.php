<?php

namespace App\Tests\Service\email;

use App\Repository\UserRepository;
use App\Service\User\PasswordInvalidator;
use App\Tests\Story\StructureStory;
use App\Tests\Story\UserStory;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class PasswordInvalidatorTest extends WebTestCase
{
    use ResetDatabase;
    use Factories;

    private UserRepository $userRepository;
    private ObjectManager $manager;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->manager = self::getContainer()->get('doctrine')->getManager();
        $this->userRepository = self::getContainer()->get(UserRepository::class);

        self::ensureKernelShutdown();

        StructureStory::load();
        UserStory::load();
    }

    public function testInvalidatePassword()
    {
        $password = self::getContainer()->get(PasswordInvalidator::class);
        $structure = StructureStory::libriciel()->object();

        $password->invalidateStructurePassword($structure);

        $users = $this->userRepository->findBy(['structure' => $structure]);
        foreach ($users as $user) {
            $this->assertSame($user->getPassword(), PasswordInvalidator::INVALID_PASSWORD);
        }
    }

}
