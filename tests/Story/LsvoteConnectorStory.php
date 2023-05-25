<?php

namespace App\Tests\Story;

use App\Tests\Factory\LsvoteConnectorFactory;
use Zenstruck\Foundry\Story;

final class LsvoteConnectorStory extends Story
{
    public function build(): void
    {
        $this->addState('lsvoteConnectorLibriciel', LsvoteConnectorFactory::new([
            'structure' => StructureStory::libriciel(),
            'apikey' => 'apikey',
            'url' => 'https://url.fr',
            'active' => true,
        ]));
    }
}
