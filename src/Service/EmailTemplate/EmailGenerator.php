<?php

namespace App\Service\EmailTemplate;

use App\Entity\Convocation;
use App\Entity\EmailTemplate;
use App\Service\Email\EmailData;
use App\Service\Util\DateUtil;
use App\Service\Util\GenderConverter;

class EmailGenerator
{
    private DateUtil $dateUtil;
    private GenderConverter $genderConverter;
    private EmailTemplateManager $emailTemplateManager;

    public function __construct(DateUtil $dateUtil, GenderConverter $genderConverter, EmailTemplateManager $emailTemplateManager)
    {
        $this->dateUtil = $dateUtil;
        $this->genderConverter = $genderConverter;
        $this->emailTemplateManager = $emailTemplateManager;
    }

    /**
     * @param array $params ["#maVar#" => 'value']
     */
    public function generateFromTemplate(EmailTemplate $emailTemplate, array $params): EmailData
    {
        return new EmailData(
            $this->generate($emailTemplate->getSubject(), $params),
            HtmlTag::START_HTML . $this->generate($emailTemplate->getContent(), $params) . HtmlTag::END_HTML
        );
    }

    /**
     * @param array $params ["#maVar#" => $value]
     */
    private function generate(string $content, array $params): string
    {
        return strtr($content, $params);
    }

    public function generateFromTemplateAndConvocation(?EmailTemplate $emailTemplate, Convocation $convocation): EmailData
    {
        if (null === $emailTemplate) {
            $emailTemplate = $this->emailTemplateManager->getDefaultConvocationTemplate($convocation->getSitting()->getStructure());
        }

        return $this->generateFromTemplate($emailTemplate, $this->generateParams($convocation));
        //$email->setTo($convocation->getActor()->getEmail());
    }

    public function generateParams(Convocation $convocation): array
    {
        $actor = $convocation->getActor();
        $sitting = $convocation->getSitting();

        return [
            TemplateTag::SITTING_TYPE => $sitting->getName(),
            TemplateTag::SITTING_DATE => $this->dateUtil->getFormattedDate(
                $sitting->getDate(),
                $sitting->getStructure()->getTimezone()->getName()
            ),
            TemplateTag::SITTING_TIME => $this->dateUtil->getFormattedTime(
                $sitting->getDate(),
                $sitting->getStructure()->getTimezone()->getName()
            ),
            TemplateTag::SITTING_PLACE => $sitting->getPlace() ?? '',
            TemplateTag::ACTOR_FIRST_NAME => $actor->getFirstName(),
            TemplateTag::ACTOR_LAST_NAME => $actor->getLastName(),
            TemplateTag::ACTOR_USERNAME => $actor->getUsername(),
            TemplateTag::ACTOR_TITLE => $actor->getTitle() ?? '',
            TemplateTag::ACTOR_GENDER => $this->genderConverter->format($actor->getGender()),
        ];
    }
}
