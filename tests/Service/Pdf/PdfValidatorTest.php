<?php

namespace App\Tests\Service\PdfValidator;

use App\Service\ApiEntity\OtherdocApi;
use App\Service\ApiEntity\ProjectApi;
use App\Service\File\FileManager;
use App\Tests\FindEntityTrait;
use App\Tests\LoginTrait;
use App\Tests\Story\AnnexStory;
use App\Tests\Story\ConvocationStory;
use App\Tests\Story\FileStory;
use App\Tests\Story\ProjectStory;
use App\Tests\Story\SittingStory;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class PdfValidatorTest extends WebTestCase
{
    use ResetDatabase;
    use Factories;
    use FindEntityTrait;
    use LoginTrait;

    private ?KernelBrowser $client;
    private ObjectManager $entityManager;
    private FileManager $fileManager;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        $container = self::getContainer();
        $this->pdfvalidator = $container->get('App\Service\Pdf\PdfValidator');

        self::ensureKernelShutdown();
        $this->client = static::createClient();

        FileStory::load();
        ProjectStory::load();
        ConvocationStory::load();
        AnnexStory::load();
        SittingStory::load();
    }

    public function testIsPdfFile()
    {
        $file = FileStory::fileEncrypted();
        $this->assertTrue($this->pdfvalidator->isPdfFile($file->getPath()) );
    }

    public function testIsProjectsPdf()
    {
        $project1 = new ProjectApi();
        $project1->setName('first Project')
            ->setFileName('project1.pdf')
            ->setRank(0)
            ->setLinkedFileKey('project1');

        $project2 = new ProjectApi();
        $project2->setName('Second Project')
            ->setFileName('project2.pdf')
            ->setRank(0)
            ->setLinkedFileKey('project2');

        $projects = [
            $project1,
            $project2
        ];
        $this->assertTrue($this->pdfvalidator->isProjectsPdf($projects));
    }

    public function testIsProjectsPdfOneIsNone()
    {
        $project1 = new ProjectApi();
        $project1->setName('first Project')
            ->setFileName('project1.pdf')
            ->setRank(0)
            ->setLinkedFileKey('project1');

        $project2 = new ProjectApi();
        $project2->setName('Second Project')
            ->setFileName('project2.odt')
            ->setRank(0)
            ->setLinkedFileKey('project2');

        $projects = [
            $project1,
            $project2
        ];
        $this->assertFalse($this->pdfvalidator->isProjectsPdf($projects));
    }


    public function testIsOtherdocsPdf()
    {
        $otherdoc1 = new OtherdocApi();
        $otherdoc1->setName('first otherdoc')
            ->setFileName('otherdoc1.pdf')
            ->setRank(0)
            ->setLinkedFileKey('otherdoc1');

        $otherdoc2 = new OtherdocApi();
        $otherdoc2->setName('Second otherdoc')
            ->setFileName('otherdoc2.pdf')
            ->setRank(0)
            ->setLinkedFileKey('otherdoc2');

        $otherdocs = [
            $otherdoc1,
            $otherdoc2
        ];
        $this->assertTrue($this->pdfvalidator->isotherdocsPdf($otherdocs));
    }

    public function testIsOtherdocsPdfOneIsNone()
    {
        $otherdoc1 = new OtherdocApi();
        $otherdoc1->setName('first otherdoc')
            ->setFileName('otherdoc1.pdf')
            ->setRank(0)
            ->setLinkedFileKey('otherdoc1');

        $otherdoc2 = new OtherdocApi();
        $otherdoc2->setName('Second otherdoc')
            ->setFileName('otherdoc2.odt')
            ->setRank(0)
            ->setLinkedFileKey('otherdoc2');

        $otherdocs = [
            $otherdoc1,
            $otherdoc2
        ];
        $this->assertFalse($this->pdfvalidator->isotherdocsPdf($otherdocs));
    }

    public function testListOfOpenablePdfForSittingCreation()
    {
        $uploadedFile1 = new UploadedFile(__DIR__ . '/../../resources/fichier.pdf', 'fichier.pdf');
        $uploadedFile2 = new UploadedFile(__DIR__ . '/../../resources/pdfEncrypted.pdf', 'pdfEncrypted.pdf');

        $uploadedFiles = [
            'sitting' => [
                'convocationFile' => $uploadedFile1,
                'invitationFile' => $uploadedFile2
            ]
        ];

        $files = $this->pdfvalidator->listOfOpenablePdfForSittingCreation($uploadedFiles);
        $this->assertCount(2, $files);
    }


    public function testlistOfOpenablePdfWhenAddingFiles()
    {
        $uploadedFile1 = new UploadedFile(__DIR__ . '/../../resources/pdfEncrypted.pdf', 'pdfEncrypted.pdf');
        $uploadedFile2 = new UploadedFile(__DIR__ . '/../../resources/toDecrypt.pdf', 'toDecrypt.pdf');

        $uploadedFiles = [
            $uploadedFile1,
            $uploadedFile2
        ];

        $files = $this->pdfvalidator->listOfOpenablePdfWhenAddingFiles($uploadedFiles);
        $this->assertCount(2, $files);
    }


    public function testListOfOpenablePdfWhenEditingOtherdocs()
    {
        $uploadedFile1 = new UploadedFile(__DIR__ . '/../../resources/pdfEncrypted.pdf', 'pdfEncrypted.pdf');
        $uploadedFile2 = new UploadedFile(__DIR__ . '/../../resources/toDecrypt.pdf', 'toDecrypt.pdf');

        $uploadedFiles = [
            $uploadedFile1,
            $uploadedFile2
        ];

        $files = $this->pdfvalidator->listOfOpenablePdfWhenAddingFiles($uploadedFiles);
        $this->assertCount(2, $files);
    }

    public function testIsPdfContent()
    {
        $uploadedFile1 = new UploadedFile(__DIR__ . '/../../resources/pdfEncrypted.pdf', 'pdfEncrypted.pdf');
        $this->assertTrue($this->pdfvalidator->isPdfContent(file_get_contents($uploadedFile1)));
    }

    public function testIsNotProtectedByPasswordPdf()
    {
        $uploadedFile1 = new UploadedFile(__DIR__ . '/../../resources/fichier.pdf', 'fichier.pdf');
        $this->assertFalse($this->pdfvalidator->isProtectedByPasswordPdf($uploadedFile1->getPathname()));
    }

    public function testIsProtectedByPasswordPdf()
    {
        $uploadedFile1 = new UploadedFile(__DIR__ . '/../../resources/PasswordProtectedPdf.pdf', 'PasswordProtectedPdf.pdf');
        $this->assertTrue($this->pdfvalidator->isProtectedByPasswordPdf($uploadedFile1->getPathname()));
    }
}
