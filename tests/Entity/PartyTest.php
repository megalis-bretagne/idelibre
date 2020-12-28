<?php

namespace App\Tests\Entity;

use App\DataFixtures\PartyFixtures;
use App\Entity\Party;
use App\Entity\Structure;
use App\Tests\FindEntityTrait;
use App\Tests\HasValidationError;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class PartyTest extends WebTestCase
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
            PartyFixtures::class,
        ]);
    }

    public function testValid()
    {
        $party = (new Party())
            ->setName('new party name')
            ->setStructure(new Structure());

        $this->assertHasValidationErrors($party, 0);
    }

    public function testValidSameNameOtherStructure()
    {
        $party = (new Party())
            ->setName('Majorité')
            ->setStructure(new Structure());

        $this->assertHasValidationErrors($party, 0);
    }

    public function testInvalidSameNameSameStructure()
    {
        $structure = $this->getOneStructureBy(['name' => 'Libriciel']);

        $party = (new Party())
            ->setName('Majorité')
            ->setStructure($structure);

        $this->assertHasValidationErrors($party, 1);
    }

    public function testInvalidNoStructure()
    {
        $party = (new Party())
            ->setName('new party name');

        $this->assertHasValidationErrors($party, 1);
    }

    public function testInvalidEmptyName()
    {
        $party = (new Party())
            ->setName('')
            ->setStructure(new Structure());

        $this->assertHasValidationErrors($party, 1);
    }

    public function testInvalidNameTooLong()
    {
        $party = (new Party())
            ->setName('name too long name too long name too long name too long name too long name too long 
            name too long name too long name too long name too long name too long name too long name too long name too long 
            name too long name too long name too long ')
            ->setStructure(new Structure());

        $this->assertHasValidationErrors($party, 1);
    }
}
