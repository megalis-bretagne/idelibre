<?php

namespace App\Tests\Service\Timestamp;

use App\Service\Timestamp\TimestampFormatter;
use App\Tests\FindEntityTrait;
use App\Tests\LoginTrait;
use Doctrine\Persistence\ObjectManager;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TimestampFormatterTest extends WebTestCase
{
    use FixturesTrait;
    use FindEntityTrait;
    use LoginTrait;


    private ?KernelBrowser $client;
    /**
     * @var ObjectManager
     */
    private $entityManager;
    private $environment;


    protected function setUp(): void
    {
        $this->client = static::createClient();

        $container = self::$container;
        $this->environment = $container->get('twig');

/*        $this->loadFixtures([

        ]);*/
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->client = null;
        $this->entityManager->close();
    }

    public function testGenerate()
    {
        try {
            file_put_contents('/tmp/token/toto', 'heck');
        }catch (\Exception $exception) {
            dd($exception->getMessage());
        }
        //$timestampFormatter = new TimestampFormatter($this->environment);
        //$timestampFormatter->generate();
    }

}
