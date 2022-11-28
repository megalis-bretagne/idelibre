<?php

namespace App\Tests\Repository;

use App\Repository\UserRepository;
use App\Tests\FindEntityTrait;
use App\Tests\LoginTrait;
use App\Tests\Story\GroupStory;
use App\Tests\Story\StructureStory;
use App\Tests\Story\UserStory;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class UserRepositoryTest extends WebTestCase
{
    use ResetDatabase;
    use Factories;
    use FindEntityTrait;
    use LoginTrait;

    private ?KernelBrowser $client;
    private ObjectManager $entityManager;
    private UserRepository $userRepository;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        $this->userRepository = self::getContainer()->get(UserRepository::class);

        self::ensureKernelShutdown();
        $this->client = static::createClient();

        StructureStory::libriciel();
        UserStory::load();
        GroupStory::load();
    }

    public function testFindByStructure()
    {
        $structure = StructureStory::libriciel();
        $this->assertCount(12, $this->userRepository->findByStructure($structure->object())->getQuery()->getResult());
    }

    public function testFindSuperAdminAndGroupAdmin()
    {
        $this->assertCount(5, $this->userRepository->findSuperAdminAndGroupAdmin(null)->getQuery()->getResult());
    }

    public function testFindSuperAdminAndGroupAdminLimitedRecia()
    {
        $groupRecia = GroupStory::recia();
        $this->assertCount(1, $this->userRepository->findSuperAdminAndGroupAdmin($groupRecia->object())->getQuery()->getResult());
    }

    public function testFindSuperAdminAndGroupInStructure()
    {
        $structure = StructureStory::libriciel();
        $superadmin = UserStory::superadmin();
        $userGroupRecia = UserStory::userGroupRecia();

        $this->assertCount(0, $this->userRepository->findSuperAdminAndGroupAdminInStructure($structure->object())->getResult());

        $superadmin->setStructure($structure->object());
        $superadmin->save();

        $this->assertNotEmpty($superadmin->getStructure());
        $this->assertCount(1, $this->userRepository->findSuperAdminAndGroupAdminInStructure($structure->object())->getResult());

        $userGroupRecia->setStructure($structure->object());
        $userGroupRecia->save();

        $this->assertNotEmpty($userGroupRecia->getStructure());
        $this->assertCount(2, $this->userRepository->findSuperAdminAndGroupAdminInStructure($structure->object())->getResult());
    }

    public function testFindActorByStructure()
    {
        $structure = StructureStory::libriciel();
        $actorQB = $this->userRepository->findActorsByStructure($structure->object());
        $this->assertCount(5, $actorQB->getQuery()->getResult());
    }

    public function testCountByRole()
    {
        $structure = StructureStory::libriciel();
        $countByRole = $this->userRepository->countByRole($structure->object());
        $this->assertCount(5, $countByRole);
    }
}
