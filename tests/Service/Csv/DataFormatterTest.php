<?php

namespace App\Tests\Service\Csv;

use App\Service\Csv\DataFormatter;
use App\Service\role\RoleManager;
use App\Service\Util\GenderConverter;
use App\Tests\Factory\StructureFactory;
use App\Tests\Factory\UserFactory;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class DataFormatterTest extends KernelTestCase
{
    use ResetDatabase;
    use Factories;

    private mixed $dataFormatter;
    private mixed $roleManager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->dataFormatter = self::getContainer()->get(DataFormatter::class);
        $this->roleManager = self::getContainer()->get(RoleManager::class);
    }

    public function testFormatUsername(): void
    {
        $structure = StructureFactory::createOne(['suffix' => 'strc'])->object();
        $user = UserFactory::createOne(['username' => 'test', 'structure' => $structure])->object();
        $expected = 'test@strc';

        $this->assertEquals($expected, $this->dataFormatter->formatUsername($user->getUsername(), $structure));
    }

    public function testGetGenderCode()
    {
        $male  = $this->dataFormatter->getGenderCode(2);
        $this->assertSame(GenderConverter::MALE, $male);

        $female  = $this->dataFormatter->getGenderCode(1);
        $this->assertSame(GenderConverter::FEMALE, $female);

        $notDefined  = $this->dataFormatter->getGenderCode(0);
        $this->assertSame(GenderConverter::NOT_DEFINED, $notDefined);
    }

    public function testSanitize()
    {
        $content = ' je suis un test  ';
        $expected = 'je suis un test';
        $this->assertSame($expected, $this->dataFormatter->sanitize($content));
    }

    public function testSanitizePhoneNumber()
    {
        $expected = '0667764523';

        $phone = '06.67.76.45.23';
        $this->assertSame($expected, $this->dataFormatter->sanitizePhoneNumber($phone));

        $phone = '06-67-76-45-23';
        $this->assertSame($expected, $this->dataFormatter->sanitizePhoneNumber($phone));

        $phone = '06 67 76 45 23';
        $this->assertSame($expected, $this->dataFormatter->sanitizePhoneNumber($phone));
    }



}
