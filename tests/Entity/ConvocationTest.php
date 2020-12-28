<?php

namespace App\Tests\Entity;

use App\Entity\Convocation;
use App\Entity\Sitting;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ConvocationTest extends WebTestCase
{
    private ValidatorInterface $validator;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->validator = self::$container->get('validator');
    }

    private function assertHasError(Convocation $convocation, int $number)
    {
        $errors = $this->validator->validate($convocation);
        $this->assertCount($number, $errors);
    }

    public function testValid()
    {
        $convocation = (new Convocation())
            ->setActor(new User())
            ->setSitting(new Sitting());
        $this->assertHasError($convocation, 0);
    }

    public function testInValidNoActor()
    {
        $convocation = (new Convocation())
            ->setSitting(new Sitting());
        $this->assertHasError($convocation, 1);
    }

    public function testInValidNoSitting()
    {
        $convocation = (new Convocation())
            ->setSitting(new Sitting());
        $this->assertHasError($convocation, 1);
    }
}
