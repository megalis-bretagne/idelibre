<?php

namespace App\Tests\Service\LegacyWs;

use App\Entity\Structure;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Tests\FindEntityTrait;
use App\Tests\LoginTrait;
use App\Tests\Story\StructureStory;
use App\Tests\Story\UserStory;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class ActorFinderTest extends WebTestCase
{
    use ResetDatabase;
    use Factories;
    use FindEntityTrait;
    use LoginTrait;

    private ObjectManager $entityManager;
    private UserRepository $userRepository;

    protected function setUp(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        $this->entityManager = $container->get('doctrine')->getManager();
        $this->userRepository = self::getcontainer()->get(UserRepository::class);

        StructureStory::load();
        UserStory::load();
    }

    private function createUser(string $firstname, string $lastName, Structure $structure, string $username = 'usernameLibriciel'): User
    {
        $user = new User();
        $user->setPassword('fake')
            ->setUsername($username)
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
        $structure = StructureStory::libriciel();
        $this->createUser('firstWS', 'lastWS', $structure->object());
        $actor = $this->userRepository->findByFirstNameLastNameAndStructureOrUsername('firstWS', 'lastWS', $structure->object(), 'f.last@libriciel');
        $this->assertNotEmpty($actor);
    }

    public function testFindByStructureTrailingSpacesBDD()
    {
        $structure = StructureStory::libriciel();
        $this->createUser(' firstWS ', ' lastWS  ', $structure->object());
        $actor = $this->userRepository->findByFirstNameLastNameAndStructureOrUsername('firstWS', 'lastWS', $structure->object(), 'f.last@libriciel');
        $this->assertNotEmpty($actor);
    }

    public function testFindByStructureTrailingSpacesWSActor()
    {
        $structure = StructureStory::libriciel();
        $this->createUser('firstWS', 'lastWS', $structure->object());
        $actor = $this->userRepository->findByFirstNameLastNameAndStructureOrUsername('  firstWS ', ' lastWS ', $structure->object(), 'f.last@libriciel');
        $this->assertNotEmpty($actor);
    }

    public function testFindByStructureSpaceBetween()
    {
        $structure = StructureStory::libriciel();
        $this->createUser('first WS', 'last WS', $structure->object());
        $actor = $this->userRepository->findByFirstNameLastNameAndStructureOrUsername('firstWS', 'lastWS', $structure->object(), 'f.last@libriciel');
        $this->assertEmpty($actor);
    }

    public function testFindByStructureCaseInsensitive()
    {
        $structure = StructureStory::libriciel();
        $this->createUser('firstws', 'LASTWS', $structure->object());
        $actor = $this->userRepository->findByFirstNameLastNameAndStructureOrUsername('firstWS', 'lastWS', $structure->object(), 'f.last@libriciel');
        $this->assertNotEmpty($actor);
    }

    public function testFindByStructureDifferentNameSameUsername()
    {
        $structure = StructureStory::libriciel();
        $this->createUser('firstws', 'lastws', $structure->object(), 'sameUsername');
        $actor = $this->userRepository->findByFirstNameLastNameAndStructureOrUsername('other', 'name same username', $structure->object(), 'sameUsername');
        $this->assertNotEmpty($actor);
    }

    public function testFindByStructureNewUser()
    {
        $structure = StructureStory::libriciel();
        $this->createUser('firstws', 'lastws', $structure->object());
        $actor = $this->userRepository->findByFirstNameLastNameAndStructureOrUsername('news', 'user', $structure->object(), 'n.user@libriciel');
        $this->assertEmpty($actor);
    }

    public function testFindByStructureNotSameStructure()
    {
        $structure = StructureStory::libriciel();
        $this->createUser('firstWS', 'firstWS', $structure->object());
        $structureMtp = StructureStory::montpellier();
        $actor = $this->userRepository->findByFirstNameLastNameAndStructureOrUsername('firstWS', 'firstWS', $structureMtp->object(), 'f.last@libriciel');
        $this->assertEmpty($actor);
    }
}
