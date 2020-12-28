<?php

namespace App\Tests\Entity;

use App\Entity\Gdpr;
use App\Tests\HasValidationError;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class GdprTest extends WebTestCase
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
        $file = new Gdpr();

        $this->assertHasValidationErrors($file, 0);
    }

    public function testInvalidAddressTooLong()
    {
        $file = (new Gdpr())
            ->setAddress('addressTooLong addressTooLong addressTooLong addressTooLong addressTooLong addressTooLong 
            addressTooLong addressTooLong addressTooLong addressTooLong addressTooLong addressTooLong addressTooLong addressTooLong 
            addressTooLong addressTooLong addressTooLong addressTooLong addressTooLong addressTooLong addressTooLong addressTooLong 
            addressTooLong addressTooLong addressTooLong addressTooLong addressTooLong addressTooLong addressTooLong addressTooLong 
            addressTooLong addressTooLong addressTooLong addressTooLong addressTooLong addressTooLong ');

        $this->assertHasValidationErrors($file, 1);
    }

    public function testInvalidApeTooLong()
    {
        $file = (new Gdpr())
            ->setApe('12345678919123456789191234567891912345678919123456789191234567891912345678919
            123456789191234567891912345678919123456789191234567891912345678919123456789191234567891912345678919
            12345678919123456789191234567891912345678919123456789191234567891912345678919');

        $this->assertHasValidationErrors($file, 1);
    }

    public function testInvalidPhoneTooLong()
    {
        $file = (new Gdpr())
            ->setCompanyPhone('12345678919123456789191234567891912345678919123456789191234567891912345678919
            123456789191234567891912345678919123456789191234567891912345678919123456789191234567891912345678919
            12345678919123456789191234567891912345678919123456789191234567891912345678919');

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
