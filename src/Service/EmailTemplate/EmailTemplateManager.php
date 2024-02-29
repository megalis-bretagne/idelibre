<?php

namespace App\Service\EmailTemplate;

use App\Entity\EmailTemplate;
use App\Entity\Structure;
use App\Repository\EmailTemplateRepository;
use App\Service\ImageHandler\Encoder;
use Doctrine\ORM\EntityManagerInterface;

class EmailTemplateManager
{
    public function __construct(
        private readonly EmailTemplateRepository $templateRepository,
        private readonly EntityManagerInterface $em,
        private readonly Encoder $encoder,
    ) {
    }

    public function save(EmailTemplate $template): void
    {
        $updatedContent = $this->encoder->imageHandlerAndUpdateContent($template->getContent(), $template->getStructure()->getId());
        $template->setContent(html_entity_decode($updatedContent));
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
