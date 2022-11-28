<?php

namespace App\Tests\Story;

use App\Tests\Factory\ComelusConnectorFactory;
use Zenstruck\Foundry\Story;

final class ComelusConnectorStory extends Story
{
    public function build(): void
    {
        $this->addState('comelusConnectorLibriciel', ComelusConnectorFactory::new([
            'structure' => StructureStory::libriciel(),
            'apiKey' => 'apikey',
            'url' => 'https://url.fr',
            'description' => 'my description',
            'active' => true,
        ]));
        // TODO build your story here (https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#stories)
    }
}
