<?php

namespace App\Tests\Entity;

use App\Entity\User;
use App\Tests\FindEntityTrait;
use App\Tests\HasValidationError;
use App\Tests\Story\RoleStory;
use App\Tests\Story\UserStory;
use App\Tests\StringTrait;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class UserTest extends WebTestCase
{
    use ResetDatabase;
    use Factories;
    use FindEntityTrait;
    use HasValidationError;
    use StringTrait;

    private ValidatorInterface $validator;
    private ObjectManager $entityManager;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->validator = self::getContainer()->get('validator');
        $this->entityManager = self::getContainer()->get('doctrine')->getManager();

        UserStory::load();
        RoleStory::load();
    }

    public function testValidNoAtUsername()
    {
        $user = (new User())
            ->setRole($this->getOneRoleBy(['name' => 'Secretary']))
            ->setFirstName('new firstName')
            ->setLastName('new lastName')
            ->setUsername('new username')
            ->setEmail('email@example.org')
        ;

        $this->assertHasValidationErrors($user, 0);
    }

    public function testValidOneAtUsername()
    {
        $user = (new User())
            ->setRole($this->getOneRoleBy(['name' => 'Secretary']))
            ->setFirstName('new firstName')
            ->setLastName('new lastName')
            ->setUsername('new username@toto')
            ->setEmail('email@example.org')
        ;

        $this->assertHasValidationErrors($user, 0);
    }

    public function testInvalidTwoAtUsername()
    {
        $user = (new User())
            ->setRole($this->getOneRoleBy(['name' => 'Secretary']))
            ->setFirstName('new firstName')
            ->setLastName('new lastName')
            ->setUsername('new username@toto@extrasuffix')
            ->setEmail('email@example.org')
        ;

        $this->assertHasValidationErrors($user, 1);
    }

    public function testInvalidUsernameAlreadyExists()
    {
        $user = (new User())
            ->setRole($this->getOneRoleBy(['name' => 'Secretary']))
            ->setFirstName('new firstName')
            ->setLastName('new lastName')
            ->setUsername('admin@libriciel')
            ->setEmail('email@example.org')
        ;

        $this->assertHasValidationErrors($user, 1);
    }

    public function testInvalidEmptyUsername()
    {
        $user = (new User())
            ->setRole($this->getOneRoleBy(['name' => 'Secretary']))
            ->setFirstName('new firstName')
            ->setLastName('new lastName')
            ->setUsername('')
            ->setEmail('email@example.org')
        ;

        $this->assertHasValidationErrors($user, 1);
    }

    public function testInvalidNoUsername()
    {
        $user = (new User())
            ->setRole($this->getOneRoleBy(['name' => 'Secretary']))
            ->setFirstName('new firstName')
            ->setLastName('new lastName')
            ->setEmail('email@example.org')
        ;

        $this->assertHasValidationErrors($user, 1);
    }

    public function testInvalidUsernameTooLong()
    {
        $user = (new User())
            ->setRole($this->getOneRoleBy(['name' => 'Secretary']))
            ->setFirstName('new firstName')
            ->setLastName('new lastName')
            ->setUsername($this->genString(256))
            ->setEmail('email@example.org')
        ;

        $this->assertHasValidationErrors($user, 1);
    }

    public function testInvalidEmptyFirstName()
    {
        $user = (new User())
            ->setRole($this->getOneRoleBy(['name' => 'Secretary']))
            ->setFirstName('')
            ->setLastName('new lastName')
            ->setUsername('new username')
            ->setEmail('email@example.org')
        ;

        $this->assertHasValidationErrors($user, 1);
    }

    public function testInvalidNoFirstName()
    {
        $user = (new User())
            ->setRole($this->getOneRoleBy(['name' => 'Secretary']))
            ->setLastName('new lastName')
            ->setUsername('new username')
            ->setEmail('email@example.org')
        ;

        $this->assertHasValidationErrors($user, 1);
    }

    public function testInvalidFirstNameTooLong()
    {
        $user = (new User())
            ->setRole($this->getOneRoleBy(['name' => 'Secretary']))
            ->setFirstName($this->genString(256))
            ->setLastName('new lastName')
            ->setUsername('new username')
            ->setEmail('email@example.org')
        ;

        $this->assertHasValidationErrors($user, 1);
    }

    public function testInvalidEmptyLastName()
    {
        $user = (new User())
            ->setRole($this->getOneRoleBy(['name' => 'Secretary']))
            ->setFirstName('new firstName')
            ->setLastName('')
            ->setUsername('new username')
            ->setEmail('email@example.org')
        ;

        $this->assertHasValidationErrors($user, 1);
    }

    public function testInvalidLastNameTooLong()
    {
        $user = (new User())
            ->setRole($this->getOneRoleBy(['name' => 'Secretary']))
            ->setFirstName('new firstName')
            ->setLastName($this->genString(256))
            ->setUsername('new username')
            ->setEmail('email@example.org')
        ;

        $this->assertHasValidationErrors($user, 1);
    }

    public function testInvalidNoLastName()
    {
        $user = (new User())
            ->setRole($this->getOneRoleBy(['name' => 'Secretary']))
            ->setFirstName('new firstName')
            ->setUsername('new username')
            ->setEmail('email@example.org')
        ;

        $this->assertHasValidationErrors($user, 1);
    }

    public function testInvalidEmailNotAnEmail()
    {
        $user = (new User())
            ->setRole($this->getOneRoleBy(['name' => 'Secretary']))
            ->setFirstName('new firstName')
            ->setLastName('new lastName')
            ->setUsername('new username')
            ->setEmail('email.example.org')
        ;

        $this->assertHasValidationErrors($user, 1);
    }
}
