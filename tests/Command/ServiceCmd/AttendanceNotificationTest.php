<?php

namespace App\Tests\Command\ServiceCmd;

use App\Command\ServiceCmd\AttendanceNotification;
use App\Entity\Structure;
use App\Tests\Factory\SittingFactory;
use App\Tests\Factory\StructureFactory;
use App\Tests\Factory\SubscriptionFactory;
use App\Tests\Factory\TypeFactory;
use App\Tests\Factory\UserFactory;
use App\Tests\FindEntityTrait;
use App\Tests\LoginTrait;
use App\Tests\Story\RoleStory;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class AttendanceNotificationTest extends KernelTestCase
{
    use ResetDatabase;
    use Factories;
    use FindEntityTrait;


    protected function setUp(): void
    {
        self::bootKernel();
        parent::setUp();
    }

    public function testGenAllAttendanceNotification(): void
    {
        $structure = StructureFactory::createOne([
            'name' => 'test'
        ]);

        $admin = UserFactory::createOne([
            'username' => 'admin',
            'role' => RoleStory::admin(),
            'structure' => $structure
        ]);

        $type = TypeFactory::createOne([
            'name' => 'type',
            'structure' => $structure
        ]);


        $secretaryRegistered = UserFactory::createOne([
            'username' => 'secretary_registered',
            'role' => RoleStory::secretary(),
            'structure' => $structure,
            'authorizedTypes' => [$type]
        ]);

        $secretaryNotRegistered = UserFactory::createOne([
            'username' => 'secretary_not_registered',
            'role' => RoleStory::secretary(),
            'structure' => $structure,
            'authorizedTypes' => [TypeFactory::new(['structure' => $structure])]
        ]);

        $secretaryRegisteredNotThisType = UserFactory::createOne([
            'username' => 'secretary_registered_otherType',
            'role' => RoleStory::secretary(),
            'structure' => $structure,
            'authorizedTypes' => [TypeFactory::new(['structure' => $structure])]
        ]);


        SubscriptionFactory::createOne(
            [
                'user' => $secretaryRegistered,
                'acceptMailRecap' => true,
            ]
        );

        SubscriptionFactory::createOne(
            [
                'user' => $admin,
                'acceptMailRecap' => true,
            ]
        );

        SubscriptionFactory::createOne(
            [
                'user' => $secretaryNotRegistered,
                'acceptMailRecap' => false,
            ]
        );


        $sittingToNotify1 = SittingFactory::createOne([
            'name' => 'sittingToNotify1',
            'structure' => $structure,
            'date' => new \DateTime('+1 day'),
            'type' => $type
        ]);

        $sittingToNotify2 = SittingFactory::createOne([
            'name' => 'sittingToNotify2',
            'structure' => $structure,
            'date' => new \DateTime('+1 day'),
            'type' => $type
        ]);

        $sittingToNotNotify = SittingFactory::createOne([
            'name' => 'sittingToNotNotify',
            'structure' => $structure,
            'date' => new \DateTime('-2 days'),
            'type' => $type
        ]);


        /** @var AttendanceNotification $attendanceNotification */
        $attendanceNotification = self::getContainer()->get(AttendanceNotification::class);
        $attendanceNotification->genAllAttendanceNotification();
        $this->assertTrue(true);
    }


}
