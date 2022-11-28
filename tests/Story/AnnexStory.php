<?php

namespace App\Tests\Story;

use App\Tests\Factory\AnnexFactory;
use Zenstruck\Foundry\Story;

final class AnnexStory extends Story
{
    public function build(): void
    {
        $this->addState('annex1', AnnexFactory::new([
            'file' => FileStory::fileAnnex1(),
            'rank' => 0,
            'project' => ProjectStory::project1(),
        ]));

        $this->addState('annex2', AnnexFactory::new([
            'file' => FileStory::fileAnnex2(),
            'rank' => 1,
            'project' => ProjectStory::project1(),
        ]));
    }
}
