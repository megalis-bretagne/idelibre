<?php

namespace App\Tests\Story;

use App\Tests\Factory\ForgetTokenFactory;
use Zenstruck\Foundry\Story;

final class ForgetTokenStory extends Story
{
    public function build(): void
    {
        $this->addState('forgetToken', ForgetTokenFactory::new([
            'user' => UserStory::adminLibriciel(),
            'token' => 'forgetToken',
        ]));
        // TODO build your story here (https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#stories)
    }
}
