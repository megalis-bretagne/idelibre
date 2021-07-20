<?php

namespace App\Tests\Entity;

use App\DataFixtures\UserFixtures;
use App\Entity\User;
use App\Tests\FindEntityTrait;
use App\Tests\HasValidationError;
use App\Tests\StringTrait;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserAliceTest extends WebTestCase
{
    use FindEntityTrait;
    use HasValidationError;
    use StringTrait;

    private $entityManager;

    protected function setUp(): void
    {

        self::bootKernel();

        $this->entityManager = self::getContainer()->get('doctrine')->getManager();

        $databaseTool = self::getContainer()->get(DatabaseToolCollection::class)->get();
        $users = $databaseTool->loadAliceFixture([
            __DIR__ . '/../Fixtures/users.yaml',
        ]);

        dd($users);
    }

    public function testCheck()
    {
        $this->markTestSkipped();
       // return;
        dd('ok');
    }


}
