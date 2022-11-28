<?php

namespace App\Tests\Story;

use App\Tests\Factory\ApiRoleFactory;
use Zenstruck\Foundry\Story;

final class ApiRoleStory extends Story
{
    public function build(): void
    {
        $this->addState('roleApiStructureAdmin', ApiRoleFactory::new([
            'name' => 'ApiStructureAdmin',
            'prettyName' => 'Administrateur api',
            'composites' => ['ROLE_API_STRUCTURE_ADMIN'],
        ]));
        // TODO build your story here (https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#stories)
    }
}
