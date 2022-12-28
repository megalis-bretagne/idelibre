<?php

namespace App\Tests\Entity;

use App\Entity\Configuration;
use App\Tests\HasValidationError;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ConfigurationTest extends KernelTestCase
{
    use HasValidationError;

    private ValidatorInterface $validator;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->validator = self::getContainer()->get('validator');
    }

    public function testValid()
    {
        $configuration = (new Configuration())
            ->setIsSharedAnnotation(true)
            ->setSittingSuppressionDelay('6 months');

        $this->assertHasValidationErrors($configuration, 0);
    }

    public function testDelayNull()
    {
        $configuration = (new Configuration())
            ->setIsSharedAnnotation(true);

        $this->assertHasValidationErrors($configuration, 0);
    }
}
