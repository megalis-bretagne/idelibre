<?php

namespace App\Tests\Story;

use App\Tests\Factory\PartyFactory;
use Zenstruck\Foundry\Story;

final class PartyStory extends Story
{
    public function build(): void
    {
        $this->addState('majorite', PartyFactory::new([
            'name' => 'Majorité',
            'structure' => StructureStory::libriciel(),
        ]));

        $this->addState('opposition', PartyFactory::new([
            'name' => 'Opposition',
            'structure' => StructureStory::libriciel(),
        ]));

        $this->addState('montpellier', PartyFactory::new([
            'name' => 'Montpellier',
            'structure' => StructureStory::montpellier(),
        ]));
    }
}
