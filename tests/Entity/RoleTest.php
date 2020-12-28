<?php

namespace App\Tests\Entity;

use App\DataFixtures\RoleFixtures;
use App\Entity\Role;
use App\Tests\HasValidationError;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;


class RoleTest extends WebTestCase
{
    use FixturesTrait;
    use HasValidationError;

    private ValidatorInterface $validator;
    private $entityManager;


    protected function setUp(): void
    {
        self::bootKernel();
        $this->validator = self::$container->get('validator');
        $this->entityManager = self::$container->get('doctrine')->getManager();

        $this->loadFixtures([
            RoleFixtures::class
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
            ->setName('Name Too long Name Too long Name Too long Name Too long Name Too long 
            Name Too long Name Too long Name Too long Name Too long Name Too long Name Too long 
            Name Too long Name Too long Name Too long Name Too long Name Too long Name Too long ')
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
            ->setPrettyName('pretty Name Too Long pretty Name Too Long pretty Name Too Long pretty Name Too Long 
            pretty Name Too Long pretty Name Too Long pretty Name Too Long pretty Name Too Long pretty Name Too Long 
            pretty Name Too Long pretty Name Too Long pretty Name Too Long pretty Name Too Long pretty Name Too Long ');

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
