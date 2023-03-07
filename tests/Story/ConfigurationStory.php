<?php

namespace App\Tests\Story;

use App\Tests\Factory\ConfigurationFactory;
use Zenstruck\Foundry\Story;

final class ConfigurationStory extends Story
{
    public function build(): void
    {
        $this->addState('configurationLibriciel', ConfigurationFactory::new([
            'structure' => StructureStory::libriciel(),
            'isSharedAnnotation' => true,
            'minimumEntropy' => 82,
        ]));

        $this->addState('configurationMontpellier', ConfigurationFactory::new([
            'structure' => StructureStory::montpellier(),
            'isSharedAnnotation' => true,
            'minimumEntropy' => 82,
        ]));

        // TODO build your story here (https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#stories)
    }
}
