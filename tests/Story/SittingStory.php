<?php

namespace App\Tests\Story;

use App\Tests\Factory\SittingFactory;
use DateTime;
use Zenstruck\Foundry\Story;

final class SittingStory extends Story
{
    public function build(): void
    {
        $this->addState('sittingConseilLibriciel', SittingFactory::new([
            'name' => 'Conseil Libriciel',
            'date' => new DateTime('2020-10-22'),
            'structure' => StructureStory::libriciel(),
            'convocationFile' => FileStory::fileConvocation(),
            'place' => 'Salle du conseil',
            'type' => TypeStory::typeConseilLibriciel(),
        ]));

        $this->addState('sittingBureauLibriciel', SittingFactory::new([
            'name' => 'Bureau Libriciel',
            'date' => new DateTime('2020-10-21'),
            'structure' => StructureStory::libriciel(),
            'convocationFile' => FileStory::fileConvocation2(),
            'place' => 'Salle du conseil',
            'type' => TypeStory::typeBureauLibriciel(),
        ]));

        $this->addState('sittingBureauLibricielWithoutProjectsAndConvocations', SittingFactory::new([
            'name' => 'Bureau Libriciel sans projets',
            'date' => new DateTime('2020-10-23'),
            'structure' => StructureStory::libriciel(),
            'convocationFile' => FileStory::fileConvocation3(),
            'place' => 'Salle du conseil',
            'type' => TypeStory::typeBureauLibriciel(),
        ]));

        $this->addState('sittingConseilWithTokenSent', SittingFactory::new([
            'name' => 'Conseil',
            'date' => new DateTime('2020-10-22'),
            'structure' => StructureStory::structureWithToken(),
            'convocationFile' => FileStory::fileConvocation4(),
            'place' => 'Agora',
            'type' => TypeStory::typeConseilLibriciel(),
        ]));

        $this->addState('sittingOtherconseil', SittingFactory::new([
            'name' => 'Autre Conseil Libriciel',
            'date' => new DateTime('now'),
            'structure' => StructureStory::libriciel(),
            'convocationFile' => FileStory::fileConvocationOther(),
            'place' => 'Salle du conseil',
            'type' => TypeStory::typeConseilLibriciel(),
        ]));
    }
}
