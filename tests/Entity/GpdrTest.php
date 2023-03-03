<?php

namespace App\Tests\Entity;

use App\Entity\Gdpr\GdprHosting;
use App\Tests\HasValidationError;
use App\Tests\StringTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class GpdrTest extends WebTestCase
{
    use HasValidationError;
    use StringTrait;

    private ValidatorInterface $validator;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->validator = self::getContainer()->get('validator');
    }

    public function testValid()
    {
        $file = new GdprHosting();

        $this->assertHasValidationErrors($file, 0);
    }

    public function testInvalidAddressTooLong()
    {
        $file = (new GdprHosting())
            ->setAddress($this->genString(515));

        $this->assertHasValidationErrors($file, 1);
    }

    public function testInvalidApeTooLong()
    {
        $file = (new GdprHosting())
            ->setApe($this->genString(256));

        $this->assertHasValidationErrors($file, 1);
    }

    public function testInvalidPhoneTooLong()
    {
        $file = (new GdprHosting())
            ->setCompanyPhone($this->genString(256));

        $this->assertHasValidationErrors($file, 1);
    }

    public function testCompanyEmailWrongFormat()
    {
        $file = (new \App\Entity\Gdpr\GdprHosting())
            ->setCompanyEmail('email.toto.fr');

        $this->assertHasValidationErrors($file, 1);
    }
}
