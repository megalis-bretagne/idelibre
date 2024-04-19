<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Service\Zip\ZipTokenGenerator;
use App\Tests\Factory\ConvocationFactory;
use App\Tests\Factory\SittingFactory;
use App\Tests\Factory\StructureFactory;
use App\Tests\Factory\TimestampFactory;
use App\Tests\FindEntityTrait;
use App\Tests\LoginTrait;
use App\Tests\Story\StructureStory;
use App\Tests\Story\UserStory;
use Libriciel\LshorodatageApiWrapper\LshorodatageInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class TimeStampControllerTest extends WebTestCase
{
    use ResetDatabase;
    use Factories;
    use LoginTrait;
    use FindEntityTrait;

    private ?KernelBrowser $client;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->entityManager = $kernel
            ->getContainer()
            ->get('doctrine')->getManager();

        self::ensureKernelShutdown();
        $this->client = static::createClient();
    }
    public function testIndexNoTimeStamp(): void
    {
        UserStory::load();
        $structure = StructureFactory::createOne();
        $sitting = SittingFactory::createOne(["structure" => $structure])->object();
        ConvocationFactory::createOne([
            "sitting" => $sitting,
            "sentTimestamp" => null
        ])->object();

        $this->loginAsSecretaryLibriciel();
        $this->client->request('GET', "/timestamp/sitting/{$sitting->getId()}/verify");
        $this->assertResponseStatusCodeSame(404, "Aucun jeton n'a été trouvé pour cette séance");
    }

    public function testIndexTimeStamp()
    {
        $path = str_replace("Controller", "", __DIR__, );

        StructureStory::load();
        UserStory::load();

        $this->loginAsSecretaryLibriciel();

        $mock = $this->getMockBuilder(ZipTokenGenerator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mock->method('getTimestampDirectory')->willReturn($path . 'resources/');
        self::getContainer()->set(ZipTokenGenerator::class, $mock);


        $lsHorodatageMock = $this->createMock(LshorodatageInterface::class);
        $lsHorodatageMock->method('readTimestampToken')->willReturn('token');
        $lsHorodatageMock->method('verifyTimestampToken')->willReturn(true);
        self::getContainer()->set(LshorodatageInterface::class, $lsHorodatageMock);

        $textPath = $path . 'timestampContent';
        $tsaPath = $path . 'timestampContent.tsa';

        StructureStory::libriciel();
        $sitting = SittingFactory::createOne(["structure" => StructureStory::libriciel()]);
        $timestamp = TimestampFactory::createOne([
            "sitting" => $sitting,
            "filePathContent" => $textPath,
            "filePathTsa" => $tsaPath
        ])->object();
        $convocation = ConvocationFactory::createOne([
            "sitting" => $sitting,
            "sentTimestamp" => $timestamp
        ])->object();


        $this->client->request('GET', "/timestamp/sitting/{$convocation->getSitting()->getId()}/verify");
        $this->assertResponseIsSuccessful();
    }
}
