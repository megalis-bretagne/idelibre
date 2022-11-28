<?php

namespace App\Tests\Entity;

use App\Entity\Party;
use App\Entity\Structure;
use App\Tests\FindEntityTrait;
use App\Tests\HasValidationError;
use App\Tests\Story\PartyStory;
use App\Tests\StringTrait;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class PartyTest extends WebTestCase
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

        PartyStory::load();
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
            ->setName($this->genString(256))
            ->setStructure(new Structure());

        $this->assertHasValidationErrors($party, 1);
    }
}
