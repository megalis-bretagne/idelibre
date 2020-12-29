<?php

namespace App\Tests\Repository;

use App\DataFixtures\GroupFixtures;
use App\DataFixtures\RoleFixtures;
use App\DataFixtures\StructureFixtures;
use App\DataFixtures\UserFixtures;
use App\Entity\Group;
use App\Entity\Structure;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Tests\FindEntityTrait;
use App\Tests\LoginTrait;
use Doctrine\Persistence\ObjectManager;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserRepositoryTest extends WebTestCase
{
    use FixturesTrait;
    use FindEntityTrait;
    use LoginTrait;

    private ?KernelBrowser $client;
    /**
     * @var ObjectManager
     */
    private $entityManager;
    /**
     * @var UserRepository
     */
    private $userRepository;

    protected function setUp(): void
    {
        $this->client = static::createClient();

        $kernel = self::bootKernel();
        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        $this->userRepository = $this->entityManager->getRepository(User::class);

        $this->loadFixtures([
            StructureFixtures::class,
            UserFixtures::class,
            RoleFixtures::class,
            GroupFixtures::class,
        ]);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->client = null;
        $this->entityManager->close();
    }

    public function testFindByStructure()
    {
        /** @var Structure $structure */
        $structure = $this->getOneEntityBy(Structure::class, ['name' => 'Libriciel']);

        $this->assertCount(7, $this->userRepository->findByStructure($structure)->getQuery()->getResult());
    }

    public function testFindSuperAdminAndGroupAdmin()
    {
        $this->assertCount(3, $this->userRepository->findSuperAdminAndGroupAdmin(null)->getQuery()->getResult());
    }

    public function testFindSuperAdminAndGroupAdminLimitedRecia()
    {
        /** @var Group $group */
        $groupRecia = $this->getOneEntityBy(Group::class, ['name' => 'Recia']);
        $this->assertCount(1, $this->userRepository->findSuperAdminAndGroupAdmin($groupRecia)->getQuery()->getResult());
    }

    public function testFindSuperAdminAndGroupInStructure()
    {
        /** @var Structure $structure */
        $structure = $this->getOneEntityBy(Structure::class, ['name' => 'Libriciel']);
        /** @var User $superadmin */
        $superadmin = $this->getOneEntityBy(User::class, ['username' => 'superadmin']);
        /** @var User $superadmin */
        $userGroupRecia = $this->getOneEntityBy(User::class, ['username' => 'userGroupRecia']);

        $this->assertCount(0, $this->userRepository->findSuperAdminAndGroupAdminInStructure($structure)->getResult());

        $superadmin->setStructure($structure);
        $this->entityManager->persist($superadmin);
        $this->entityManager->flush();

        $this->assertNotEmpty($superadmin->getStructure());
        $this->assertCount(1, $this->userRepository->findSuperAdminAndGroupAdminInStructure($structure)->getResult());

        $userGroupRecia->setStructure($structure);
        $this->entityManager->persist($userGroupRecia);
        $this->entityManager->flush();

        $this->assertNotEmpty($userGroupRecia->getStructure());
        $this->assertCount(2, $this->userRepository->findSuperAdminAndGroupAdminInStructure($structure)->getResult());
    }

    public function testFindActorByStructure()
    {
        /** @var Structure $structure */
        $structure = $this->getOneEntityBy(Structure::class, ['name' => 'Libriciel']);
        $actorQB = $this->userRepository->findActorByStructure($structure);
        $this->assertCount(4, $actorQB->getQuery()->getResult());
    }
}
