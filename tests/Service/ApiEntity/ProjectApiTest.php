<?php

namespace App\Tests\Service\ApiEntity;

use App\Service\ApiEntity\ProjectApi;
use App\Tests\HasValidationError;
use App\Tests\StringTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ProjectApiTest extends WebTestCase
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
        $projectApi = (new ProjectApi())
            ->setName($this->genString(20))
            ->setFileName($this->genString(20))
            ->setLinkedFileKey($this->genString(20))
            ->setRank(2)
            ->setSize(100)
            ;
        $this->assertHasValidationErrors($projectApi, 0);
    }

    public function testNameNotBlankNorNull()
    {
        $projectApi = (new ProjectApi())
            ->setRank(2)
        ;
        $this->assertHasValidationErrors($projectApi, 1);
    }

    public function testNameTooLong()
    {
        $projectApi = (new ProjectApi())
            ->setName($this->genString(501))
            ->setRank(2)
            ;
        $this->assertHasValidationErrors($projectApi, 1);
    }

    public function testRankNotBlankNorNull()
    {
        $projectApi = (new ProjectApi())
            ->setName($this->genString(50))
        ;
        $this->assertHasValidationErrors($projectApi, 1);
    }
}
