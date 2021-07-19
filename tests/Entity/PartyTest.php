<?php

namespace App\Tests\Entity;

use App\DataFixtures\PartyFixtures;
use App\Entity\Party;
use App\Entity\Structure;
use App\Tests\FindEntityTrait;
use App\Tests\HasValidationError;
use App\Tests\StringTrait;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class PartyTest extends WebTestCase
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
            ->setName($this->genString(256))
            ->setStructure(new Structure());

        $this->assertHasValidationErrors($party, 1);
    }
}
