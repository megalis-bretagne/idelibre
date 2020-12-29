<?php

namespace App\Tests\Entity;

use App\DataFixtures\TypeFixtures;
use App\Entity\Structure;
use App\Entity\Type;
use App\Tests\FindEntityTrait;
use App\Tests\HasValidationError;
use App\Tests\StringTrait;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class TypeTest extends WebTestCase
{
    use FixturesTrait;
    use FindEntityTrait;
    use HasValidationError;
    use StringTrait;

    private ValidatorInterface $validator;
    private $entityManager;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->validator = self::$container->get('validator');
        $this->entityManager = self::$container->get('doctrine')->getManager();

        $this->loadFixtures([
            TypeFixtures::class,
        ]);
    }

    public function testValid()
    {
        $type = (new Type())
            ->setName('New Type Name')
            ->setStructure(new Structure());

        $this->assertHasValidationErrors($type, 0);
    }

    public function testInvalidEmptyName()
    {
        $type = (new Type())
            ->setName('')
            ->setStructure(new Structure());

        $this->assertHasValidationErrors($type, 1);
    }

    public function testInvalidNameTooLong()
    {
        $type = (new Type())
            ->setName($this->genString(256))
            ->setStructure(new Structure());

        $this->assertHasValidationErrors($type, 1);
    }

    public function testInvalidNoName()
    {
        $type = (new Type())
            ->setStructure(new Structure());

        $this->assertHasValidationErrors($type, 1);
    }

    public function testInvalidNameSameNameInSameStructure()
    {
        $dbType = $this->getOneTypeBy(['name' => 'Bureau Communautaire Libriciel']);

        $type = (new Type())
            ->setName($dbType->getName())
            ->setStructure($dbType->getStructure());

        $this->assertHasValidationErrors($type, 1);
    }

    public function testValidNameSameNameInOtherStructure()
    {
        $dbType = $this->getOneTypeBy(['name' => 'Bureau Communautaire Libriciel']);

        $type = (new Type())
            ->setName($dbType->getName())
            ->setStructure(new Structure());

        $this->assertHasValidationErrors($type, 0);
    }

    public function testInvalidNoStructure()
    {
        $type = (new Type())
            ->setName('New Type Name');

        $this->assertHasValidationErrors($type, 1);
    }
}
