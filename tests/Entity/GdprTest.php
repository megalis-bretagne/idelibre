<?php

namespace App\Tests\Entity;

use App\Entity\Gdpr;
use App\Tests\HasValidationError;
use App\Tests\StringTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class GdprTest extends WebTestCase
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
        $file = new Gdpr();

        $this->assertHasValidationErrors($file, 0);
    }

    public function testInvalidAddressTooLong()
    {
        $file = (new Gdpr())
            ->setAddress($this->genString(515));

        $this->assertHasValidationErrors($file, 1);
    }

    public function testInvalidApeTooLong()
    {
        $file = (new Gdpr())
            ->setApe($this->genString(256));

        $this->assertHasValidationErrors($file, 1);
    }

    public function testInvalidPhoneTooLong()
    {
        $file = (new Gdpr())
            ->setCompanyPhone($this->genString(256));

        $this->assertHasValidationErrors($file, 1);
    }

    public function testCompanyEmailWrongFormat()
    {
        $file = (new Gdpr())
            ->setCompanyEmail('email.toto.fr');

        $this->assertHasValidationErrors($file, 1);
    }

    public function testDpoEmailWrongFormat()
    {
        $file = (new Gdpr())
            ->setDpoEmail('email.toto.fr');

        $this->assertHasValidationErrors($file, 1);
    }
}
