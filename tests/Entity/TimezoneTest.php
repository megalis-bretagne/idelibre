<?php

namespace App\Tests\Entity;

use App\Entity\Timezone;
use App\Tests\FindEntityTrait;
use App\Tests\HasValidationError;
use App\Tests\Story\TimezoneStory;
use App\Tests\StringTrait;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class TimezoneTest extends WebTestCase
{
    use ResetDatabase;
    use Factories;
    use FindEntityTrait;
    use HasValidationError;
    use StringTrait;

    private ValidatorInterface $validator;
    private ObjectManager $entityManager;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->validator = self::getContainer()->get('validator');
        $this->entityManager = self::getContainer()->get('doctrine')->getManager();

        TimezoneStory::load();
    }

    public function testValid()
    {
        $timezone = (new Timezone())
            ->setName('France/Montpellier')
        ;

        $this->assertHasValidationErrors($timezone, 0);
    }

    public function testInvalidAlreadyExistName()
    {
        $timezone = (new Timezone())
            ->setName('Europe/Paris');

        $this->assertHasValidationErrors($timezone, 1);
    }

    public function testInvalidEmptyName()
    {
        $timezone = (new Timezone())
            ->setName('');

        $this->assertHasValidationErrors($timezone, 1);
    }

    public function testInvalidNoName()
    {
        $timezone = new Timezone();

        $this->assertHasValidationErrors($timezone, 1);
    }

    public function testInvalidNameTooLong()
    {
        $timezone = (new Timezone())
            ->setName($this->genString(256));

        $this->assertHasValidationErrors($timezone, 1);
    }

    public function testInvalidInfoTooLong()
    {
        $timezone = (new Timezone())
            ->setName($this->genString(20))
            ->setInfo($this->genString(256));

        $this->assertHasValidationErrors($timezone, 1);
    }
}
