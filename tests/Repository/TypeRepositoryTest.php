<?php

namespace App\Tests\Repository;

use App\Repository\TypeRepository;
use App\Tests\FindEntityTrait;
use App\Tests\LoginTrait;
use App\Tests\Story\EmailTemplateStory;
use App\Tests\Story\StructureStory;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class TypeRepositoryTest extends WebTestCase
{
    use ResetDatabase;
    use Factories;
    use FindEntityTrait;
    use LoginTrait;

    private ?KernelBrowser $client;
    private ObjectManager $entityManager;
    private TypeRepository $typeRepository;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        $this->typeRepository = self::getContainer()->get(TypeRepository::class);

        self::ensureKernelShutdown();
        $this->client = static::createClient();

        StructureStory::libriciel();
        EmailTemplateStory::load();
    }

    public function testFindByStructure()
    {
        $structure = StructureStory::libriciel();
        $this->assertCount(3, $this->typeRepository->findByStructure($structure->object())->getQuery()->getResult());
    }

    public function testFindNotAssociatedWithOtherTemplateByStructure()
    {
        $structure = StructureStory::libriciel();
        $associatedEmailTemplate = EmailTemplateStory::emailTemplateConseilLs();
        $notAssociatedEmailTemplate = EmailTemplateStory::emailTemplateSansTypeLs();

        $this->assertCount(
            3,
            $this->typeRepository->findNotAssociatedWithOtherTemplateByStructure(
                $structure->object(),
                $associatedEmailTemplate->object()
            )->getQuery()->getResult()
        );

        $this->assertCount(
            2,
            $this->typeRepository->findNotAssociatedWithOtherTemplateByStructure(
                $structure->object(),
                $notAssociatedEmailTemplate->object()
            )->getQuery()->getResult()
        );
    }
}
