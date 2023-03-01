<?php

namespace App\Tests\Service\ApiEntity;

use App\Service\ApiEntity\OtherdocApi;
use App\Tests\HasValidationError;
use App\Tests\StringTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class OtherdocApiTest extends WebTestCase
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
        $otherdocApi = (new OtherdocApi())
            ->setName($this->genString(20))
            ->setFileName($this->genString(20))
            ->setLinkedFileKey($this->genString(20))
            ->setSize(100)
            ->setRank(2)
        ;
        $this->assertHasValidationErrors($otherdocApi, 0);
    }

    public function testNameNotBlankNorNull()
    {
        $otherdocApi = (new OtherdocApi())
            ->setRank(2)
        ;
        $this->assertHasValidationErrors($otherdocApi, 1);
    }

    public function testNameTooLong()
    {
        $otherdocApi = (new OtherdocApi())
            ->setName($this->genString(501))
            ->setRank(2)
        ;
        $this->assertHasValidationErrors($otherdocApi, 1);
    }

    public function testRankNotNull()
    {
        $otherdocApi = (new OtherdocApi())
            ->setName($this->genString(501))
        ;
        $this->assertHasValidationErrors($otherdocApi, 2);
    }
}
