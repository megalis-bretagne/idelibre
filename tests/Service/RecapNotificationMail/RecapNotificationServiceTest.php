<?php

namespace App\Tests\Service\RecapNotificationMail;

use App\Entity\EmailTemplate;
use App\Service\RecapNotificationMail\RecapDataProvider;
use App\Service\RecapNotificationMail\RecapNotificationService;
use App\Tests\Factory\EmailTemplateFactory;
use App\Tests\Factory\SittingFactory;
use App\Tests\Factory\StructureFactory;
use App\Tests\Factory\SubscriptionFactory;
use App\Tests\Factory\TypeFactory;
use App\Tests\Factory\UserFactory;
use App\Tests\Story\RoleStory;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Component\Messenger\Transport\InMemory\InMemoryTransport;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class RecapNotificationServiceTest extends KernelTestCase
{
    use ResetDatabase;
    use Factories;

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
            'structure' => $structure,
            'email' => 'admin@email.fr'
        ]);

        $type = TypeFactory::createOne([
            'name' => 'type',
            'structure' => $structure
        ]);


        $secretaryRegistered = UserFactory::createOne([
            'username' => 'secretary_registered',
            'role' => RoleStory::secretary(),
            'structure' => $structure,
            'authorizedTypes' => [$type],
            'email' => 'secretary_registered@email.fr'
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

        $emailTemplateRecap = EmailTemplateFactory::createOne(
            [
                'name' => 'Invitation',
                'subject' => 'idelibre : Recapitulation',
                'category' => EmailTemplate::CATEGORY_RECAPITULATIF,
                'content' => 'Voici un Email recaputilatif',
                'structure' => $structure,
                'isAttachment' => false
            ]
        );

        /** @var RecapNotificationService $recapNotificationService */
        $recapNotificationService = self::getContainer()->get(RecapNotificationService::class);
        $recapNotificationService->sendRecapNotifications();

        $this->assertEmailCount(2);
    }
}
