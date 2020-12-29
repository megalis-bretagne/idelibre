<?php

namespace App\Tests\Entity;

use App\Entity\ForgetToken;
use App\Entity\User;
use App\Tests\HasValidationError;
use App\Tests\StringTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ForgetTokenTest extends WebTestCase
{
    use HasValidationError;
    use StringTrait;

    private ValidatorInterface $validator;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->validator = self::$container->get('validator');
    }

    public function testValid()
    {
        $forgetToken = (new ForgetToken(new User()));

        $this->assertHasValidationErrors($forgetToken, 0);
    }

    public function testInvalidTooLongToken()
    {
        $forgetToken = (new ForgetToken(new User()));

        $forgetToken->setToken($this->genString(256));

        $this->assertHasValidationErrors($forgetToken, 1);
    }
}
