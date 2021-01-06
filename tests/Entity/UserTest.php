<?php

namespace App\Tests\Entity;

use App\DataFixtures\UserFixtures;
use App\Entity\User;
use App\Tests\FindEntityTrait;
use App\Tests\HasValidationError;
use App\Tests\StringTrait;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserTest extends WebTestCase
{
    use FixturesTrait;
    use FindEntityTrait;
    use HasValidationError;
    use StringTrait;

    private ValidatorInterface $validator;
    private $entityManager;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->validator = self::$container->get('validator');
        $this->entityManager = self::$container->get('doctrine')->getManager();

        $this->loadFixtures([
            UserFixtures::class,
        ]);
    }

    public function testValidNoAtUsername()
    {
        $user = (new User())
            ->setFirstName('new firstName')
            ->setLastName('new lastName')
            ->setUsername('new username')
            ->setEmail('email@example.org');

        $this->assertHasValidationErrors($user, 0);
    }

    public function testValidOneAtUsername()
    {
        $user = (new User())
            ->setFirstName('new firstName')
            ->setLastName('new lastName')
            ->setUsername('new username@toto')
            ->setEmail('email@example.org');

        $this->assertHasValidationErrors($user, 0);
    }

    public function testInvalidTwoAtUsername()
    {
        $user = (new User())
            ->setFirstName('new firstName')
            ->setLastName('new lastName')
            ->setUsername('new username@toto@extrasuffix')
            ->setEmail('email@example.org');

        $this->assertHasValidationErrors($user, 1);
    }

    public function testInvalidUsernameAlreadyExists()
    {
        $user = (new User())
            ->setFirstName('new firstName')
            ->setLastName('new lastName')
            ->setUsername('admin@libriciel')
            ->setEmail('email@example.org');

        $this->assertHasValidationErrors($user, 1);
    }

    public function testInvalidEmptyUsername()
    {
        $user = (new User())
            ->setFirstName('new firstName')
            ->setLastName('new lastName')
            ->setUsername('')
            ->setEmail('email@example.org');

        $this->assertHasValidationErrors($user, 1);
    }

    public function testInvalidNoUsername()
    {
        $user = (new User())
            ->setFirstName('new firstName')
            ->setLastName('new lastName')
            ->setEmail('email@example.org');

        $this->assertHasValidationErrors($user, 1);
    }

    public function testInvalidUsernameTooLong()
    {
        $user = (new User())
            ->setFirstName('new firstName')
            ->setLastName('new lastName')
            ->setUsername($this->genString(256))
            ->setEmail('email@example.org');

        $this->assertHasValidationErrors($user, 1);
    }

    public function testInvalidEmptyFirstName()
    {
        $user = (new User())
            ->setFirstName('')
            ->setLastName('new lastName')
            ->setUsername('new username')
            ->setEmail('email@example.org');

        $this->assertHasValidationErrors($user, 1);
    }

    public function testInvalidNoFirstName()
    {
        $user = (new User())
            ->setLastName('new lastName')
            ->setUsername('new username')
            ->setEmail('email@example.org');

        $this->assertHasValidationErrors($user, 1);
    }

    public function testInvalidFirstNameTooLong()
    {
        $user = (new User())
            ->setFirstName($this->genString(256))
            ->setLastName('new lastName')
            ->setUsername('new username')
            ->setEmail('email@example.org');

        $this->assertHasValidationErrors($user, 1);
    }

    public function testInvalidEmptyLastName()
    {
        $user = (new User())
            ->setFirstName('new firstName')
            ->setLastName('')
            ->setUsername('new username')
            ->setEmail('email@example.org');

        $this->assertHasValidationErrors($user, 1);
    }

    public function testInvalidLastNameTooLong()
    {
        $user = (new User())
            ->setFirstName('new firstName')
            ->setLastName($this->genString(256))
            ->setUsername('new username')
            ->setEmail('email@example.org');

        $this->assertHasValidationErrors($user, 1);
    }

    public function testInvalidNoLastName()
    {
        $user = (new User())
            ->setFirstName('new firstName')
            ->setUsername('new username')
            ->setEmail('email@example.org');

        $this->assertHasValidationErrors($user, 1);
    }

    public function testInvalidEmailNotAnEmail()
    {
        $user = (new User())
            ->setFirstName('new firstName')
            ->setLastName('new lastName')
            ->setUsername('new username')
            ->setEmail('email.example.org');

        $this->assertHasValidationErrors($user, 1);
    }
}
