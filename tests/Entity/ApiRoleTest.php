<?php

namespace App\Tests\Entity;

use App\Entity\ApiRole;
use App\Tests\HasValidationError;
use App\Tests\StringTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ApiRoleTest extends WebTestCase
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
        $apiRole = (new ApiRole())
            ->setName($this->genString(10))
            ->setPrettyName($this->genString(10))
            ->setComposites(['ben', 'doc', 'api'])
        ;
        $this->assertHasValidationErrors($apiRole, 0);
    }
}
