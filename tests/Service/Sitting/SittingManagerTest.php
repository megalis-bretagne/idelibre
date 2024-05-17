<?php

declare(strict_types=1);

namespace App\Tests\Service\Sitting;

use App\Entity\Convocation;
use App\Service\Seance\SittingManager;
use App\Tests\Factory\ConvocationFactory;
use App\Tests\Factory\SittingFactory;
use App\Tests\Factory\StructureFactory;
use App\Tests\Factory\UserFactory;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class SittingManagerTest extends WebTestCase
{
    use Factories;
    use ResetDatabase;

    private SittingManager $sittingManager;
    private ObjectManager $entityManager;


    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();
        $this->sittingManager = self::getContainer()->get(SittingManager::class);
        self::ensureKernelShutdown();
    }

    public function testDeleteInvitations(): void
    {
        $sitting = SittingFactory::createOne(['structure' => StructureFactory::createOne()]);
        ConvocationFactory::createOne([
            'sitting' => $sitting,
            'user' => UserFactory::createOne(),
            'category' => Convocation::CATEGORY_INVITATION
        ]);
        ConvocationFactory::createOne([
            'sitting' => $sitting,
            'user' => UserFactory::createOne(),
            'category' => Convocation::CATEGORY_CONVOCATION
        ]);

        $this->sittingManager->deleteInvitations($sitting->object());
        $this->assertCount(1, $sitting->object()->getConvocations());
        $this->assertSame(Convocation::CATEGORY_CONVOCATION, $sitting->object()->getConvocations()->first()->getCategory());

    }
}
