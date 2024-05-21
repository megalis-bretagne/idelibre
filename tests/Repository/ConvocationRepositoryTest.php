<?php

namespace App\Tests\Repository;

use App\Entity\Convocation;
use App\Repository\ConvocationRepository;
use App\Tests\Factory\ConvocationFactory;
use App\Tests\Factory\SittingFactory;
use App\Tests\Factory\StructureFactory;
use App\Tests\Factory\UserFactory;
use App\Tests\FindEntityTrait;
use App\Tests\LoginTrait;
use App\Tests\Story\SittingStory;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class ConvocationRepositoryTest extends WebTestCase
{
    use ResetDatabase;
    use Factories;
    use FindEntityTrait;
    use LoginTrait;

    private ?KernelBrowser $client;
    private ObjectManager $entityManager;
    private ConvocationRepository $convocationRepository;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        $this->convocationRepository = self::getContainer()->get(ConvocationRepository::class);

        self::ensureKernelShutdown();
        $this->client = static::createClient();
    }

    public function testGetConvocationsBySittingAndActorIds()
    {
        $sitting = SittingStory::sittingConseilLibriciel()->object();
        $user1 = UserFactory::createOne();
        $user2 = UserFactory::createOne();

        ConvocationFactory::createOne(['user' => $user1, 'sitting' => $sitting]);
        ConvocationFactory::createOne(['user' => $user2, 'sitting' => $sitting]);

        $convocations = $this->convocationRepository->getConvocationsBySittingAndActorIds($sitting, [$user1->getId(), $user2->getId()]);

        $this->assertCount(2, $convocations);
    }

    public function testGetConvocationWithUser()
    {
        $sitting = SittingStory::sittingConseilLibriciel()->object();
        $all_convocations = ConvocationFactory::createMany(5, ['sitting' => $sitting]);

        $convocationsId = [];
        foreach ($all_convocations as $convocation) {
            $convocationsId[] = $convocation->getId();
        }

        $convocations = $this->convocationRepository->getConvocationsWithUser($convocationsId);

        $this->assertCount(5, $convocations);
    }

    public function testGetConvocationsWithUserBySitting()
    {
        $sitting = SittingStory::sittingConseilLibriciel()->object();
        $user1 = UserFactory::createOne();
        $user2 = UserFactory::createOne();

        ConvocationFactory::createOne(['user' => $user1, 'sitting' => $sitting]);
        ConvocationFactory::createOne(['user' => $user2, 'sitting' => $sitting]);

        $convocations = $this->convocationRepository->getConvocationsWithUserBySitting($sitting);

        $this->assertCount(2, $convocations);
    }

    public function testGetAndDeleteInvitationBySitting(): void
    {
        $sitting = SittingFactory::createOne(["structure" => StructureFactory::createOne()]);
        ConvocationFactory::createOne([
            "category" => Convocation::CATEGORY_INVITATION,
            "sitting" => $sitting,
            "user" => UserFactory::createOne()
        ]);
        ConvocationFactory::createOne([
            "category" => Convocation::CATEGORY_CONVOCATION,
            "sitting" => $sitting,
            "user" => UserFactory::createOne()
        ]);

        $this->convocationRepository->deleteInvitationsBySitting($sitting->object());
        $convocations = $this->convocationRepository->findAll();

        $this->assertCount(1, $convocations);
        $this->assertSame(Convocation::CATEGORY_CONVOCATION, $convocations[0]->getCategory());
    }
}
