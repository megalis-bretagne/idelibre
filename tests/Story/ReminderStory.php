<?php

namespace App\Tests\Story;

use App\Tests\Factory\ReminderFactory;
use Zenstruck\Foundry\Story;

final class ReminderStory extends Story
{
    public function build(): void
    {
        $this->addState('reminderConseil', ReminderFactory::new([
            'type' => TypeStory::typeConseilLibriciel(),
            'isActive' => true,
            'duration' => 240,
        ]));
        // TODO build your story here (https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#stories)
    }
}
