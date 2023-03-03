<?php

namespace App\Tests\Entity;

use App\Entity\AttendanceToken;
use App\Entity\Convocation;
use App\Tests\HasValidationError;
use App\Tests\StringTrait;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AttendanceTokenTest extends WebTestCase
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
        $attendanceToken = (new AttendanceToken())
            ->setToken('token')
            ->setConvocation(new Convocation())
            ->setExpiredAt(new DateTime())
        ;
        $this->assertHasValidationErrors($attendanceToken, 0);
    }
}
