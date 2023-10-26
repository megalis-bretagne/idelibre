<?php

namespace App\Tests\Service\User;

use App\Service\User\PasswordInvalidator;
use App\Tests\LoginTrait;
use App\Tests\Story\RoleStory;
use App\Tests\Story\UserStory;
use Proxies\__CG__\App\Entity\User;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class PasswordInvalidatorTest extends WebTestCase
{

    use Factories;
    use ResetDatabase;
    use LoginTrait;

    private PasswordInvalidator $passwordInvalidator;
    private KernelBrowser $client;
//    public const password = '$argon2i$v=19$m=65536,t=4,p=1$QXNER0ZnNm5aSW9HRmMzbg$7hld/jiw0amTyqeEHKs5YQ0pa60338Ni9BQzNS5KImg';


    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->passwordInvalidator = self::getContainer()->get(PasswordInvalidator::class);
        self::ensureKernelShutdown();

        $this->client = static::createClient();
    }

//    public function testIsAuthorizeInvalidate()
//    {
//        UserStory::load();
//        RoleStory::load();
//        $this->loginAsSuperAdmin();
//
//        $userLoggedIn = UserStory::superadmin();
//        $userToDeactivate = UserStory::userMontpellier();
//
//        $isAutorized = $this->passwordInvalidator->isAuthorizeInvalidate($userToDeactivate->object(), $userLoggedIn->object());
//
//        dd($isAutorized);
//
//        $this->assertTrue($isAutorized);
//    }



}
