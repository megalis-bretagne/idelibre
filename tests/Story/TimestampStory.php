<?php

namespace App\Tests\Story;

use App\Tests\Factory\TimestampFactory;
use Zenstruck\Foundry\Story;

final class TimestampStory extends Story
{
    public function build(): void
    {
        $this->addState('timestamp', TimestampFactory::new([
            'filePathContent' => 'fake path File content',
            'filePathTsa' => 'fakepathFile tsa',
        ]));
        // TODO build your story here (https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#stories)
    }
}
