<?php

namespace App\Tests\Service\User;

use App\Repository\UserRepository;
use App\Service\User\UserManager;
use App\Tests\Factory\UserFactory;
use App\Tests\Story\StructureStory;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class UserManagerTest extends WebTestCase
{
    use ResetDatabase;
    use Factories;

    private UserManager $userManager;
    private UserRepository $repository;

    protected function setUp(): void
    {
        $this->userManager = self::getContainer()->get(UserManager::class);
        $this->repository = self::getContainer()->get(UserRepository::class);
    }

    public function testIfDeputy()
    {
        $structure = StructureStory::libriciel();
        $user = UserFactory::createOne([
            "structure" => $structure,
            "isDeputy" => true,
            "mandator" => UserFactory::new()
        ])->object();
        $this->userManager->ifDeputy($user);
        $count = count($this->repository->findAll());
        $this->assertSame(2, $count);
        $this->assertNull($user->getMandator());
    }

}