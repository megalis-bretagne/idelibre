<?php

namespace App\Tests\Story;

use App\Tests\Factory\TimezoneFactory;
use Zenstruck\Foundry\Story;

final class TimezoneStory extends Story
{
    public function build(): void
    {
        $this->addState('paris', TimezoneFactory::new([
            'name' => 'Europe/Paris'
        ]));
    }
}
