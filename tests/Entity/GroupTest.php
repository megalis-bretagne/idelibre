<?php

namespace App\Tests\Entity;

use App\DataFixtures\GroupFixtures;
use App\Entity\Group;
use App\Tests\HasValidationError;
use App\Tests\StringTrait;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class GroupTest extends WebTestCase
{
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
            GroupFixtures::class,
        ]);
    }

    public function testValid()
    {
        $group = (new Group())
            ->setName('new  group name');
        $this->assertHasValidationErrors($group, 0);
    }

    public function testInvalidEmptyName()
    {
        $group = (new Group())
            ->setName('');
        $this->assertHasValidationErrors($group, 1);
    }

    public function testInvalidNoName()
    {
        $group = new Group();
        $this->assertHasValidationErrors($group, 1);
    }

    public function testInvalidNameTooLong()
    {
        $group = (new Group())
            ->setName($this->genString(256));
        $this->assertHasValidationErrors($group, 1);
    }

    public function testInvalidNameAlreadyExists()
    {
        $group = (new Group())
            ->setName('Recia');
        $this->assertHasValidationErrors($group, 1);
    }
}
