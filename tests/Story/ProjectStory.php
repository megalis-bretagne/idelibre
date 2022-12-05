<?php

namespace App\Tests\Story;

use App\Tests\Factory\ProjectFactory;
use Zenstruck\Foundry\Story;

final class ProjectStory extends Story
{
    public function build(): void
    {
        $this->addState('project1', ProjectFactory::new([
            'rank' => 0,
            'file' => FileStory::fileProject1(),
            'name' => 'Project 1',
            'sitting' => SittingStory::sittingConseilLibriciel(),
            'theme' => ThemeStory::financeTheme(),
            'reporter' => UserStory::actorLibriciel1(),
        ]));

        $this->addState('project2', ProjectFactory::new([
            'rank' => 1,
            'name' => 'Project 2',
            'file' => FileStory::fileProject2(),
            'sitting' => SittingStory::sittingConseilLibriciel(),
        ]));

        $this->addState('projectFile', ProjectFactory::new([
            'rank' => 3,
            'name' => 'ProjectFile',
            'file' => FileStory::fileProject(),
            'sitting' => SittingStory::sittingConseilLibriciel(),
        ]));
    }
}