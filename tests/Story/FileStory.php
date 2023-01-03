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
            'cached_at' => new \DateTimeImmutable('+4 weeks')
        ]));

        $this->addState('fileProject2', FileFactory::new([
            'name' => 'Fichier projet 2',
            'size' => 100,
            'path' => '/tmp/fileProject2',
            'cached_at' => new \DateTimeImmutable('+4 weeks')
        ]));

        $this->addState('fileAnnex1', FileFactory::new([
            'name' => 'Fichier annexe 1',
            'size' => 100,
            'path' => '/tmp/fileAnnex1',
            'cached_at' => new \DateTimeImmutable('+4 weeks')
        ]));

        $this->addState('fileAnnex2', FileFactory::new([
            'name' => 'Fichier annexe 2',
            'size' => 100,
            'path' => '/tmp/fileAnnex2',
            'cached_at' => new \DateTimeImmutable('+4 weeks')
        ]));

        $this->addState('fileConvocation', FileFactory::new([
            'name' => 'Fichier de convocation',
            'size' => 100,
            'path' => '/tmp/convocation',
            'cached_at' => new \DateTimeImmutable('+4 weeks')
        ]));

        $this->addState('fileConvocation2', FileFactory::new([
            'name' => 'Fichier de convocation',
            'size' => 100,
            'path' => '/tmp/convocation',
            'cached_at' => new \DateTimeImmutable('+4 weeks')
        ]));

        $this->addState('fileConvocation3', FileFactory::new([
            'name' => 'Fichier de convocation',
            'size' => 100,
            'path' => '/tmp/convocation',
            'cached_at' => new \DateTimeImmutable('+4 weeks')
        ]));
    }
}
