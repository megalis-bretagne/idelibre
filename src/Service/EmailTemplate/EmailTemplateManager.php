<?php


namespace App\Service\EmailTemplate;

use App\Entity\EmailTemplate;
use App\Entity\Structure;
use App\Repository\EmailTemplateRepository;
use Doctrine\ORM\EntityManagerInterface;

class EmailTemplateManager
{
    private EmailTemplateRepository $templateRepository;
    private EntityManagerInterface $em;


    public function __construct(EmailTemplateRepository $templateRepository, EntityManagerInterface $em)
    {
        $this->templateRepository = $templateRepository;
        $this->em = $em;
    }


    public function initDefaultTemplates(Structure $structure): void
    {
        $forgetMsg = 'bonjour, <br>
    Vous avez effectuer une demande remise à zéro de mot de passe <br>
    Veuillez Cliquer ici pour le réinitialiser #reinitLink#';
        $forgetTpl = new EmailTemplate();
        $forgetTpl->setName('Mot de passe oublié')
            ->setStructure($structure)
            ->setContent($forgetMsg);
        $this->em->persist($forgetTpl);

        $notifyMsg = 'bonjour, <br>
Un dossier a été mis à votre disposition. veuillez cliquez sur le lien pour le consulter <br>
#linkUrl#';
        $notifyTpl = new EmailTemplate();
        $notifyTpl->setName('Message de notification')
            ->setStructure($structure)
            ->setContent($notifyMsg);
        $this->em->persist($notifyTpl);

        $this->em->flush();
    }


    public function save(EmailTemplate $template, Structure $structure): void
    {
        $template->setStructure($structure);
        $this->em->persist($template);
        $this->em->flush();
    }

    public function delete(EmailTemplate $emailTemplate): void
    {
        $this->em->remove($emailTemplate);
        $this->em->flush();
    }
}
