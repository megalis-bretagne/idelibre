<?php

namespace App\Tests\Service\Connector;

use App\Entity\Structure;
use App\Repository\LsvoteSittingRepository;
use App\Service\Connector\Lsvote\LsvoteClient;
use App\Service\Connector\Lsvote\LsvoteException;
use App\Service\Connector\LsvoteConnectorManager;
use App\Tests\Factory\LsvoteConnectorFactory;
use App\Tests\Factory\LsvoteSittingFactory;
use App\Tests\Factory\SittingFactory;
use App\Tests\Factory\StructureFactory;
use ContainerGhKLKiD\getContainer_EnvVarProcessorService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class LsvoteConnectorManagerTest extends KernelTestCase
{

    use ResetDatabase;
    use Factories;

    private readonly LsvoteSittingRepository $lsvoteSittingRepository;

    protected function setUp(): void
    {
        $this->lsvoteSittingRepository = self::getContainer()->get(LsvoteSittingRepository::class);
    }

//    public function testCreateConnector()
//    {
//
//
//    }

    public function testCheckApiKey()
    {
        $lsvoteClientMock = $this->getMockBuilder(LsvoteClient::class)
            ->disableOriginalConstructor()
            ->getMock();

        $lsvoteClientMock->method('checkApiKey')->willReturn(true);
        self::getContainer()->set(LsvoteClient::class, $lsvoteClientMock);

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



//    public function testSave()
//    {
//
//    }

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

        $id = $lsvoteConnectorManager->createSitting($sitting->object());
        $this->assertNull($id);
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
}
