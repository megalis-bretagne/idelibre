<?php

namespace App\Tests\Service\NotificationMail;

use App\Service\NotificationMail\NotificationDataProvider;
use App\Tests\Factory\SittingFactory;
use App\Tests\Factory\StructureFactory;
use App\Tests\Factory\SubscriptionFactory;
use App\Tests\Factory\TypeFactory;
use App\Tests\Factory\UserFactory;
use App\Tests\Story\RoleStory;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class NotificationDataProviderTest extends KernelTestCase
{


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


        /** @var NotificationDataProvider $notificationDataProvider */
        $notificationDataProvider = self::getContainer()->get(NotificationDataProvider::class);
        $notificationsToSend= $notificationDataProvider->getAllStructuresAttendanceNotifications();

        $this->assertCount(2, $notificationsToSend);
        $this->assertCount(2, $notificationsToSend[0]->getNotificationsData());
    }

}
