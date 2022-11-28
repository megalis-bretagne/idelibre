<?php

namespace App\Tests\Story;

use App\Tests\Factory\ApiUserFactory;
use Zenstruck\Foundry\Story;

final class ApiUserStory extends Story
{
    public function build(): void
    {
        $this->addState('apiAdminLibriciel', ApiUserFactory::new([
            'apiRole' => ApiRoleStory::roleApiStructureAdmin(),
            'structure' => StructureStory::libriciel(),
            'name' => 'connecteur api',
            'token' => '1234',
        ]));
        // TODO build your story here (https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#stories)
    }
}
