<?php

namespace App\Tests\Repository;

use App\Repository\EmailTemplateRepository;
use App\Tests\Factory\EmailTemplateFactory;
use App\Tests\Story\StructureStory;
use App\Tests\Story\TypeStory;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class EmailTemplateRepositoryTest extends WebTestCase
{
    use ResetDatabase;
    use Factories;

    private ?KernelBrowser $client;
    private ObjectManager $entityManager;
    private EmailTemplateRepository $emailTemplateRepository;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        $this->emailTemplateRepository = self::getContainer()->get(EmailTemplateRepository::class);

        self::ensureKernelShutdown();
        $this->client = static::createClient();
    }

    public function testFindAllByStructure()
    {
        $structure = StructureStory::libriciel()->object();
        EmailTemplateFactory::createMany(5, ['structure' => $structure]);

        $emailTemplate = $this->emailTemplateRepository->findAllByStructure($structure)->getQuery()->getResult();

        $this->assertCount(5, $emailTemplate);
    }

    public function testFindOneByType()
    {
        $structure = StructureStory::libriciel()->object();
        $type = TypeStory::typeConseilLibriciel()->object();
        $typeName = $type->getName();
        EmailTemplateFactory::createOne(['type' => $type, 'structure' => $structure]);

        $emailTemplate = $this->emailTemplateRepository->findOneByType($type);

        $this->assertSame($typeName, $emailTemplate->getType()->getName());
    }

    public function testFindOneByStructureAndCategory()
    {
        $structure = StructureStory::libriciel()->object();
        $category = 'convocation';

        EmailTemplateFactory::createOne(['structure' => $structure, 'category' => $category]);
        $emailTemplate = $this->emailTemplateRepository->findOneByStructureAndCategory($structure, $category);

        $this->assertSame($structure->getName(), $emailTemplate->getStructure()->getName());
        $this->assertSame($category, $emailTemplate->getCategory());
    }

    public function testFindOneByStructureAndCategoryAndType()
    {
        $structure = StructureStory::libriciel()->object();
        $type = TypeStory::typeConseilLibriciel()->object();
        $category = 'convocation';

        EmailTemplateFactory::createOne(['structure' => $structure, 'type' => $type, 'category' => $category]);

        $emailTemplate = $this->emailTemplateRepository->findOneByStructureAndCategoryAndType($structure, $type, $category);

        $this->assertSame($structure->getName(), $emailTemplate->getStructure()->getName());
        $this->assertSame($type->getName(), $emailTemplate->getType()->getName());
        $this->assertSame($category, $emailTemplate->getCategory());
    }
}
