<?php

namespace App\Tests\Service\Otherdocs;

use App\Service\Otherdoc\OtherdocManager;
use App\Tests\Factory\FileFactory;
use App\Tests\Factory\OtherdocFactory;
use App\Tests\Story\SittingStory;
use App\Tests\StringTrait;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class OtherdocManagerTest extends WebTestCase
{
    use ResetDatabase;
    use Factories;
    use StringTrait;

    private ?KernelBrowser $client;
    private ObjectManager $entityManager;
    private OtherdocManager $otherdocManager;

    protected function setUp(): void
    {
        $this->client = static::createClient();

        $kernel = self::bootKernel();
        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        $this->otherdocManager = self::getContainer()->get(OtherdocManager::class);

        self::ensureKernelShutdown();
    }

    public function testGetOtherdocFroSitting()
    {
        $sitting = SittingStory::sittingConseilLibriciel()->object();
        $file = FileFactory::createOne([
            'name' => 'file',
            'path' => '/tmp/ficher.pdf',
            'size' => 100,
        ]);
        OtherdocFactory::createMany(1, [
            'sitting' => $sitting,
            'file' => $file,
        ]);

        $otherdocs = $this->otherdocManager->getOtherdocsFromSitting($sitting);

        $this->assertCount(1, $otherdocs);
    }

    /**
     * @throws \Exception
     */
    public function testGetApiOtherdocsFromOtherdocs()
    {
        $sitting = SittingStory::sittingConseilLibriciel()->object();
        $file1 = FileFactory::createOne([
            'name' => 'file',
            'path' => '/tmp/ficher.pdf',
            'size' => 100,
        ]);
        $file2 = FileFactory::createOne([
            'name' => 'file',
            'path' => '/tmp/ficher.pdf',
            'size' => 100,
        ]);
        $od1 = OtherdocFactory::createOne([
            'sitting' => $sitting,
            'file' => $file1,
        ]);
        $od2 = OtherdocFactory::createOne([
            'sitting' => $sitting,
            'file' => $file2,
        ]);
        $otherdocsArray = [$od1, $od2];

        $otherdocsApi = $this->otherdocManager->getApiOtherdocsFromOtherdocs($otherdocsArray);

        $this->assertCount(2, $otherdocsApi);
    }
}
