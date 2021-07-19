<?php

namespace App\Tests\Entity;

use App\DataFixtures\TimezoneFixtures;
use App\Entity\Timezone;
use App\Tests\FindEntityTrait;
use App\Tests\HasValidationError;
use App\Tests\StringTrait;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class TimezoneTest extends WebTestCase
{
    use FindEntityTrait;
    use HasValidationError;
    use StringTrait;

    private ValidatorInterface $validator;
    private $entityManager;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->validator = self::getContainer()->get('validator');
        $this->entityManager = self::getContainer()->get('doctrine')->getManager();

        $databaseTool = self::getContainer()->get(DatabaseToolCollection::class)->get();

        $databaseTool->loadFixtures([
            TimezoneFixtures::class,
        ]);
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
