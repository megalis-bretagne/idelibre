<?php

namespace App\Service\EmailTemplate;

use App\Entity\EmailTemplate;
use App\Entity\Structure;
use App\Repository\EmailTemplateRepository;
use App\Service\File\FileManager;
use Doctrine\ORM\EntityManagerInterface;

class EmailTemplateManager
{
    public function __construct(
        private readonly EmailTemplateRepository $templateRepository,
        private readonly EntityManagerInterface $em,
    ) {
    }

    public function save(EmailTemplate $template): void
    {
        $template->setContent(html_entity_decode($template->getContent()));
        $this->em->persist($template);
        $this->em->flush();
    }

    public function delete(EmailTemplate $emailTemplate): void
    {
        $this->em->remove($emailTemplate);
        $this->em->flush();
    }

    public function getDefaultConvocationTemplate(Structure $structure): EmailTemplate
    {
        return $this->templateRepository->findOneBy([
            'category' => EmailTemplate::CATEGORY_CONVOCATION,
            'structure' => $structure,
            'isDefault' => true,
        ]);
    }

    public function getDefaultInvitationTemplate(Structure $structure): EmailTemplate
    {
        return $this->templateRepository->findOneBy([
            'category' => EmailTemplate::CATEGORY_INVITATION,
            'structure' => $structure,
            'isDefault' => true,
        ]);
    }

}
