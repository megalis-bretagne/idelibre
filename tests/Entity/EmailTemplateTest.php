<?php

namespace App\Tests\Entity;

use App\DataFixtures\EmailTemplateFixtures;
use App\Entity\EmailTemplate;
use App\Entity\Structure;
use App\Tests\FindEntityTrait;
use App\Tests\HasValidationError;
use App\Tests\StringTrait;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class EmailTemplateTest extends WebTestCase
{
    use FindEntityTrait;
    use HasValidationError;
    use StringTrait;

    private $validator;
    private $entityManager;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->validator = self::getContainer()->get('validator');
        $this->entityManager = self::getContainer()->get('doctrine')->getManager();

        $databaseTool = self::getContainer()->get(DatabaseToolCollection::class)->get();

        $databaseTool->loadFixtures([
            EmailTemplateFixtures::class,
        ]);
    }

    public function testValid()
    {
        $emailTemplate = (new EmailTemplate())
            ->setName('new name')
            ->setContent('my new email content')
            ->setSubject('my new subject')
            ->setStructure(new Structure());
        $this->assertHasValidationErrors($emailTemplate, 0);
    }

    public function testValidNameAlreadyExistsInOtherStructure()
    {
        $emailTemplate = (new EmailTemplate())
            ->setName('Conseil Libriciel')
            ->setContent('my new email content')
            ->setSubject('my new subject')
            ->setStructure(new Structure());
        $this->assertHasValidationErrors($emailTemplate, 0);
    }

    public function testInValidNameAlreadyExistsInSameStructure()
    {
        $structure = $this->getOneStructureBy(['name' => 'Libriciel']);
        $emailTemplate = (new EmailTemplate())
            ->setName('Conseil Libriciel')
            ->setContent('my new email content')
            ->setSubject('my new subject')
            ->setStructure($structure);
        $this->assertHasValidationErrors($emailTemplate, 1);
    }

    public function testInvalidNameTooLong()
    {
        $emailTemplate = (new EmailTemplate())
            ->setName($this->genString(256))
            ->setContent('my new email content')
            ->setSubject('my new subject')
            ->setStructure(new Structure());
        $this->assertHasValidationErrors($emailTemplate, 1);
    }

    public function testInvalidNoName()
    {
        $emailTemplate = (new EmailTemplate())
            ->setContent('my new email content')
            ->setSubject('my new subject')
            ->setStructure(new Structure());
        $this->assertHasValidationErrors($emailTemplate, 1);
    }

    public function testInvalidEmptyName()
    {
        $emailTemplate = (new EmailTemplate())
            ->setName('')
            ->setContent('my new email content')
            ->setSubject('my new subject')
            ->setStructure(new Structure());
        $this->assertHasValidationErrors($emailTemplate, 1);
    }

    public function testInvalidNoStructure()
    {
        $emailTemplate = (new EmailTemplate())
            ->setName('My new name')
            ->setContent('my new email content')
            ->setSubject('my new subject');
        $this->assertHasValidationErrors($emailTemplate, 1);
    }

    public function testInvalidNoContent()
    {
        $emailTemplate = (new EmailTemplate())
            ->setName('My new name')
            ->setSubject('my new subject')
            ->setStructure(new Structure());
        $this->assertHasValidationErrors($emailTemplate, 1);
    }

    public function testInvalidEmptyContent()
    {
        $emailTemplate = (new EmailTemplate())
            ->setName('My new name')
            ->setContent('')
            ->setSubject('my new subject')
            ->setStructure(new Structure());
        $this->assertHasValidationErrors($emailTemplate, 1);
    }

    public function testInvalidNoSubject()
    {
        $emailTemplate = (new EmailTemplate())
            ->setName('My new name')
            ->setContent('my content')
            ->setStructure(new Structure());
        $this->assertHasValidationErrors($emailTemplate, 1);
    }

    public function testInvalidEmptySubject()
    {
        $emailTemplate = (new EmailTemplate())
            ->setName('My new name')
            ->setContent('my content')
            ->setSubject('')
            ->setStructure(new Structure());
        $this->assertHasValidationErrors($emailTemplate, 1);
    }

    public function testInvalidSubjectTooLong()
    {
        $emailTemplate = (new EmailTemplate())
            ->setName('My new name')
            ->setContent('my content')
            ->setSubject($this->genString(256))
            ->setStructure(new Structure());
        $this->assertHasValidationErrors($emailTemplate, 1);
    }
}
