<?php

namespace App\Tests\Entity;

use App\Entity\Structure;
use App\Entity\Timezone;
use App\Tests\FindEntityTrait;
use App\Tests\HasValidationError;
use App\Tests\Story\StructureStory;
use App\Tests\StringTrait;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class StructureTest extends WebTestCase
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

        StructureStory::load();
    }

    public function testValid()
    {
        $structure = (new Structure())
            ->setName('New Structure Name')
            ->setReplyTo('replyto@exemple.org')
            ->setSuffix('suffix.com')
            ->setTimezone(new Timezone());

        $this->assertHasValidationErrors($structure, 0);
    }

    public function testInvalidNameAlreadyExists()
    {
        $structure = (new Structure())
            ->setName('Libriciel')
            ->setReplyTo('replyto@exemple.org')
            ->setSuffix('suffix.com')
            ->setTimezone(new Timezone());

        $this->assertHasValidationErrors($structure, 1);
    }

    public function testInvalidSuffixAlreadyExists()
    {
        $structure = (new Structure())
            ->setName('new')
            ->setReplyTo('replyto@exemple.org')
            ->setSuffix('libriciel')
            ->setTimezone(new Timezone());

        $this->assertHasValidationErrors($structure, 1);
    }

    public function testInvalidNameTooLong()
    {
        $structure = (new Structure())
            ->setName($this->genString(256))
            ->setReplyTo('replyto@exemple.org')
            ->setSuffix('suffix.com')
            ->setTimezone(new Timezone());

        $this->assertHasValidationErrors($structure, 1);
    }

    public function testInvalidEmptyName()
    {
        $structure = (new Structure())
            ->setName('')
            ->setReplyTo('replyto@exemple.org')
            ->setSuffix('suffix.com')
            ->setTimezone(new Timezone());

        $this->assertHasValidationErrors($structure, 1);
    }

    public function testInvalidNoName()
    {
        $structure = (new Structure())
            ->setReplyTo('replyto@exemple.org')
            ->setSuffix('suffix.com')
            ->setTimezone(new Timezone());

        $this->assertHasValidationErrors($structure, 1);
    }

    public function testInvalidReplyToNotAnEmail()
    {
        $structure = (new Structure())
            ->setName('New Structure Name')
            ->setReplyTo('replyto.example.org')
            ->setSuffix('suffix.com')
            ->setTimezone(new Timezone());

        $this->assertHasValidationErrors($structure, 1);
    }

    public function testInvalidEmptyReplyTo()
    {
        $structure = (new Structure())
            ->setName('New Structure Name')
            ->setReplyTo('')
            ->setSuffix('suffix.com')
            ->setTimezone(new Timezone());

        $this->assertHasValidationErrors($structure, 1);
    }

    public function testInvalidNoReplyTo()
    {
        $structure = (new Structure())
            ->setName('New Structure Name')
            ->setSuffix('suffix.com')
            ->setTimezone(new Timezone());

        $this->assertHasValidationErrors($structure, 1);
    }

    public function testInvalidSuffixTooLong()
    {
        $structure = (new Structure())
            ->setName('New Structure Name')
            ->setReplyTo('replyto@exemple.org')
            ->setSuffix($this->genString(256))
            ->setTimezone(new Timezone());

        $this->assertHasValidationErrors($structure, 1);
    }

    public function testInvalidEmptySuffix()
    {
        $structure = (new Structure())
            ->setName('New Structure Name')
            ->setReplyTo('replyto@exemple.org')
            ->setSuffix('')
            ->setTimezone(new Timezone());

        $this->assertHasValidationErrors($structure, 1);
    }

    public function testInvalidNoSuffix()
    {
        $structure = (new Structure())
            ->setName('New Structure Name')
            ->setReplyTo('replyto@exemple.org')
            ->setTimezone(new Timezone());

        $this->assertHasValidationErrors($structure, 1);
    }

    public function testInvalidNoTimezone()
    {
        $structure = (new Structure())
            ->setName('New Structure Name')
            ->setReplyTo('replyto@exemple.org')
            ->setSuffix('suffix.com');

        $this->assertHasValidationErrors($structure, 1);
    }
}
