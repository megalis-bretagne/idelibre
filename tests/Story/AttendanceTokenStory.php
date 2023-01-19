<?php

namespace App\Tests\Story;

use App\Tests\Factory\AttendanceTokenFactory;
use Zenstruck\Foundry\Story;

final class AttendanceTokenStory extends Story
{
    public function build(): void
    {
        $this->addState('attendanceToken', AttendanceTokenFactory::new([
            'token' => 'mytoken',
            'convocation' => ConvocationStory::convocationActor2SentWithToken(),
        ]));
    }
}
