<?php

namespace App\Tests\Service\Zip;

use App\Service\Zip\ZipTokenGenerator;
use App\Tests\Story\SittingStory;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class ZipTokenGeneratorTest extends WebTestCase
{
    use ResetDatabase;
    use Factories;

    private ZipTokenGenerator $zipTokenGenerator;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->zipTokenGenerator = self::getContainer()->get(ZipTokenGenerator::class);

        self::ensureKernelShutdown();
    }

    public function testGenerateZipToken()
    {
        $sitting = SittingStory::sittingConseilLibriciel()->object();

        $token = $this->zipTokenGenerator->generateZipToken($sitting);

        $this->assertIsString($token);
        $this->assertStringContainsString('/tmp/zip_token', $token);
    }
}
