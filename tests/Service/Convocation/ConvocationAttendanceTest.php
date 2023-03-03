<?php

namespace App\Tests\Service\Convocation;

use App\Service\Convocation\ConvocationAttendance;
use App\Tests\HasValidationError;
use App\Tests\StringTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class ConvocationAttendanceTest extends WebTestCase
{
    use ResetDatabase;
    use Factories;
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
        $convocationAttendance = (new ConvocationAttendance());

        $this->assertHasValidationErrors($convocationAttendance, 0);
    }
}
