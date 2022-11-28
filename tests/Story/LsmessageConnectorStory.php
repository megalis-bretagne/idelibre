<?php

namespace App\Tests\Story;

use App\Tests\Factory\LsmessageConnectorFactory;
use Zenstruck\Foundry\Story;

final class LsmessageConnectorStory extends Story
{
    public function build(): void
    {
        $this->addState('lsmessageConnectorLibriciel', LsmessageConnectorFactory::new([
            'structure' => StructureStory::libriciel(),
            'apikey' => 'apikey',
            'url' => 'https://url.fr',
            'sender' => 'sender',
            'content' => 'my content',
            'active' => true,
        ]));
        // TODO build your story here (https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#stories)
    }
}
