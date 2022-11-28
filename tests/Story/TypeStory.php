<?php

namespace App\Tests\Story;

use App\Tests\Factory\TypeFactory;
use Zenstruck\Foundry\Story;

final class TypeStory extends Story
{
    public function build(): void
    {
        $this->addState('typeConseilLibriciel', TypeFactory::new([
            'name' => 'Conseil Communautaire Libriciel',
            'structure' => StructureStory::libriciel(),
            'associatedUsers' => [
                'associatedUser1' => UserStory::actorLibriciel1(),
                'associatedUser2' => UserStory::actorLibriciel2(),
            ],
            'authorizedSecretaries' => [UserStory::secretaryLibriciel1()],
        ]));

        $this->addState('typeBureauLibriciel', TypeFactory::new([
            'name' => 'Bureau Communautaire Libriciel',
            'structure' => StructureStory::libriciel(),
            'associatedUsers' => ['associatedUser1' => UserStory::actorLibriciel2()],
        ]));

        $this->addState('typeConseilMontpellier', TypeFactory::new([
            'name' => 'Conseil Municipal Montpellier',
            'structure' => StructureStory::montpellier(),
        ]));

        $this->addState('testTypeLS', TypeFactory::new([
            'name' => 'unUsedType',
            'structure' => StructureStory::libriciel(),
            'associatedUsers' => [
                'associatedUser1' => UserStory::actorLibriciel1(),
                'associatedUser2' => UserStory::actorLibriciel2(),
            ],
        ]));

        // TODO build your story here (https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#stories)
    }
}
