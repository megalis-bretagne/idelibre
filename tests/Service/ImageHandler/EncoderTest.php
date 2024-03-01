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


        dd($emailTemplate->getContent());


        $filesystem = new Filesystem();
        $filesystem->copy(__DIR__ . '/../../resources/image.jpg', '/tmp/image/' . $structure->getId() . '/image.jpg');


        $content = '<img src="https://www.example.com/image.jpg" alt="image" />';
        $updatedContent = $this->encoder->imageHandlerAndUpdateContent($content, $structure->getId());
//        dd($updatedContent);

        $this->assertStringContainsString("src=$structure->getId()", $content);
    }
}
