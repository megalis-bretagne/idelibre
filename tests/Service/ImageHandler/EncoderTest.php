<?php

namespace App\Tests\Service\ImageHandler;


use App\Service\ImageHandler\Encoder;
use App\Tests\Factory\EmailTemplateFactory;
use App\Tests\Factory\StructureFactory;
use Exception;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Filesystem\Filesystem;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class EncoderTest extends WebTestCase
{

    use ResetDatabase;
    use Factories;

    private readonly Encoder $encoder;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->encoder = self::getContainer()->get(Encoder::class);
    }

    public function testImageHandlerAndUpdateContent()
    {
        $structure = StructureFactory::createOne()->object();
        $uuid = Uuid::uuid4();
        $emailTemplate = EmailTemplateFactory::createOne([
            'content' => "<img src='$uuid' alt='image' />",
            'structure' => $structure,
        ])->object();

        $filesystem = new Filesystem();
        $filesystem->copy(__DIR__ . '/../../resources/image.jpg', '/tmp/image/' . $structure->getId() . '/image.jpg');

        $updatedContent = $this->encoder->imageHandlerAndUpdateContent($emailTemplate->getContent(), $structure->getId());

        $this->assertStringContainsString($emailTemplate->getContent(),$updatedContent);
    }

}
