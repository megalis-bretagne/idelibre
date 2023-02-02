<?php

namespace App\Tests\Entity;

use App\Entity\Annotation;
use App\Entity\AnnotationUser;
use App\Entity\User;
use App\Tests\HasValidationError;
use App\Tests\StringTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AnnotationUserTest extends WebTestCase
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
        $annotationUser = (new AnnotationUser())
            ->setAnnotation(new Annotation())
            ->setUser(new User())
            ->setIsRead(true);

        $this->assertHasValidationErrors($annotationUser, 0);
    }
}
