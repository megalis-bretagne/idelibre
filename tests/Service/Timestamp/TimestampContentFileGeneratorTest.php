<?php

namespace App\Tests\Service\Timestamp;

use App\Service\Timestamp\TimestampContentFileGenerator;
use App\Tests\FileTrait;
use App\Tests\FindEntityTrait;
use App\Tests\LoginTrait;
use App\Tests\Story\AnnexStory;
use App\Tests\Story\ConvocationStory;
use App\Tests\Story\FileStory;
use App\Tests\Story\ProjectStory;
use App\Tests\Story\SittingStory;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBag;
use Symfony\Component\Filesystem\Filesystem;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class TimestampContentFileGeneratorTest extends WebTestCase
{
    use ResetDatabase;
    use Factories;
    use FindEntityTrait;
    use LoginTrait;
    use FileTrait;

    private ObjectManager $entityManager;
    private $environment;
    private ContainerBag $bag;
    private Filesystem $fileSystem;

    protected function setUp(): void
    {
        self::bootKernel();
        $container = self::getContainer();

        $this->environment = $container->get('twig');
        $this->entityManager = $container->get('doctrine')->getManager();
        $this->bag = $container->get('parameter_bag');
        $this->fileSystem = $container->get('filesystem');

        SittingStory::load();
        ConvocationStory::load();
        ProjectStory::load();
        AnnexStory::load();
        FileStory::load();
    }

    public function testGenerateFile()
    {
        $sitting = SittingStory::sittingConseilLibriciel()->object();
        $timestampGenerator = new TimestampContentFileGenerator($this->environment, $this->bag, $this->fileSystem);
        $path = $timestampGenerator->generateConvocationFile($sitting, $sitting->getConvocations());

        $this->assertSame(51, $this->countFileLines($path));
    }
}
