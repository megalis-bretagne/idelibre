<?php

namespace App\Tests\Story;

use App\Tests\Factory\ThemeFactory;
use Zenstruck\Foundry\Story;

final class ThemeStory extends Story
{
    public function build(): void
    {
        $this->addState('rootTheme', ThemeFactory::new([
            'name' => 'ROOT',
            'fullName' => 'ROOT',
            'structure' => StructureStory::libriciel(),
        ]));

        $this->addState('financeTheme', ThemeFactory::new([
            'name' => 'Finance',
            'parent' => ThemeStory::rootTheme(),
            'fullName' => 'Finance',
            'structure' => StructureStory::libriciel(),
        ]));

        $this->addState('ecoleTheme', ThemeFactory::new([
            'name' => 'Ecole',
            'parent' => ThemeStory::rootTheme(),
            'fullName' => 'Ecole',
            'structure' => StructureStory::libriciel(),
        ]));

        $this->addState('rhTheme', ThemeFactory::new([
            'name' => 'rh',
            'parent' => ThemeStory::rootTheme(),
            'fullName' => 'rh',
            'structure' => StructureStory::libriciel(),
        ]));

        $this->addState('budgetTheme', ThemeFactory::new([
            'name' => 'budget',
            'parent' => ThemeStory::financeTheme(),
            'fullName' => 'Finance, budget',
            'structure' => StructureStory::libriciel(),
        ]));

        $this->addState('rootThemeMtp', ThemeFactory::new([
            'name' => 'ROOT',
            'fullName' => 'ROOT',
            'structure' => StructureStory::montpellier(),
        ]));

        $this->addState('urbanismeThemeMtp', ThemeFactory::new([
            'name' => 'Urbanisme Montpellier',
            'parent' => ThemeStory::rootThemeMtp(),
            'structure' => StructureStory::montpellier(),
        ]));

        // TODO build your story here (https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#stories)
    }
}
