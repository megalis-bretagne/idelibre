<?php

namespace App\Tests\Entity;

use App\DataFixtures\SittingFixtures;
use App\Entity\File;
use App\Entity\Sitting;
use App\Entity\Structure;
use App\Tests\FindEntityTrait;
use App\Tests\HasValidationError;
use DateTime;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;


class SittingTest extends WebTestCase
{
    use FixturesTrait;
    use FindEntityTrait;
    use HasValidationError;

    private ValidatorInterface $validator;
    private $entityManager;


    protected function setUp(): void
    {
        self::bootKernel();
        $this->validator = self::$container->get('validator');
        $this->entityManager = self::$container->get('doctrine')->getManager();

        $this->loadFixtures([
            SittingFixtures::class
        ]);

    }


    public function testValid()
    {
        $sitting = (new Sitting())
            ->setName('New Sitting Name')
            ->setFile(new File())
            ->setStructure(new Structure())
            ->setDate(new DateTime())
            ;

        $this->assertHasValidationErrors($sitting, 0);
    }


    public function testInvalidNoStructure()
    {
        $sitting = (new Sitting())
            ->setName('New Sitting Name')
            ->setFile(new File())
            ->setDate(new DateTime())
            ->setPlace('ici');

        $this->assertHasValidationErrors($sitting, 1);
    }

    public function testInvalidEmptyName()
    {
        $sitting = (new Sitting())
            ->setName('')
            ->setFile(new File())
            ->setStructure(new Structure())
            ->setDate(new DateTime())
            ->setPlace('ici');

        $this->assertHasValidationErrors($sitting, 1);
    }


    public function testInvalidNameTooLong()
    {
        $sitting = (new Sitting())
            ->setName('Name Too Long Name Too Long Name Too Long Name Too Long Name Too Long Name Too Long 
            Name Too Long Name Too Long Name Too Long Name Too Long Name Too Long Name Too Long Name Too Long Name Too Long 
            Name Too Long Name Too Long Name Too Long Name Too Long Name Too Long ')
            ->setFile(new File())
            ->setStructure(new Structure())
            ->setDate(new DateTime())
            ->setPlace('ici');

        $this->assertHasValidationErrors($sitting, 1);
    }


    public function testInvalidNoName()
    {
        $sitting = (new Sitting())
            ->setFile(new File())
            ->setStructure(new Structure())
            ->setDate(new DateTime())
            ->setPlace('ici');

        $this->assertHasValidationErrors($sitting, 1);
    }


    public function testInvalidNoFile()
    {
        $sitting = (new Sitting())
            ->setName('Sitting Name')
            ->setStructure(new Structure())
            ->setDate(new DateTime())
            ->setPlace('ici');

        $this->assertHasValidationErrors($sitting, 1);
    }


    public function testInvalidNoDate()
    {
        $sitting = (new Sitting())
            ->setName('Sitting Name')
            ->setFile(new File())
            ->setStructure(new Structure())
            ->setPlace('ici');

        $this->assertHasValidationErrors($sitting, 1);
    }

    public function testInvalidPlaceTooLong()
    {
        $sitting = (new Sitting())
            ->setName('My Sitting Name')
            ->setFile(new File())
            ->setStructure(new Structure())
            ->setDate(new DateTime())
            ->setPlace('place too long place too long place too long place too long place too long 
            place too long place too long place too long place too long place too long place too long place too long 
            place too long place too long place too long place too long place too long place too long ');

        $this->assertHasValidationErrors($sitting, 1);
    }



    public function testInvalidAlreadyExistSameNameSameDateTimeSameStructure()
    {
        $dbSitting = $this->getOneSittingBy(['name' => 'Conseil Libriciel']);

        $sitting = (new Sitting())
            ->setName($dbSitting->getName())
            ->setFile(new File())
            ->setStructure($dbSitting->getStructure())
            ->setDate($dbSitting->getDate())
        ;

        $this->assertHasValidationErrors($sitting, 1);
    }


    public function testValidAlreadyExistSameNameSameDateTimeOtherStructure()
    {
        $dbSitting = $this->getOneSittingBy(['name' => 'Conseil Libriciel']);

        $sitting = (new Sitting())
            ->setName($dbSitting->getName())
            ->setFile(new File())
            ->setStructure(new Structure())
            ->setDate($dbSitting->getDate())
        ;

        $this->assertHasValidationErrors($sitting, 0);
    }


    public function testValidAlreadyExistSameNameSameStructureOtherDateTime()
    {
        $dbSitting = $this->getOneSittingBy(['name' => 'Conseil Libriciel']);

        $sitting = (new Sitting())
            ->setName($dbSitting->getName())
            ->setFile(new File())
            ->setStructure($dbSitting->getStructure())
            ->setDate(new DateTime())
        ;

        $this->assertHasValidationErrors($sitting, 0);
    }

}
