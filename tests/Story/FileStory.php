<?php

namespace App\Tests\Story;

use App\Tests\Factory\FileFactory;
use Zenstruck\Foundry\Story;

final class FileStory extends Story
{
    public function build(): void
    {
        $this->addState('fileProject1', FileFactory::new([
            'name' => 'Fichier projet 1',
            'size' => 100,
            'path' => TMP_TESTDIR . '/fileProject1',
        ]));

        $this->addState('fileProject2', FileFactory::new([
            'name' => 'Fichier projet 2',
            'size' => 100,
            'path' => TMP_TESTDIR . '/fileProject2',
        ]));

        $this->addState('fileAnnex1', FileFactory::new([
            'name' => 'Fichier annexe 1',
            'size' => 100,
            'path' => TMP_TESTDIR . '/fileAnnex1',
        ]));

        $this->addState('fileAnnex2', FileFactory::new([
            'name' => 'Fichier annexe 2',
            'size' => 100,
            'path' => TMP_TESTDIR . '/fileAnnex2',
        ]));

        $this->addState('fileConvocation', FileFactory::new([
            'name' => 'Fichier de convocation',
            'size' => 100,
            'path' => TMP_TESTDIR . '/convocation',
        ]));

        $this->addState('fileConvocation2', FileFactory::new([
            'name' => 'Fichier de convocation',
            'size' => 100,
            'path' => TMP_TESTDIR . '/convocation',
        ]));

        $this->addState('fileConvocation3', FileFactory::new([
            'name' => 'Fichier de convocation',
            'size' => 100,
            'path' => TMP_TESTDIR . '/convocation',
        ]));

        $this->addState('fileConvocation4', FileFactory::new([
            'name' => 'Fichier de convocation 4',
            'size' => 100,
            'path' => TMP_TESTDIR . '/convocation',
        ]));

        $this->addState('fileEncrypted', FileFactory::new([
            'name' => 'Fichier crypter',
            'size' => 100,
            'path' => 'tests/resources/toDecrypt.pdf',
        ]));

        $this->addState('filePdfProject1', FileFactory::new([
            'name' => 'Fichier projet pdf 1',
            'size' => 100,
            'path' => 'tests/resources/project1.pdf',
        ]));

        $this->addState('filePdfProject2', FileFactory::new([
            'name' => 'Fichier projet pdf 2',
            'size' => 100,
            'path' => 'tests/resources/project2.pdf',
        ]));

        $this->addState('fileConvocationOther', FileFactory::new([
            'name' => 'Fichier de convocation autre',
            'size' => 100,
            'path' => TMP_TESTDIR . '/convocation',
        ]));
    }
}
