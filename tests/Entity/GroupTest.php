<?php

namespace App\Tests\Entity;

use App\Entity\Group;
use App\Tests\HasValidationError;
use App\Tests\Story\GroupStory;
use App\Tests\StringTrait;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class GroupTest extends WebTestCase
{
    use ResetDatabase;
    use Factories;
    use HasValidationError;
    use StringTrait;

    private ValidatorInterface $validator;
    private ObjectManager $entityManager;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->validator = self::getContainer()->get('validator');
        $this->entityManager = self::getContainer()->get('doctrine')->getManager();

        GroupStory::load();
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
