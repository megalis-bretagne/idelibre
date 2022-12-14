<?php

namespace App\Service\EmailTemplate;

use App\Entity\EmailTemplate;
use App\Entity\Structure;
use Doctrine\ORM\EntityManagerInterface;

class DefaultTemplateCreator
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    public function initDefaultTemplates(Structure $structure)
    {
        $this->initDefaultConvocationTemplates($structure);
        $this->initDefaultInvitationTemplates($structure);
        $this->initDefaultRecapitulatifTemplates($structure);
    }

    private function initDefaultConvocationTemplates(Structure $structure): void
    {
        $convocationTemplate = new EmailTemplate();
        $convocationTemplate->setName('Convocation par défaut')
            ->setStructure($structure)
            ->setSubject('Convocation à la séance')
            ->setContent(DefaultTemplate::CONVOCATION)
            ->setIsDefault(true)
            ->setCategory(EmailTemplate::CATEGORY_CONVOCATION);
        $this->em->persist($convocationTemplate);
    }

    private function initDefaultInvitationTemplates(Structure $structure): void
    {
        $invitationTemplate = new EmailTemplate();
        $invitationTemplate->setName('Invitation par défaut')
            ->setStructure($structure)
            ->setSubject('Invitation à la séance')
            ->setContent(DefaultTemplate::INVITATION)
            ->setIsDefault(true)
            ->setCategory(EmailTemplate::CATEGORY_INVITATION);
        $this->em->persist($invitationTemplate);
    }

    private function initDefaultRecapitulatifTemplates(Structure $structure): void
    {
        $recapitulatifTemplate = new EmailTemplate();
        $recapitulatifTemplate->setName('Récapitulatif par défaut')
            ->setStructure($structure)
            ->setSubject('Récapitulatif des absences/présences aux séances')
            ->setContent(DefaultTemplate::RECAPITULATIF)
            ->setIsDefault(true)
            ->setCategory(EmailTemplate::CATEGORY_RECAPITULATIF);
        $this->em->persist($recapitulatifTemplate);
    }
}
