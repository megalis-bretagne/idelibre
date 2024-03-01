<?php

namespace App\Tests\Service\ImageHandler;

use App\Service\ImageHandler\UploadStorageHandler;
use App\Tests\Factory\StructureFactory;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class UploadStrorageHandlerTest extends WebTestCase
{

    use ResetDatabase;
    use Factories;

    private UploadStorageHandler $uploadStorageHandler;

    protected function setUp(): void
    {
        $this->uploadStorageHandler = self::getContainer()->get(UploadStorageHandler::class);
    }

    public function testUpload()
    {
        $structure = StructureFactory::createOne()->object();

        $filesystem = new FileSystem();
        $filesystem->copy(__DIR__ . '/../../resources/image.jpg', TMP_TESTDIR . '/resources/image.jpg');

        $imageFile = new UploadedFile(TMP_TESTDIR . '/resources/image.jpg', 'image.jpg', 'image/jpeg');

        $this->uploadStorageHandler->upload($imageFile, $structure, 'image.jpg');

        $this->assertDirectoryExists('/tmp/image/' . $structure->getId());
        $this->assertDirectoryExists('/data/image/' . $structure->getId());

        $this->assertFileExists('/tmp/image/' . $structure->getId() . '/image.jpg');
    }

}
