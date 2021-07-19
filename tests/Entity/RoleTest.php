<?php

namespace App\Tests\Entity;

use App\DataFixtures\RoleFixtures;
use App\Entity\Role;
use App\Tests\HasValidationError;
use App\Tests\StringTrait;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RoleTest extends WebTestCase
{
    use HasValidationError;
    use StringTrait;

    private ValidatorInterface $validator;
    private $entityManager;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->validator = self::getContainer()->get('validator');
        $this->entityManager = self::getContainer()->get('doctrine')->getManager();

        $databaseTool = self::getContainer()->get(DatabaseToolCollection::class)->get();

        $databaseTool->loadFixtures([
            RoleFixtures::class,
        ]);
    }

    public function testValid()
    {
        $role = (new Role())
            ->setName('myRoleName')
            ->setPrettyName('My Role Name');

        $this->assertHasValidationErrors($role, 0);
    }

    public function testInvalidEmptyName()
    {
        $role = (new Role())
            ->setName('')
            ->setPrettyName('My Role Name');

        $this->assertHasValidationErrors($role, 1);
    }

    public function testInvalidNameTooLong()
    {
        $role = (new Role())
            ->setName($this->genString(256))
            ->setPrettyName('My Role Name');

        $this->assertHasValidationErrors($role, 1);
    }

    public function testInvalidNameAlreadyExists()
    {
        $role = (new Role())
            ->setName('SuperAdmin')
            ->setPrettyName('My Role Name');

        $this->assertHasValidationErrors($role, 1);
    }

    public function testInvalidNoName()
    {
        $role = (new Role())
            ->setPrettyName('My Role Name');

        $this->assertHasValidationErrors($role, 1);
    }

    public function testInvalidEmptyPrettyName()
    {
        $role = (new Role())
            ->setName('myRoleName')
            ->setPrettyName('');

        $this->assertHasValidationErrors($role, 1);
    }

    public function testInvalidNoPrettyName()
    {
        $role = (new Role())
            ->setName('myRoleName');

        $this->assertHasValidationErrors($role, 1);
    }

    public function testInvalidPrettyNameTooLong()
    {
        $role = (new Role())
            ->setName('myRoleName')
            ->setPrettyName($this->genString(256));

        $this->assertHasValidationErrors($role, 1);
    }

    public function testInvalidPrettyNameAlreadyExists()
    {
        $role = (new Role())
            ->setName('myRoleName')
            ->setPrettyName('Super administrateur');

        $this->assertHasValidationErrors($role, 1);
    }
}
