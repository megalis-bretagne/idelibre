<?php

namespace App\Tests\Entity;

use App\Entity\File;
use App\Tests\HasValidationError;
use App\Tests\StringTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class FileTest extends WebTestCase
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
        $file = (new File())
            ->setName('my new file.pdf')
            ->setPath('/tmp/strucutre/file.pdf');

        $this->assertHasValidationErrors($file, 0);
    }

    public function testInvalidEmptyName()
    {
        $file = (new File())
            ->setName('')
            ->setPath('/tmp/strucutre/file.pdf');

        $this->assertHasValidationErrors($file, 1);
    }

    public function testInvalidNoName()
    {
        $file = (new File())
            ->setPath('/tmp/strucutre/file.pdf');

        $this->assertHasValidationErrors($file, 1);
    }

    public function testInvalidNameTooLong()
    {
        $file = (new File())
            ->setPath('/tmp/file.pdf')
            ->setName($this->genString(126));

        $this->assertHasValidationErrors($file, 1);
    }

    public function testInvalidEmptyPath()
    {
        $file = (new File())
            ->setName('file.pdf')
            ->setPath('');

        $this->assertHasValidationErrors($file, 1);
    }

    public function testInvalidNoPath()
    {
        $file = (new File())
            ->setName('file.pdf');

        $this->assertHasValidationErrors($file, 1);
    }

    public function testInvalidPathTooLong()
    {
        $file = (new File())
            ->setPath($this->genString(256))
            ->setPath('file.pdf');

        $this->assertHasValidationErrors($file, 1);
    }
}
