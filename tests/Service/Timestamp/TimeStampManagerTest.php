<?php

namespace App\Tests\Service\Timestamp;

use App\Service\Timestamp\TimestampManager;
use App\Service\Zip\ZipTokenGenerator;
use App\Tests\Factory\ConvocationFactory;
use App\Tests\Factory\SittingFactory;
use App\Tests\Factory\TimestampFactory;
use App\Tests\Story\StructureStory;
use Exception;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class TimeStampManagerTest extends KernelTestCase
{
    use Factories;
    use ResetDatabase;

    private TimestampManager $timestampManager;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        $this->timestampManager = self::getContainer()->get(TimestampManager::class);

        self::ensureKernelShutdown();
    }

    /**
     * @throws Exception
     */
    public function testListTimestamps()
    {
        $path = str_replace("Service/Timestamp", "", __DIR__);

        StructureStory::load();
        $mock = $this->getMockBuilder(ZipTokenGenerator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mock->method('getTimestampDirectory')->willReturn($path . 'resources/');
        self::getContainer()->set(ZipTokenGenerator::class, $mock);

        $textPath = $path . 'resources/timestampContent';
        $tsaPath = $path . 'resources/timestampContent.tsa';

        StructureStory::libriciel();
        $sitting = SittingFactory::createOne(["structure" => StructureStory::libriciel()]);
        $timestamp = TimestampFactory::createOne(["sitting" => $sitting, "filePathContent" => $textPath, "filePathTsa" => $tsaPath])->object();
        ConvocationFactory::createOne([
            "sitting" => $sitting,
            "sentTimestamp" => $timestamp
        ])->object();

        $paths = $this->timestampManager->listTimeStamps($mock->getTimestampDirectory($sitting->object()));

        $this->assertIsArray($paths);
        $this->assertArrayHasKey('file', $paths[0]);
        $this->assertArrayHasKey('tsa', $paths[0]);
    }
}
