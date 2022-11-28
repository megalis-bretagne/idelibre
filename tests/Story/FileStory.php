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
            'path' => '/tmp/fileProject1',
        ]));

        $this->addState('fileProject2', FileFactory::new([
            'name' => 'Fichier projet 2',
            'size' => 100,
            'path' => '/tmp/fileProject2',
        ]));

        $this->addState('fileProject', FileFactory::new([
            'name' => 'FileProject',
            'size' => 100,
            'path' => __DIR__ . '/../file/download/FileProject.pdf',
        ]));

        $this->addState('fileAnnex1', FileFactory::new([
            'name' => 'Fichier annexe 1',
            'size' => 100,
            'path' => '/tmp/fileAnnex1',
        ]));

        $this->addState('fileAnnex2', FileFactory::new([
            'name' => 'Fichier annexe 2',
            'size' => 100,
            'path' => '/tmp/fileAnnex2',
        ]));

        $this->addState('fileConvocation', FileFactory::new([
            'name' => 'Fichier de convocation',
            'size' => 100,
            'path' => '/tmp/convocation',
        ]));

        $this->addState('fileConvocation2', FileFactory::new([
            'name' => 'Fichier de convocation 2',
            'size' => 100,
            'path' => '/tmp/convocation2',
        ]));

        $this->addState('fileConvocation3', FileFactory::new([
            'name' => 'Fichier de convocation 3',
            'size' => 100,
            'path' => '/tmp/convocation3',
        ]));
    }
}
