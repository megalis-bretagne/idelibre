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
        $this->initResetPassword($structure);
        $this->initDefaultConvocationTemplates($structure);
        $this->initDefaultInvitationTemplates($structure);
    }


    private function initResetPassword(Structure $structure)
    {
        $forgetMsg = HtmlTag::START_HTML . 'Bonjour, <br>
    Vous avez effectué une demande de remise à zéro de mot de passe <br>
    Veuillez Cliquer ici pour le réinitialiser #reinitLink#' . HtmlTag::END_HTML;

        $forgetTemplate = new EmailTemplate();
        $forgetTemplate->setName('Mot de passe oublié')
            ->setSubject('Mot de passe oublié')
            ->setStructure($structure)
            ->setIsDefault(true)
            ->setCategory(EmailTemplate::RESET_PASSWORD)
            ->setContent($forgetMsg);
        $this->em->persist($forgetTemplate);
    }


    private function initDefaultConvocationTemplates(Structure $structure): void
    {
        $convocationMsg = HtmlTag::START_HTML . 'Bonjour, <br>
        Vous êtes convoqué à la séance ... <br> ' . HtmlTag::END_HTML;

        $convocationTemplate = new EmailTemplate();
        $convocationTemplate->setName('Convocation par défaut')
            ->setStructure($structure)
            ->setSubject('Convocation à la séance')
            ->setContent($convocationMsg)
            ->setIsDefault(true)
            ->setCategory(EmailTemplate::CONVOCATION);
        $this->em->persist($convocationTemplate);
    }


    private function initDefaultInvitationTemplates(Structure $structure): void
    {
        $invitationMsg = HtmlTag::START_HTML . 'Bonjour, <br>
        Vous êtes invité à la séance ... <br>' . HtmlTag::END_HTML;

        $invitationTemplate = new EmailTemplate();
        $invitationTemplate->setName('Invitation par défaut')
            ->setStructure($structure)
            ->setSubject('Invitation à la séance')
            ->setContent($invitationMsg)
            ->setIsDefault(true)
            ->setCategory(EmailTemplate::INVITATION);
        $this->em->persist($invitationTemplate);
    }
}
