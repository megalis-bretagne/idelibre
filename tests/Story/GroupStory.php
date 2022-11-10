<?php

namespace App\Tests\Story;

use App\Tests\Factory\GroupFactory;
use Zenstruck\Foundry\Story;

final class GroupStory extends Story
{
    public function build(): void
    {
        $this->addState('recia', GroupFactory::new([
            'name' => 'Recia',
            'isStructureCreator' => true,
        ]));

        $this->addState('notStructureCreator', GroupFactory::new([
            'name' => 'notStructureCreator',
            'isStructureCreator' => false,
        ]));
    }
}
