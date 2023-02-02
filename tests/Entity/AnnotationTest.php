<?php

namespace App\Tests\Entity;

use App\Entity\Annex;
use App\Entity\Annotation;
use App\Entity\Project;
use App\Entity\Sitting;
use App\Entity\User;
use App\Tests\HasValidationError;
use App\Tests\StringTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AnnotationTest extends WebTestCase
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
        $annotation = (new Annotation())
            ->setPage(1)
            ->setText($this->genString(600))
            ->setAuthor(new User())
            ->setProject(new Project())
            ->setAnnex(new Annex())
            ->setSitting(new Sitting());

        $this->assertHasValidationErrors($annotation, 0);
    }
}
