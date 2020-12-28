<?php

namespace App\Tests\Entity;

use App\DataFixtures\EmailTemplateFixtures;
use App\Entity\EmailTemplate;
use App\Entity\Structure;
use App\Tests\FindEntityTrait;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class EmailTemplateTest extends WebTestCase
{
    use FixturesTrait;
    use FindEntityTrait;

    private ValidatorInterface $validator;
    private $entityManager;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->validator = self::$container->get('validator');
        $this->entityManager = self::$container->get('doctrine')->getManager();

        $this->loadFixtures([
            EmailTemplateFixtures::class,
        ]);
    }

    private function assertHasError(EmailTemplate $emailTemplate, int $number)
    {
        $errors = $this->validator->validate($emailTemplate);
        $this->assertCount($number, $errors);
    }

    public function testValid()
    {
        $emailTemplate = (new EmailTemplate())
            ->setName('new name')
            ->setContent('my new email content')
            ->setSubject('my new subject')
            ->setStructure(new Structure());
        $this->assertHasError($emailTemplate, 0);
    }

    public function testValidNameAlreadyExistsInOtherStructure()
    {
        $emailTemplate = (new EmailTemplate())
            ->setName('Conseil Libriciel')
            ->setContent('my new email content')
            ->setSubject('my new subject')
            ->setStructure(new Structure());
        $this->assertHasError($emailTemplate, 0);
    }

    public function testInValidNameAlreadyExistsInSameStructure()
    {
        $structure = $this->getOneStructureBy(['name' => 'Libriciel']);
        $emailTemplate = (new EmailTemplate())
            ->setName('Conseil Libriciel')
            ->setContent('my new email content')
            ->setSubject('my new subject')
            ->setStructure($structure);
        $this->assertHasError($emailTemplate, 1);
    }

    public function testInvalidNameTooLong()
    {
        $emailTemplate = (new EmailTemplate())
            ->setName('My new name way too long ! My new name way too long ! My new name way too long ! My new name way too long ! 
            My new name way too long ! My new name way too long ! My new name way too long ! My new name way too long ! 
            My new name way too long ! My new name way too long ! My new name way too long ! My new name way too long ! ')
            ->setContent('my new email content')
            ->setSubject('my new subject')
            ->setStructure(new Structure());
        $this->assertHasError($emailTemplate, 1);
    }

    public function testInvalidNoName()
    {
        $emailTemplate = (new EmailTemplate())
            ->setContent('my new email content')
            ->setSubject('my new subject')
            ->setStructure(new Structure());
        $this->assertHasError($emailTemplate, 1);
    }

    public function testInvalidEmptyName()
    {
        $emailTemplate = (new EmailTemplate())
            ->setName('')
            ->setContent('my new email content')
            ->setSubject('my new subject')
            ->setStructure(new Structure());
        $this->assertHasError($emailTemplate, 1);
    }

    public function testInvalidNoStructure()
    {
        $emailTemplate = (new EmailTemplate())
            ->setName('My new name')
            ->setContent('my new email content')
            ->setSubject('my new subject');
        $this->assertHasError($emailTemplate, 1);
    }

    public function testInvalidNoContent()
    {
        $emailTemplate = (new EmailTemplate())
            ->setName('My new name')
            ->setSubject('my new subject')
            ->setStructure(new Structure());
        $this->assertHasError($emailTemplate, 1);
    }

    public function testInvalidEmptyContent()
    {
        $emailTemplate = (new EmailTemplate())
            ->setName('My new name')
            ->setContent('')
            ->setSubject('my new subject')
            ->setStructure(new Structure());
        $this->assertHasError($emailTemplate, 1);
    }

    public function testInvalidNoSubject()
    {
        $emailTemplate = (new EmailTemplate())
            ->setName('My new name')
            ->setContent('my content')
            ->setStructure(new Structure());
        $this->assertHasError($emailTemplate, 1);
    }

    public function testInvalidEmptySubject()
    {
        $emailTemplate = (new EmailTemplate())
            ->setName('My new name')
            ->setContent('my content')
            ->setSubject('')
            ->setStructure(new Structure());
        $this->assertHasError($emailTemplate, 1);
    }

    public function testInvalidSubjectTooLong()
    {
        $emailTemplate = (new EmailTemplate())
            ->setName('My new name')
            ->setContent('my content')
            ->setSubject('My way too long subject ! My way too long subject ! My way too long subject ! 
            My way too long subject ! My way too long subject ! My way too long subject ! My way too long subject ! 
            My way too long subject ! My way too long subject ! My way too long subject ! My way too long subject ! ')
            ->setStructure(new Structure());
        $this->assertHasError($emailTemplate, 1);
    }
}
