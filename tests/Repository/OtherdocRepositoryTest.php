<?php

namespace App\Tests\Repository;

use App\Repository\OtherdocRepository;
use App\Tests\Factory\FileFactory;
use App\Tests\Factory\OtherdocFactory;
use App\Tests\Factory\SittingFactory;
use App\Tests\FindEntityTrait;
use App\Tests\LoginTrait;
use App\Tests\Story\SittingStory;
use App\Tests\Story\StructureStory;
use DateTime;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class OtherdocRepositoryTest extends WebTestCase
{
    use ResetDatabase;
    use Factories;
    use FindEntityTrait;
    use LoginTrait;

    private ?KernelBrowser $client;
    private ObjectManager $entityManager;
    private OtherdocRepository $otherdocRepository;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        $this->otherdocRepository = self::getContainer()->get(OtherdocRepository::class);

        self::ensureKernelShutdown();
        $this->client = static::createClient();
    }

    public function testGetOtherdocsWithAssociatedEntities()
    {
        $sitting = SittingStory::sittingConseilLibriciel()->object();
        $file = FileFactory::createOne([
            'path' => '/tmp/fichier.pdf',
            'name' => 'ficher.pdf',
            'size' => 100, ]);

        $od = OtherdocFactory::createMany(1, [
            'file' => $file,
            'sitting' => $sitting,
        ]);

        $otherdoc = $this->otherdocRepository->getOtherdocsWithAssociatedEntities($sitting);
        $this->assertCount(1, $otherdoc);
    }

    public function testFindNotInListOtherdocs()
    {
        $structure = StructureStory::libriciel()->object();
        $sitting = SittingFactory::createOne([
            'structure' => $structure,
            'date' => new DateTime(),
        ])->object();
        $file1 = FileFactory::createOne([
            'path' => '/tmp/fichier.pdf',
            'name' => 'ficher.pdf',
            'size' => 100,
        ]);
        $file2 = FileFactory::createOne([
            'path' => '/tmp/fichier.pdf',
            'name' => 'ficher.pdf',
            'size' => 100,
        ]);

        $od1 = OtherdocFactory::createOne(['file' => $file1, 'sitting' => $sitting]);
        $od2 = OtherdocFactory::createOne(['file' => $file2, 'sitting' => $sitting]);

        $otherdocsIds = [$od1->getId(), $od2->getId()];

        $otherdocs = $this->otherdocRepository->findNotInListOtherdocs($otherdocsIds, $sitting);
        $this->assertCount(0, $otherdocs);
    }

    public function testGetOtherdocsBySitting()
    {
        $structure = StructureStory::libriciel()->object();
        $sitting = SittingFactory::createOne([
            'structure' => $structure,
            'date' => new DateTime(),
        ])->object();
        $file = FileFactory::createOne([
            'path' => '/tmp/fichier.pdf',
            'name' => 'ficher.pdf',
            'size' => 100,
        ]);
        OtherdocFactory::createMany(1, ['sitting' => $sitting, 'file' => $file]);

        $otherdocs = $this->otherdocRepository->getOtherdocsBySitting($sitting);

        $this->assertCount(1, $otherdocs);
    }
}
