<?php

namespace App\Tests\Entity;

use App\Entity\Timestamp;
use App\Tests\HasValidationError;
use App\Tests\StringTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class TimestampTest extends WebTestCase
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
        $timestamp = (new Timestamp())
            ->setFilePathContent('/tmp/timestamp_1234')
            ->setFilePathTsa('/tmp/timestamp_1234.tsa');

        $this->assertHasValidationErrors($timestamp, 0);
    }

    public function testInvalidEmptyPathContent()
    {
        $timestamp = (new Timestamp())
            ->setFilePathContent('');

        $this->assertHasValidationErrors($timestamp, 1);
    }

    public function testInvalidNoPathContent()
    {
        $timestamp = new Timestamp();

        $this->assertHasValidationErrors($timestamp, 1);
    }

    public function testInvalidPathContentTooLong()
    {
        $timestamp = (new Timestamp())
            ->setFilePathContent($this->genString(256));

        $this->assertHasValidationErrors($timestamp, 1);
    }

    public function testInvalidFilePAthTsaTooLong()
    {
        $timestamp = (new Timestamp())
            ->setFilePathContent('/tmp/timestamp_1234')
            ->setFilePathTsa($this->genString(256));

        $this->assertHasValidationErrors($timestamp, 1);
    }
}
