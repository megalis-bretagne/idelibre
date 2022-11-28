<?php

namespace App\Tests\Repository;

use App\Repository\AnnexRepository;
use App\Tests\FindEntityTrait;
use App\Tests\LoginTrait;
use App\Tests\Story\AnnexStory;
use App\Tests\Story\SittingStory;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class AnnexRepositoryTest extends WebTestCase
{
    use ResetDatabase;
    use Factories;
    use FindEntityTrait;
    use LoginTrait;

    private ?KernelBrowser $client;
    private ObjectManager $entityManager;
    private AnnexRepository $annexRepository;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        $this->annexRepository = self::getContainer()->get(AnnexRepository::class);

        self::ensureKernelShutdown();
        $this->client = static::createClient();

        SittingStory::sittingConseilLibriciel();
        AnnexStory::annex1();
    }

    public function testFindNotInListProjects()
    {
        $sitting = SittingStory::sittingConseilLibriciel();
        $annex = AnnexStory::annex1();

        $annexes = $this->annexRepository->findNotInListAnnexes([$annex->getId()], $sitting->object());
        $this->assertCount(1, $annexes);
        $this->assertSame('Fichier annexe 2', $annexes[0]->getFile()->getName());
    }
}
