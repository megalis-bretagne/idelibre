<?php

namespace App\Tests\Service\Connector;

use App\Service\Connector\ComelusContentGenerator;
use App\Tests\FindEntityTrait;
use App\Tests\Story\SittingStory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class ComelusContentGeneratorTest extends WebTestCase
{
    use ResetDatabase;
    use Factories;
    use FindEntityTrait;

    private EntityManagerInterface $entityManager;
    private ComelusContentGenerator $comelusContentGenerator;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        $container = self::getContainer();

        $this->comelusContentGenerator = $container->get(ComelusContentGenerator::class);

        self::ensureKernelShutdown();

        SittingStory::load();
    }

    public function testCreateDescription()
    {
        $sitting = SittingStory::sittingConseilLibriciel();

        $content = 'Ceci est un test de conversion  #typeseance# , #dateseance# , #heureseance# , #lieuseance#';
        $generated = $this->comelusContentGenerator->createDescription($content, $sitting->object());
        $this->assertSame('Ceci est un test de conversion  Conseil Libriciel , 22/10/2020 , 02:00 , Salle du conseil', $generated);
    }
}
