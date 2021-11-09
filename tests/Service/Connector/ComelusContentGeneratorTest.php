<?php

namespace App\Tests\Service\Connector;

use App\DataFixtures\SittingFixtures;
use App\Service\Connector\ComelusContentGenerator;
use App\Tests\FindEntityTrait;
use Doctrine\ORM\EntityManagerInterface;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ComelusContentGeneratorTest extends WebTestCase
{
    use FindEntityTrait;

    private EntityManagerInterface $entityManager;

    /** @var ComelusContentGenerator */
    private $comelusContentGenerator;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        $container = self::getContainer();


        $this->comelusContentGenerator = $container->get(ComelusContentGenerator::class);

        $databaseTool = self::getContainer()->get(DatabaseToolCollection::class)->get();
        $databaseTool->loadFixtures([
            SittingFixtures::class,
        ]);
    }


    public function testCreateDescription()
    {
        $sitting = $this->getOneSittingBy(['name' => 'Conseil Libriciel']);

        $content = "Ceci est un test de conversion  #typeseance# , #dateseance# , #heureseance# , #lieuseance#";
        $generated = $this->comelusContentGenerator->createDescription($content, $sitting);
        $this->assertSame("Ceci est un test de conversion  Conseil Libriciel , 22/10/2020 , 02:00 , Salle du conseil", $generated);
    }


}
