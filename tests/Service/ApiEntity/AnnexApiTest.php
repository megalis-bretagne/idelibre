<?php

namespace App\Tests\Service\ApiEntity;

use App\Service\ApiEntity\AnnexApi;
use App\Tests\HasValidationError;
use App\Tests\StringTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AnnexApiTest extends WebTestCase
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
        $annexApi = (new AnnexApi())
            ->setRank(2)
            ->setFileName($this->genString(20))
            ->setLinkedFileKey($this->genString(20))
        ;
        $this->assertHasValidationErrors($annexApi, 0);
    }

    public function testRankNotNullNorEmpty()
    {
        $annexApi = (new AnnexApi())
            ->setFileName($this->genString(20))
            ->setLinkedFileKey($this->genString(20))
        ;
        $this->assertHasValidationErrors($annexApi, 1);
    }
}
