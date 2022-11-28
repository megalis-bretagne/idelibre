<?php

namespace App\Tests\Story;

use App\Tests\Factory\StructureFactory;
use Zenstruck\Foundry\Story;

final class StructureStory extends Story
{
    public function build(): void
    {
        $this->addState('libriciel', StructureFactory::new([
            'name' => 'Libriciel',
            'suffix' => 'libriciel',
            'legacyConnectionName' => 'libriciel',
            'replyTo' => 'libriciel@exemple.org',
            'timezone' => TimezoneStory::paris(),
        ]));

        $this->addState('montpellier', StructureFactory::new([
            'name' => 'Montpellier',
            'suffix' => 'montpellier',
            'legacyConnectionName' => 'montpellier',
            'replyTo' => 'montpellier@exemple.org',
            'timezone' => TimezoneStory::paris(),
            'group' => GroupStory::recia(),
        ]));
    }
}
