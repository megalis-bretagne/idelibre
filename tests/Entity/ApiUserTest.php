<?php

namespace App\Tests\Entity;

use App\Entity\ApiRole;
use App\Entity\ApiUser;
use App\Entity\Structure;
use App\Tests\HasValidationError;
use App\Tests\StringTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ApiUserTest extends WebTestCase
{
    use HasValidationError;
    use StringTrait;

    private ValidatorInterface $validator;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->validator = self::getContainer()->get('validator');
    }

    public function testIsValid()
    {
        $apiUser = (new ApiUser())
            ->setName('apiUser')
            ->setApiRole(new ApiRole())
            ->setStructure(new Structure())
            ->setToken($this->genString(32))
        ;
        $this->assertHasValidationErrors($apiUser, 0);
    }

    public function testNameNotBlank()
    {
        $apiUser = (new ApiUser())
            ->setName('')
            ->setApiRole(new ApiRole())
            ->setStructure(new Structure())
            ->setToken($this->genString(32))
        ;
        $this->assertHasValidationErrors($apiUser, 1);
    }

    public function testNameNotTooLong()
    {
        $apiUser = (new ApiUser())
            ->setName($this->genString(256))
            ->setApiRole(new ApiRole())
            ->setStructure(new Structure())
            ->setToken('token')
        ;
        $this->assertHasValidationErrors($apiUser, 1);
    }

    public function testTokenNotBlankNorNull()
    {
        $apiUser = (new ApiUser())
            ->setName('')
            ->setApiRole(new ApiRole())
            ->setStructure(new Structure())
            ->setToken('')
        ;
        $this->assertHasValidationErrors($apiUser, 2);
    }

    public function testTokenNotTooLong()
    {
        $apiUser = (new ApiUser())
            ->setName('apiUser')
            ->setApiRole(new ApiRole())
            ->setStructure(new Structure())
            ->setToken($this->genString(256))
        ;
        $this->assertHasValidationErrors($apiUser, 1);
    }

    public function testStructureNotNull()
    {
        $apiUser = (new ApiUser())
            ->setName('apiUser')
            ->setApiRole(new ApiRole())
            ->setToken('token')
        ;
        $this->assertHasValidationErrors($apiUser, 1);
    }

    public function testRoleNotNull()
    {
        $apiUser = (new ApiUser())
            ->setName('apiUser')
            ->setStructure(new Structure())
            ->setToken('token')
        ;
        $this->assertHasValidationErrors($apiUser, 1);
    }
}
