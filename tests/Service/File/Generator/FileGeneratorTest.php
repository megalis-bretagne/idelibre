<?php

namespace App\Tests\Service\File\Generator;

use _PHPStan_255691850\Nette\Neon\Exception;
use App\Service\File\Generator\FileChecker;
use App\Service\File\Generator\FileGenerator;
use App\Service\File\Generator\UnsupportedExtensionException;
use App\Tests\Factory\FileFactory;
use App\Tests\Factory\ProjectFactory;
use App\Tests\Factory\SittingFactory;
use App\Tests\Story\FileStory;
use App\Tests\Story\SittingStory;
use App\Tests\Story\StructureStory;
use App\Tests\Story\ThemeStory;
use App\Tests\Story\TypeStory;
use App\Tests\Story\UserStory;
use DateTime;
use Howtomakeaturn\PDFInfo\PDFInfo;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class FileGeneratorTest extends KernelTestCase
{
    use ResetDatabase;
    use Factories;

    private ?KernelBrowser $client;
    private ParameterBagInterface $bag;
    private ?FileGenerator $fileGenerator;
    private ?FileChecker $fileChecker;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        $this->bag = self::getContainer()->getParameterBag();
        $this->fileGenerator = self::getContainer()->get(FileGenerator::class);
        $this->fileChecker = self::getContainer()->get(FileChecker::class);

        self::ensureKernelShutdown();
    }

    public function testGenFullSittingDirPath()
    {
        $extension = 'pdf';
        $sitting = SittingStory::sittingConseilLibriciel();

        $expected = $this->bag->get('document_full_pdf_directory') . $sitting->getStructure()->getId() . '/' . $sitting->getId() . '.' . $extension;
        $dirPath = $this->fileGenerator->genFullSittingDirPath($sitting->object(), $extension);

        $this->assertSame($expected, $dirPath);
    }

    public function testSanitizeEncryption()
    {
        $paths = [];
        $filesystem = new Filesystem();
        $filesystem->copy('tests/resources/toDecrypt.pdf', 'tests/resources/toDecrypt1.pdf');
        $filesystem->copy('tests/resources/toDecrypt.pdf', 'tests/resources/toDecrypt2.pdf');

        $fileProject1 = new UploadedFile('tests/resources/toDecrypt1.pdf', 'toDecrypt.pdf', 'application/pdf');
        $fileProject2 = new UploadedFile('tests/resources/toDecrypt2.pdf', 'toDecrypt.pdf', 'application/pdf');

        $file1 = FileFactory::createOne([
            'name' => 'Fichier crypté',
            'size' => 100,
            'path' => $fileProject1->getPath() . '/' . $fileProject1->getFilename(),
        ])->object();

        $file2 = FileFactory::createOne([
            'name' => 'Fichier crypté',
            'size' => 100,
            'path' => $fileProject2->getPath() . '/' . $fileProject2->getFilename(),
        ])->object();

        $paths = [$file1->getPath(), $file2->getPath()];

        $this->fileChecker->sanitizeEncrypted($paths);

        foreach ($paths as $path) {
            $pdfInfos = new PDFInfo($path);
            if ('no' !== $pdfInfos->encrypted) {
                return $this->throwException(new Exception('some files are encrypted'));
            }
        }

        $this->expectNotToPerformAssertions();
    }

    public function testSizeChecker()
    {
        $sitting = SittingFactory::createOne([
            'name' => 'Reforme des retraites',
            'date' => new DateTime('2020-10-21'),
            'structure' => StructureStory::libriciel(),
            'convocationFile' => FileStory::fileConvocation2(),
            'place' => 'Salle du conseil',
            'type' => TypeStory::typeBureauLibriciel(),
        ]);

        ProjectFactory::createMany(3, [
            'rank' => 1,
            'file' => FileFactory::new(
                [
                    'name' => 'Fichier projet',
                    'size' => 1 * (pow(10, 5)),
                    'path' => '/tmp/fileProject',
                ]
            ),
            'name' => 'Project 1',
            'sitting' => $sitting,
            'theme' => ThemeStory::financeTheme(),
            'reporter' => UserStory::actorLibriciel1(),
        ]);

        $this->assertTrue($this->fileChecker->sizeChecker($sitting->object()));
    }

    public function testGetDirectoryPathByExtension()
    {
        $pdfPath = $this->bag->get('document_full_pdf_directory');
        $zipPath = $this->bag->get('document_full_zip_directory');

        $this->assertSame($pdfPath, $this->fileGenerator->getDirectoryPathByExtension('pdf'));
        $this->assertSame($zipPath, $this->fileGenerator->getDirectoryPathByExtension('zip'));
    }

    public function testGetDirectoryPathByExtensionNotExist()
    {
        $this->expectException(UnsupportedExtensionException::class);
        $this->fileGenerator->getDirectoryPathByExtension('fake');
    }
}
