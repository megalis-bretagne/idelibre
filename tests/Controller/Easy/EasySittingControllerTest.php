<?php

namespace App\Tests\Controller\Easy;

use App\Controller\Easy\EasySittingController;
use App\Tests\AuthenticationTrait;
use App\Tests\LoginTrait;
use App\Tests\Story\UserStory;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class EasySittingControllerTest extends WebTestCase
{
    use ResetDatabase;
    use Factories;
    use LoginTrait;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }


    public function testIndex()
    {
        $actorLibriciel1 = UserStory::actorLibriciel1();
        $this->login($actorLibriciel1->getUsername());

        $this->client->request('GET', '/easy/sitting');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Séances en cours');
    }
}
