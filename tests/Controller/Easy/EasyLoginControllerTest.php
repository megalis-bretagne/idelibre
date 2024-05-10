<?php

namespace App\Tests\Controller\Easy;

use App\Controller\Easy\EasyLoginController;
use App\Service\Jwt\JwtManager;
use App\Tests\Factory\ConvocationFactory;
use App\Tests\Factory\SittingFactory;
use App\Tests\Factory\TimestampFactory;
use App\Tests\LoginTrait;
use App\Tests\Story\UserStory;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class EasyLoginControllerTest extends WebTestCase
{

    use ResetDatabase;
    use Factories;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }


    public function testMagicLink(){

        /** @var JwtManager $jwtManager */
        $jwtManager = self::getContainer()->get(JwtManager::class);

        $actorLibriciel1 = UserStory::actorLibriciel1();
        $sitting = SittingFactory::createOne([
            'structure' => $actorLibriciel1->getStructure(),
            'date' => new \DateTimeImmutable('+2 hours'),
            'name' => 'Séance test'
        ]);

        $convocation = ConvocationFactory::createOne([
            'sitting' => $sitting->object(),
            'user' => $actorLibriciel1->object(),
            'sentTimestamp' => TimestampFactory::new()
        ]);

        $token = $jwtManager->generateTokenForUserNameAndSittingId(
            $actorLibriciel1->getUsername(),
            $sitting->getId(),
            true,
            new \DateTimeImmutable('+2 hours')
        );


        $this->client->request('GET', '/easy/magic-link?token='.$token);
        $this->assertResponseRedirects();
        $this->client->followRedirect();
        $this->assertSelectorTextContains('h1', 'Accuser réception de la séance');
    }



}
