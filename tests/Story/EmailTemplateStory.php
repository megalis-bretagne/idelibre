<?php

namespace App\Tests\Story;

use App\Entity\EmailTemplate;
use App\Tests\Factory\EmailTemplateFactory;
use Zenstruck\Foundry\Story;

final class EmailTemplateStory extends Story
{
    public function build(): void
    {
        $this->addState('emailTemplateConseilLs', EmailTemplateFactory::new([
            'structure' => StructureStory::libriciel(),
            'type' => TypeStory::typeConseilLibriciel(),
            'name' => 'Conseil Libriciel',
            'subject' => 'idelibre : une nouvelle convocation ...',
            'category' => EmailTemplate::CATEGORY_CONVOCATION,
            'content' => 'Voici mon template pour les seance de type conseil de la structure libriciel',
        ]));

        $this->addState('emailTemplateSansTypeLs', EmailTemplateFactory::new([
            'structure' => StructureStory::libriciel(),
            'name' => 'Sans type Libriciel',
            'subject' => 'idelibre : une nouvelle convocation ...',
            'category' => EmailTemplate::CATEGORY_CONVOCATION,
            'content' => 'Voici un template sans type associé appartenant à libriciel',
        ]));

        $this->addState('emailTemplateMtp', EmailTemplateFactory::new([
            'structure' => StructureStory::montpellier(),
            'name' => 'Sans type Montpellier',
            'subject' => 'idelibre : une nouvelle convocation ...',
            'category' => EmailTemplate::CATEGORY_CONVOCATION,
            'content' => 'Voici un template sans type associé apartenant à Montpellier',
        ]));

        $this->addState('emailTemplateInvitation', EmailTemplateFactory::new([
            'structure' => StructureStory::libriciel(),
            'name' => 'Invitation',
            'subject' => 'idelibre : une nouvelle invitation ...',
            'category' => EmailTemplate::CATEGORY_INVITATION,
            'content' => 'Voici un template pour une invitation',
        ]));
        // TODO build your story here (https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#stories)
    }
}
