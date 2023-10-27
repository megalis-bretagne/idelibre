<?php

namespace App\Tests\Service\Convocation;

use App\Entity\Convocation;
use App\Service\Convocation\ConvocationManager;
use App\Tests\Factory\ConvocationFactory;
use App\Tests\Factory\SittingFactory;
use App\Tests\Factory\TimestampFactory;
use App\Tests\Story\SittingStory;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class ConvocationManagerTest extends WebTestCase
{
    use ResetDatabase;
    use Factories;

    private ?KernelBrowser $client;
    private ObjectManager $entityManager;
    private ConvocationManager $convocationManager;

    protected function setUp(): void
    {
        $this->client = static::createClient();

        $kernel = self::bootKernel();
        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        $this->convocationManager = self::getContainer()->get(ConvocationManager::class);

        self::ensureKernelShutdown();
    }

    public function testKeepOnlyNotSent()
    {
        $timestamp = TimestampFactory::createOne();
        $sitting = SittingStory::sittingConseilLibriciel();
        $sent_convocations = ConvocationFactory::createMany(1, [
            'sitting' => $sitting,
            'sentTimestamp' => $timestamp,
        ]);
        $not_sent_convocation = ConvocationFactory::createMany(2, [
            'sitting' => $sitting,
            'sentTimestamp' => null,
        ]);

        $all_convocations = [...$sent_convocations, ...$not_sent_convocation];

        $convocations = $this->convocationManager->keepOnlyNotSent($all_convocations);

        $this->assertCount(2, $convocations);
    }

    public function testCountConvocationNotAnswered()
    {

        $sitting = SittingStory::sittingConseilLibriciel();
        $convocation1 = ConvocationFactory::createOne(["sitting" => $sitting , "attendance" => ""]);
        $convocation2 = ConvocationFactory::createOne(["sitting" => $sitting , "attendance" => Convocation::PRESENT]);

        $convocations = [$convocation1, $convocation2];

        $countConvocationNotAnswered = $this->convocationManager->countConvocationNotanswered($convocations);
        $this->assertIsInt($countConvocationNotAnswered);
        $this->assertEquals(1, $countConvocationNotAnswered);
    }
}
