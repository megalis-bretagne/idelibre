<?php

namespace App\Tests\Security\Password;

use App\Repository\UserRepository;
use App\Security\Password\PasswordChange;
use App\Security\Password\PasswordUpdater;
use App\Security\Password\PasswordUpdaterException;
use App\Tests\FindEntityTrait;
use App\Tests\LoginTrait;
use App\Tests\Story\StructureStory;
use App\Tests\Story\UserStory;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class PasswordUpdaterTest extends KernelTestCase
{

    use ResetDatabase;
    use Factories;
    use FindEntityTrait;

    protected function setUp(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        $this->entityManager = $container->get('doctrine')->getManager();
        $this->userRepository = self::getcontainer()->get(UserRepository::class);
    }


    public function testReplace()
    {
        $user = UserStory::actorLibriciel1();

        $passwordChange = new PasswordChange();
        $passwordChange->plainNewPassword = "NewPassword1234567890!NewPassword1234567890!";
        $passwordChange->plainCurrentPassword = "password";
        $passwordChange->userId = $user->getId();

        /** @var PasswordUpdater $passwordUpdater */
        $passwordUpdater = self::getContainer()->get(PasswordUpdater::class);
        $success = $passwordUpdater->replace($passwordChange);
        $this->assertTrue($success);
    }

    public function testReplaceUserIDDoesNotExist()
    {
        $passwordChange = new PasswordChange();
        $passwordChange->plainNewPassword = "NewPassword1234567890!";
        $passwordChange->plainCurrentPassword = "OldPassword1234567890!";
        $passwordChange->userId = "74946758-7237-4130-9f7e-f2e4016e1590";

        /** @var PasswordUpdater $passwordUpdater */
        $passwordUpdater = self::getContainer()->get(PasswordUpdater::class);

        $this->expectException(PasswordUpdaterException::class);
        $this->expectExceptionMessage('BAD_USER_ID');
        $passwordUpdater->replace($passwordChange);
    }


    public function testReplaceFalseCurrentPassword()
    {
        $user = UserStory::actorLibriciel1();

        $passwordChange = new PasswordChange();
        $passwordChange->plainNewPassword = "NewPassword1234567890!";
        $passwordChange->plainCurrentPassword = "OldPassword1234567890!";
        $passwordChange->userId = $user->getId();


        /** @var PasswordUpdater $passwordUpdater */
        $passwordUpdater = self::getContainer()->get(PasswordUpdater::class);

        $this->expectException(PasswordUpdaterException::class);
        $this->expectExceptionMessage('INVALID_CURRENT_PASSWORD');
        $passwordUpdater->replace($passwordChange);
    }

    public function testReplaceEntropyTooLowNewPassword()
    {
        $user = UserStory::actorLibriciel1();

        $passwordChange = new PasswordChange();
        $passwordChange->plainNewPassword = "ToWeak";
        $passwordChange->plainCurrentPassword = "password";
        $passwordChange->userId = $user->getId();


        /** @var PasswordUpdater $passwordUpdater */
        $passwordUpdater = self::getContainer()->get(PasswordUpdater::class);

        $this->expectException(PasswordUpdaterException::class);
        $this->expectExceptionMessage('ENTROPY_TOO_LOW');
        $passwordUpdater->replace($passwordChange);
    }





}
