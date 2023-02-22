<?php

namespace App\Tests\Repository;

use App\Repository\PartyRepository;
use App\Tests\Factory\PartyFactory;
use App\Tests\FindEntityTrait;
use App\Tests\LoginTrait;
use App\Tests\Story\StructureStory;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class PartyRepositoryTest extends WebTestCase
{
    use ResetDatabase;
    use Factories;
    use FindEntityTrait;
    use LoginTrait;

    private ?KernelBrowser $client;
    private ObjectManager $entityManager;
    private PartyRepository $partyRepository;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        $this->partyRepository = self::getContainer()->get(PartyRepository::class);

        self::ensureKernelShutdown();
        $this->client = static::createClient();
    }

    public function testFindByStructure()
    {
        $structure = StructureStory::libriciel()->object();
        PartyFactory::createMany(5, ['structure' => $structure]);

        $parties = $this->partyRepository->findByStructure($structure)->getQuery()->getResult();

        $this->assertCount(5, $parties);
    }
}
