<?php

namespace App\Tests\Entity;

use App\Entity\File;
use App\Entity\Otherdoc;
use App\Entity\Sitting;
use App\Tests\HasValidationError;
use App\Tests\StringTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class OtherdocTest extends WebTestCase
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
        $otherdoc = (new Otherdoc())
            ->setName($this->genString(55))
            ->setRank(2)
            ->setFile(new File())
            ->setSitting(new Sitting());

        $this->assertHasValidationErrors($otherdoc, 0);
    }

    public function testNameNotBlank()
    {
        $otherdoc = (new Otherdoc())
            ->setRank(2)
            ->setFile(new File())
            ->setSitting(new Sitting());

        $this->assertHasValidationErrors($otherdoc, 1);
    }

    public function testNameNotTooLong()
    {
        $otherdoc = (new Otherdoc())
            ->setName($this->genString(555))
            ->setRank(2)
            ->setFile(new File())
            ->setSitting(new Sitting());

        $this->assertHasValidationErrors($otherdoc, 1);
    }

    public function testRankNotNull()
    {
        $otherdoc = (new Otherdoc())
            ->setName($this->genString(12))
            ->setFile(new File())
            ->setSitting(new Sitting());

        $this->assertHasValidationErrors($otherdoc, 1);
    }

    public function testOtherdocFileNotNull()
    {
        $otherdoc = (new Otherdoc())
            ->setName($this->genString(55))
            ->setRank(2)
            ->setSitting(new Sitting());

        $this->assertHasValidationErrors($otherdoc, 1);
    }

    public function testOtherdocSittingNotNull()
    {
        $otherdoc = (new Otherdoc())
            ->setName($this->genString(55))
            ->setRank(2)
            ->setFile(new File());

        $this->assertHasValidationErrors($otherdoc, 1);
    }
}
