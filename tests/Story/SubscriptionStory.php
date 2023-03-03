<?php

namespace App\Tests\Story;

use App\Tests\Factory\SubscriptionFactory;
use Zenstruck\Foundry\Story;

final class SubscriptionStory extends Story
{
    public function build(): void
    {
        $this->addState('subscription_adminLibriciel', SubscriptionFactory::new([
            'user' => UserStory::adminLibriciel(),
            'acceptMailRecap' => false,
            'createdAt' => null,
        ]));

        $this->addState('subscription_secretaryLibriciel1', SubscriptionFactory::new([
            'user' => UserStory::secretaryLibriciel1(),
            'acceptMailRecap' => false,
            'createdAt' => null,
        ]));
    }
}
