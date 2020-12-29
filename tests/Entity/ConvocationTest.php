<?php

namespace App\Tests\Entity;

use App\Entity\Convocation;
use App\Entity\Sitting;
use App\Entity\User;
use App\Tests\HasValidationError;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ConvocationTest extends WebTestCase
{
    use HasValidationError;

    private ValidatorInterface $validator;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->validator = self::$container->get('validator');
    }

    public function testValid()
    {
        $convocation = (new Convocation())
            ->setActor(new User())
            ->setSitting(new Sitting());
        $this->assertHasValidationErrors($convocation, 0);
    }

    public function testInValidNoActor()
    {
        $convocation = (new Convocation())
            ->setSitting(new Sitting());
        $this->assertHasValidationErrors($convocation, 1);
    }

    public function testInValidNoSitting()
    {
        $convocation = (new Convocation())
            ->setSitting(new Sitting());
        $this->assertHasValidationErrors($convocation, 1);
    }
}
