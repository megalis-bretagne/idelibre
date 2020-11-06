<?php

namespace App\Tests\Repository;

use App\DataFixtures\EmailTemplateFixtures;
use App\DataFixtures\StructureFixtures;
use App\DataFixtures\TypeFixtures;
use App\DataFixtures\UserFixtures;
use App\Entity\EmailTemplate;
use App\Entity\Structure;
use App\Entity\Type;
use App\Repository\TypeRepository;
use App\Tests\FindEntityTrait;
use App\Tests\LoginTrait;
use Doctrine\Persistence\ObjectManager;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TypeRepositoryTest extends WebTestCase
{
    use FixturesTrait;
    use FindEntityTrait;
    use LoginTrait;


    private ?KernelBrowser $client;
    /**
     * @var ObjectManager
     */
    private $entityManager;
    /**
     * @var TypeRepository
     */
    private $typeRepository;


    protected function setUp(): void
    {
        $this->client = static::createClient();

        $kernel = self::bootKernel();
        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        $this->typeRepository = $this->entityManager->getRepository(Type::class);

        $this->loadFixtures([
            StructureFixtures::class,
            UserFixtures::class,
            TypeFixtures::class,
            EmailTemplateFixtures::class
        ]);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->client = null;
        $this->entityManager->close();
    }

    public function testFindByStructure()
    {
        /** @var Structure $structure */
        $structure = $this->getOneEntityBy(Structure::class, ['name' => 'Libriciel']);

        $this->assertCount(3, $this->typeRepository->findByStructure($structure)->getQuery()->getResult());
    }


    public function testFindNotAssociatedWithOtherTemplateByStructure()
    {
        /** @var Structure $structure */
        $structure = $this->getOneEntityBy(Structure::class, ['name' => 'Libriciel']);

        /** @var EmailTemplate $associatedEmailTemplate */
        $associatedEmailTemplate = $this->getOneEntityBy(EmailTemplate::class, ['name' => 'Conseil Libriciel']);

        /** @var EmailTemplate $notAssociatedEmailTemplate */
        $notAssociatedEmailTemplate = $this->getOneEntityBy(EmailTemplate::class, ['name' => 'Bureau Libriciel']);

        $this->assertCount(
            3,
            $this->typeRepository->findNotAssociatedWithOtherTemplateByStructure(
                $structure,
                $associatedEmailTemplate
            )->getQuery()->getResult()
        );

        $this->assertCount(
            2,
            $this->typeRepository->findNotAssociatedWithOtherTemplateByStructure(
                $structure,
                $notAssociatedEmailTemplate
            )->getQuery()->getResult()
        );
    }
}
