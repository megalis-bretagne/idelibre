<?php

namespace App\Service\EmailTemplate;

use App\Entity\EmailTemplate;
use App\Entity\Structure;
use Doctrine\ORM\EntityManagerInterface;

class DefaultTemplateCreator
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function initDefaultTemplates(Structure $structure)
    {
        $this->initDefaultConvocationTemplates($structure);
        $this->initDefaultInvitationTemplates($structure);
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
}
