<?php

namespace App\Tests\Service\Csv;

use App\Service\Csv\CsvUserErrorManager;
use App\Tests\Factory\StructureFactory;
use App\Tests\Factory\UserFactory;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\ConstraintViolationList;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class CsvUserErrorManagerTest extends KernelTestCase
{

    use Factories;
    use ResetDatabase;

    private CsvUserErrorManager $csvUserErrorManager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->csvUserErrorManager = self::getContainer()->get(CsvUserErrorManager::class);
    }

    public function testIsExistUsername(): void
    {
        $structure = StructureFactory::createOne()->object();
        $user = UserFactory::createOne(['username' => 'test', 'structure' => $structure])->object();
        $this->assertTrue($this->csvUserErrorManager->isExistUsername($user->getUsername(), $structure));

        $structure2 = StructureFactory::createOne()->object();
        $user2 = UserFactory::createOne(['username' => 'test2', 'structure' => $structure2])->object();
        $this->assertFalse($this->csvUserErrorManager->isExistUsername($user2->getUsername(), $structure));
    }

    public function testPreSavingValidation(): void
    {
        $record = [
            'username' => 'username@lib',
            'firstname' => 'firstname',
            'lastname' => 'lastname',
            'email' => '',
            'role' => '3',
        ];

        $validation = $this->csvUserErrorManager->preSavingValidation($record);
        $this->assertNotNull($validation);
        $this->assertCount(1, $validation);

        $record2 = [
            'username' => '',
            'firstname' => 'firstname',
            'lastname' => 'lastname',
            'email' => 'email@email.com',
            'role' => '3',
        ];
        $validation2 = $this->csvUserErrorManager->preSavingValidation($record2);
        $this->assertNotNull($validation2);
        $this->assertCount(1, $validation2);
    }

    public function testPostSavingValidation()
    {
        $structure = StructureFactory::createOne()->object();
        $user = UserFactory::createOne(['username' => 'test', 'structure' => $structure])->object();
        $csvEmails = ['test'];
        $record = [
            'username' => 'test',
            'firstname' => 'firstname',
            'lastname' => 'lastname',
            'email' => 'email@lib',
            'role' => '',
        ];
        $validation = $this->csvUserErrorManager->postSavingValidation($record, $user, $csvEmails);
        $this->assertNotNull($validation);
        $this->assertCount(1, $validation);

        $record2 = [
            'username' => 'test2',
            'firstname' => 'firstname',
            'lastname' => 'lastname',
            'email' => 'email@lib',
            'role' => '3',
        ];
        $validation2 = $this->csvUserErrorManager->postSavingValidation($record2, $user, $csvEmails);
        $this->assertNotNull($validation2);
    }
}
