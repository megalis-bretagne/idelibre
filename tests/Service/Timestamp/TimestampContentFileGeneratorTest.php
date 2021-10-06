<?php

namespace App\Tests\Service\Timestamp;

use App\DataFixtures\AnnexFixtures;
use App\DataFixtures\ConvocationFixtures;
use App\DataFixtures\FileFixtures;
use App\DataFixtures\ProjectFixtures;
use App\DataFixtures\SittingFixtures;
use App\Service\Timestamp\TimestampContentFileGenerator;
use App\Tests\FileTrait;
use App\Tests\FindEntityTrait;
use App\Tests\LoginTrait;
use Doctrine\Persistence\ObjectManager;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TimestampContentFileGeneratorTest extends WebTestCase
{
    use FindEntityTrait;
    use LoginTrait;
    use FileTrait;

    /**
     * @var ObjectManager
     */
    private $entityManager;
    private $environment;
    /**
     * @var object|\Symfony\Component\DependencyInjection\ParameterBag\ContainerBag|null
     */
    private $bag;
    /**
     * @var object|\Symfony\Component\Filesystem\Filesystem|null
     */
    private $fileSystem;

    protected function setUp(): void
    {
        self::bootKernel();
        $container = self::getContainer();

        $this->environment = $container->get('twig');
        $this->entityManager = $container->get('doctrine')->getManager();
        $this->bag = $container->get('parameter_bag');
        $this->fileSystem = $container->get('filesystem');

        $databaseTool = self::getContainer()->get(DatabaseToolCollection::class)->get();
        $databaseTool->loadFixtures([
            SittingFixtures::class,
            ConvocationFixtures::class,
            ProjectFixtures::class,
            AnnexFixtures::class,
            FileFixtures::class,
        ]);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
    }

    public function testGenerateFile()
    {
        $sitting = $this->getOneSittingBy(['name' => 'Conseil Libriciel']);
        $timestampGenerator = new TimestampContentFileGenerator($this->environment, $this->bag, $this->fileSystem);
        $path = $timestampGenerator->generateConvocationFile($sitting, $sitting->getConvocations());
        $this->assertSame(51, $this->countFileLines($path));
    }
}
