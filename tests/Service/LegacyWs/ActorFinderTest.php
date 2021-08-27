<?php

namespace App\Tests\Service\LegacyWs;

use App\DataFixtures\StructureFixtures;
use App\DataFixtures\UserFixtures;
use App\Entity\Structure;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Tests\FindEntityTrait;
use App\Tests\LoginTrait;
use Doctrine\Persistence\ObjectManager;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ActorFinderTest extends WebTestCase
{
    use FindEntityTrait;
    use LoginTrait;

    /**
     * @var ObjectManager
     */
    private $entityManager;
    private UserRepository $userRepository;


    protected function setUp(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        $this->entityManager = $container->get('doctrine')->getManager();
        $this->userRepository = $this->entityManager->getRepository(User::class);

        $databaseTool = self::getContainer()->get(DatabaseToolCollection::class)->get();
        $databaseTool->loadFixtures([
            StructureFixtures::class,
            UserFixtures::class
        ]);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
    }


    private function createUser(string $firstname, string $lastName, Structure $structure): User
    {
        $user = new User();
        $user->setPassword('fake')
            ->setUsername('usernameLibriciel')
            ->setLastName($lastName)
            ->setFirstName($firstname)
            ->setEmail('email@exemple.org')
            ->setStructure($structure);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    public function testFindByStructureIdenticalNames()
    {
        $structure = $this->getOneStructureBy(['name' => 'Libriciel']);
        $this->createUser("firstWS", "lastWS", $structure);
        $actor = $this->userRepository->findByFirstNameLastNameAndStructure("firstWS", "lastWS", $structure);
        $this->assertNotEmpty($actor);
    }


    public function testFindByStructureTrailingSpacesBDD()
    {
        $structure = $this->getOneStructureBy(['name' => 'Libriciel']);
        $this->createUser(" firstWS ", " lastWS  ", $structure);
        $actor = $this->userRepository->findByFirstNameLastNameAndStructure("firstWS", "lastWS", $structure);
        $this->assertNotEmpty($actor);
    }

    public function testFindByStructureTrailingSpacesWSActor()
    {
        $structure = $this->getOneStructureBy(['name' => 'Libriciel']);
        $this->createUser("firstWS", "lastWS", $structure);
        $actor = $this->userRepository->findByFirstNameLastNameAndStructure("  firstWS ", " lastWS ", $structure);
        $this->assertNotEmpty($actor);
    }


    public function testFindByStructureSpaceBetween()
    {
        $structure = $this->getOneStructureBy(['name' => 'Libriciel']);
        $this->createUser("first WS", "last WS", $structure);
        $actor = $this->userRepository->findByFirstNameLastNameAndStructure("firstWS", "lastWS", $structure);
        $this->assertEmpty($actor);
    }


    public function testFindByStructureCaseInsensitive()
    {
        $structure = $this->getOneStructureBy(['name' => 'Libriciel']);
        $this->createUser("firstws", "LASTWS", $structure);
        $actor = $this->userRepository->findByFirstNameLastNameAndStructure("firstWS", "lastWS", $structure);
        $this->assertNotEmpty($actor);
    }


    public function testFindByStructureNotSameStructure()
    {
        $structure = $this->getOneStructureBy(['name' => 'Libriciel']);
        $this->createUser("firstWS", "firstWS", $structure);
        $structureMtp = $this->getOneStructureBy(['name' => 'Montpellier']);
        $actor = $this->userRepository->findByFirstNameLastNameAndStructure("firstWS", "firstWS", $structureMtp);
        $this->assertEmpty($actor);
    }


}
