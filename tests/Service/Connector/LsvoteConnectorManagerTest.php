<?php

namespace App\Tests\Service\Connector;

use App\Entity\Structure;
use App\Repository\LsvoteConnectorRepository;
use App\Repository\LsvoteSittingRepository;
use App\Service\Connector\Lsvote\LsvoteClient;
use App\Service\Connector\Lsvote\LsvoteException;
use App\Service\Connector\LsvoteConnectorManager;
use App\Service\Connector\LsvoteResultException;
use App\Service\Connector\LsvoteSittingCreationException;
use App\Tests\Factory\LsvoteConnectorFactory;
use App\Tests\Factory\LsvoteSittingFactory;
use App\Tests\Factory\SittingFactory;
use App\Tests\Factory\StructureFactory;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Throwable;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class LsvoteConnectorManagerTest extends WebTestCase
{

    use ResetDatabase;
    use Factories;

    private readonly LsvoteSittingRepository $lsvoteSittingRepository;
    private readonly LsvoteConnectorManager $lsvoteConnectorManager;
    private readonly LsvoteConnectorRepository $lsvoteConnectorRepository;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->lsvoteConnectorManager = self::getContainer()->get(LsvoteConnectorManager::class);
        $this->lsvoteSittingRepository = self::getContainer()->get(LsvoteSittingRepository::class);
        $this->lsvoteConnectorRepository = self::getContainer()->get(LsvoteConnectorRepository::class);
        self::ensureKernelShutdown();
    }

    public function testCheckApiKey()
    {
        $lsvoteClientMock = $this->getMockBuilder(LsvoteClient::class)
            ->disableOriginalConstructor()
            ->getMock();

        $lsvoteClientMock->method('checkApiKey')->willReturn(true);
        $service = self::getContainer()->set(LsvoteClient::class, $lsvoteClientMock);

        /** @var LsvoteConnectorManager $lsvoteConnectorManager */
        $lsvoteConnectorManager = self::getContainer()->get(LsvoteConnectorManager::class);

        $res = $lsvoteConnectorManager->checkApiKey("https://test.fr", '1234');

        $this->assertTrue($res);
    }


    public function testCheckApiKeyError()
    {
        $structure = StructureFactory::createOne([
        ])->object();

        LsvoteConnectorFactory::createOne(['structure' => $structure]);

        $lsvoteClientMock = $this->getMockBuilder(LsvoteClient::class)
            ->disableOriginalConstructor()
            ->getMock();

        $lsvoteClientMock->method('checkApiKey')->willThrowException(new LsvoteException("key error"));
        self::getContainer()->set(LsvoteClient::class, $lsvoteClientMock);

        /** @var LsvoteConnectorManager $lsvoteConnectorManager */
        $lsvoteConnectorManager = self::getContainer()->get(LsvoteConnectorManager::class);

        $res = $lsvoteConnectorManager->checkApiKey("https://test.fr", '1234');

        $this->assertFalse($res);
    }

    public function testGetLsvoteConnector()
    {
        $structure = StructureFactory::createOne();
        LsvoteConnectorFactory::createOne(["structure" => $structure]);

        $expected = $this->lsvoteConnectorRepository->findOneBy(["structure" => $structure->getId()]);
        $connector = $this->lsvoteConnectorManager->getLsvoteConnector($structure->object());

        $this->assertSame($expected->getStructure()->getId(), $connector->getStructure()->getId());
    }

    public function testCreateSitting()
    {
        /** @var Structure $structure */
        $structure = StructureFactory::createOne([])->object();

        $sitting = SittingFactory::createOne(["structure" => $structure]);

        LsvoteConnectorFactory::createOne(['structure' => $structure]);

        $lsvoteClientMock = $this->getMockBuilder(LsvoteClient::class)
            ->disableOriginalConstructor()
            ->getMock();

        $lsvoteClientMock->method('sendSitting')->willReturn("ca330f73-ccf2-46a4-a0c3-abe0d7e46689");
        self::getContainer()->set(LsvoteClient::class, $lsvoteClientMock);

        /** @var LsvoteConnectorManager $lsvoteConnectorManager */
        $lsvoteConnectorManager = self::getContainer()->get(LsvoteConnectorManager::class);

        $id = $lsvoteConnectorManager->createSitting($sitting->object());
        $this->assertSame("ca330f73-ccf2-46a4-a0c3-abe0d7e46689", $id);

        $this-> assertNotNull($sitting->getLsvoteSitting()->getLsvoteSittingId());

    }

    public function testSittingNotCreated()
    {
        /** @var Structure $structure */
        $structure = StructureFactory::createOne([
        ])->object();

        $sitting = SittingFactory::createOne(["structure" => $structure]);

        LsvoteConnectorFactory::createOne(['structure' => $structure]);

        $lsvoteClientMock = $this->getMockBuilder(LsvoteClient::class)
            ->disableOriginalConstructor()
            ->getMock();

        $lsvoteClientMock->method('sendSitting')->willThrowException(new LsvoteException());
        self::getContainer()->set(LsvoteClient::class, $lsvoteClientMock);

        /** @var LsvoteConnectorManager $lsvoteConnectorManager */
        $lsvoteConnectorManager = self::getContainer()->get(LsvoteConnectorManager::class);

        $this->expectException(LsvoteSittingCreationException::class);
        $id = $lsvoteConnectorManager->createSitting($sitting->object());
    }

    public function testDeleteLsvoteSitting()
    {
        /** @var Structure $structure */
        $structure = StructureFactory::createOne([])->object();

        $lsvoteSitting = LsvoteSittingFactory::createOne([
            "results" => [],
            "sitting" => SittingFactory::createOne(['structure' => $structure]),
        ])->object();

        LsvoteConnectorFactory::createOne(['structure' => $structure]);

        $lsvoteClientMock = $this->getMockBuilder(LsvoteClient::class)
            ->disableOriginalConstructor()
            ->getMock();

        $lsvoteClientMock->method('deleteSitting')->willReturn(true);
        self::getContainer()->set(LsvoteClient::class, $lsvoteClientMock);

        /** @var LsvoteConnectorManager $lsvoteConnectorManager */
        $lsvoteConnectorManager = self::getContainer()->get(LsvoteConnectorManager::class);

        $res = $lsvoteConnectorManager->deleteLsvoteSitting($lsvoteSitting->getSitting());
        $this->assertTrue($res);
    }

    public function testDeleteLsvoteSittingFailed()
    {
        /** @var Structure $structure */
        $structure = StructureFactory::createOne([])->object();

        $lsvoteSitting = LsvoteSittingFactory::createOne([
            "results" => [],
            "sitting" => SittingFactory::createOne(['structure' => $structure]),
        ])->object();

        LsvoteConnectorFactory::createOne(['structure' => $structure]);

        $lsvoteClientMock = $this->getMockBuilder(LsvoteClient::class)
            ->disableOriginalConstructor()
            ->getMock();

        $lsvoteClientMock->method('deleteSitting')->willThrowException(new LsvoteException());
        self::getContainer()->set(LsvoteClient::class, $lsvoteClientMock);

        /** @var LsvoteConnectorManager $lsvoteConnectorManager */
        $lsvoteConnectorManager = self::getContainer()->get(LsvoteConnectorManager::class);

        $res = $lsvoteConnectorManager->deleteLsvoteSitting($lsvoteSitting->getSitting());
        $this->assertFalse($res);
    }


    public function testGetLsvoteSittingResults()
    {
        /** @var Structure $structure */
        $structure = StructureFactory::createOne([])->object();

        $lsvoteSitting = LsvoteSittingFactory::createOne([
            "results" => [],
            "sitting" => SittingFactory::createOne(['structure' => $structure]),
        ])->object();

        LsvoteConnectorFactory::createOne(['structure' => $structure]);

        $lsvoteClientMock = $this->getMockBuilder(LsvoteClient::class)
            ->disableOriginalConstructor()
            ->getMock();

        $lsvoteClientMock->method('resultSitting')->willReturn(['ok']);
        self::getContainer()->set(LsvoteClient::class, $lsvoteClientMock);

        /** @var LsvoteConnectorManager $lsvoteConnectorManager */
        $lsvoteConnectorManager = self::getContainer()->get(LsvoteConnectorManager::class);

        $res = $lsvoteConnectorManager->getLsvoteSittingResults($lsvoteSitting->getSitting());
        $this->assertIsArray($res);
    }

    public function testGetLsvoteSittingResultsFailed()
    {
        /** @var Structure $structure */
        $structure = StructureFactory::createOne([])->object();

        $lsvoteSitting = LsvoteSittingFactory::createOne([
            "results" => [],
            "sitting" => SittingFactory::createOne(['structure' => $structure]),
        ])->object();

        LsvoteConnectorFactory::createOne(['structure' => $structure]);

        $lsvoteClientMock = $this->getMockBuilder(LsvoteClient::class)
            ->disableOriginalConstructor()
            ->getMock();

        $lsvoteClientMock->method('resultSitting')->willThrowException(new LsvoteResultException());
        self::getContainer()->set(LsvoteClient::class, $lsvoteClientMock);

        /** @var LsvoteConnectorManager $lsvoteConnectorManager */
        $lsvoteConnectorManager = self::getContainer()->get(LsvoteConnectorManager::class);

        $this->expectException(LsvoteResultException::class);
        $res = $lsvoteConnectorManager->getLsvoteSittingResults($lsvoteSitting->getSitting());

    }

    public function testCreateJsonFile()
    {
        $structure = StructureFactory::createOne([])->object();
        $sitting = SittingFactory::createOne(['structure' => $structure]);

        $lsvoteSitting = LsvoteSittingFactory::createOne([
            "results" => [],
            "sitting" => $sitting,
        ])->object();
        $path = $this->lsvoteConnectorManager->createJsonFile($sitting->object());

        $this->assertIsString($path);
        $this->assertStringContainsString('/tmp/', $path);
    }

    public function testEditLsvoteSitting()
    {
        $structure = StructureFactory::createOne([])->object();
        $sitting = SittingFactory::createOne(['structure' => $structure]);

        $lsvoteSitting = LsvoteSittingFactory::createOne([
            "results" => [],
            "sitting" => $sitting,
        ])->object();

        LsvoteConnectorFactory::createOne(['structure' => $structure]);

        $lsvoteClientMock = $this->getMockBuilder(LsvoteClient::class)
            ->disableOriginalConstructor()
            ->getMock();

        $lsvoteClientMock->method('reSendSitting')->willReturn("ca330f73-ccf2-46a4-a0c3-abe0d7e46689");
        self::getContainer()->set(LsvoteClient::class, $lsvoteClientMock);

        /** @var LsvoteConnectorManager $lsvoteConnectorManager */
        $lsvoteConnectorManager = self::getContainer()->get(LsvoteConnectorManager::class);

        $id = $lsvoteConnectorManager->editLsvoteSitting($sitting->object());
        $this->assertSame("ca330f73-ccf2-46a4-a0c3-abe0d7e46689", $id);

        $this-> assertNotNull($sitting->getLsvoteSitting()->getLsvoteSittingId());

    }

    public function testEditLsvoteSittingFailed()
    {
        $structure = StructureFactory::createOne([])->object();
        $sitting = SittingFactory::createOne(['structure' => $structure]);

        $lsvoteSitting = LsvoteSittingFactory::createOne([
            "results" => [],
            "sitting" => $sitting,
        ])->object();

        LsvoteConnectorFactory::createOne(['structure' => $structure]);

        $lsvoteClientMock = $this->getMockBuilder(LsvoteClient::class)
            ->disableOriginalConstructor()
            ->getMock();

        $lsvoteClientMock->method('reSendSitting')->willThrowException(new LsvoteSittingCreationException());
        self::getContainer()->set(LsvoteClient::class, $lsvoteClientMock);

        /** @var LsvoteConnectorManager $lsvoteConnectorManager */
        $lsvoteConnectorManager = self::getContainer()->get(LsvoteConnectorManager::class);

        $this->expectException(LsvoteSittingCreationException::class);
        $id = $lsvoteConnectorManager->editLsvoteSitting($sitting->object());

    }
}
