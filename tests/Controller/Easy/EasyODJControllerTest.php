<?php

namespace App\Tests\Controller\Easy;

use App\Controller\Easy\EasyODJController;
use App\Tests\Factory\ConvocationFactory;
use App\Tests\Factory\SittingFactory;
use App\Tests\Factory\TimestampFactory;
use App\Tests\LoginTrait;
use App\Tests\Story\UserStory;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class EasyODJControllerTest extends WebTestCase
{
    use ResetDatabase;
    use Factories;
    use LoginTrait;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }


    public function testIndexConvocationNotRead()
    {
        $actorLibriciel1 = UserStory::actorLibriciel1();

        $sitting = SittingFactory::createOne([
            'structure' => $actorLibriciel1->getStructure(),
            'date' => new \DateTimeImmutable('+2 hours'),
            'name' => 'Séance test'
        ]);

        $convocation = ConvocationFactory::createOne([
            'sitting' => $sitting->object(),
            'user' => $actorLibriciel1->object(),
            'sentTimestamp' => TimestampFactory::new(),
            'isRead' => false
        ]);


        $this->login($actorLibriciel1->getUsername());

        $this->client->request('GET', "/easy/sitting/{$sitting->getId()}/odj");

        $this->assertResponseRedirects();
        $this->client->followRedirect();
        $this->assertSelectorTextContains('h1', 'Accuser réception de la séance');
    }


    public function testIndexConvocationRead()
    {
        $actorLibriciel1 = UserStory::actorLibriciel1();

        $sitting = SittingFactory::createOne([
            'structure' => $actorLibriciel1->getStructure(),
            'date' => new \DateTimeImmutable('+2 hours'),
            'name' => 'Séance test'
        ]);

        $convocation = ConvocationFactory::createOne([
            'sitting' => $sitting->object(),
            'user' => $actorLibriciel1->object(),
            'sentTimestamp' => TimestampFactory::new(),
            'isRead' => true
        ]);


        $this->login($actorLibriciel1->getUsername());

        $this->client->request('GET', "/easy/sitting/{$sitting->getId()}/odj");

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Séance test');
    }
}
