<?php

namespace App\Tests\Story;

use App\Entity\Convocation;
use App\Tests\Factory\ConvocationFactory;
use Zenstruck\Foundry\Story;

final class ConvocationStory extends Story
{
    public function build(): void
    {
        $this->addState('convocationActor1', ConvocationFactory::new([
            'sitting' => SittingStory::sittingConseilLibriciel(),
            'user' => UserStory::actorLibriciel1(),
            'category' => Convocation::CATEGORY_CONVOCATION,
            'sentTimestamp' => null,
        ]));

        $this->addState('convocationActor2Sent', ConvocationFactory::new([
            'sitting' => SittingStory::sittingConseilLibriciel(),
            'user' => UserStory::actorLibriciel2(),
            'sentTimestamp' => TimestampStory::timestamp(),
            'category' => Convocation::CATEGORY_CONVOCATION,
        ]));

        $this->addState('convocationActor2SentWithToken', ConvocationFactory::new([
            'sitting' => SittingStory::sittingConseilWithTokenSent(),
            'user' => UserStory::actorLibriciel1(),
            'category' => Convocation::CATEGORY_CONVOCATION,
            'sentTimestamp' => null,
        ]));

        $this->addState('convocationActor3SentWithToken', ConvocationFactory::new([
            'sitting' => SittingStory::sittingConseilWithTokenSent(),
            'user' => UserStory::actorWithDeputy(),
            'category' => Convocation::CATEGORY_CONVOCATION,
            'sentTimestamp' => null,
        ]));
    }
}
