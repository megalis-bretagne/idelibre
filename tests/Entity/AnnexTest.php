<?php

namespace App\Tests\Entity;

use App\Entity\Annex;
use App\Entity\File;
use App\Entity\Project;
use App\Tests\HasValidationError;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AnnexTest extends WebTestCase
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
        $annex = (new Annex())
            ->setRank(1)
            ->setFile(new File())
            ->setProject(new Project());
        $this->assertHasValidationErrors($annex, 0);
    }

    public function testInvalidNoRank()
    {
        $annex = $annex = (new Annex())
            ->setFile(new File())
            ->setProject(new Project());

        $this->assertHasValidationErrors($annex, 1);
    }

    public function testInvalidNoFile()
    {
        $annex = (new Annex())
            ->setRank(1)
            ->setProject(new Project());

        $this->assertHasValidationErrors($annex, 1);
    }

    public function testInvalidNoProject()
    {
        $annex = (new Annex())
            ->setRank(1)
            ->setFile(new File());

        $this->assertHasValidationErrors($annex, 1);
    }
}
