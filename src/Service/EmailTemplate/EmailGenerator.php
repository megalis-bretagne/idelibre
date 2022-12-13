<?php

namespace App\Service\EmailTemplate;

use App\Entity\Convocation;
use App\Entity\EmailTemplate;
use App\Service\Email\EmailData;
use App\Service\Util\DateUtil;
use App\Service\Util\GenderConverter;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class EmailGenerator
{
    public function __construct(
        private DateUtil $dateUtil,
        private GenderConverter $genderConverter,
        private EmailTemplateManager $emailTemplateManager,
        private ParameterBagInterface $params,
    ) {
    }

    /**
     * @param array $params ["#maVar#" => 'value']
     */
    public function generateFromTemplate(EmailTemplate $emailTemplate, array $params): EmailData
    {
        $emailData = new EmailData(
            $this->generate($emailTemplate->getSubject(), $params),
            $this->generate($emailTemplate->getContent(), $params),
            $emailTemplate->getFormat()
        );

        $emailData->setIsAttachment($emailTemplate->getIsAttachment());

        return $emailData;
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
        $isInvitation = $this->isInvitation($convocation);

        if ($isInvitation) {
            $emailTemplate = $this->emailTemplateManager->getDefaultInvitationTemplate($convocation->getSitting()->getStructure());
        }

        if (null === $emailTemplate && !$isInvitation) {
            $emailTemplate = $this->emailTemplateManager->getDefaultConvocationTemplate($convocation->getSitting()->getStructure());
        }

        return $this->generateFromTemplate($emailTemplate, $this->generateParams($convocation));
    }

    public function isInvitation(Convocation $convocation): bool
    {
        return Convocation::CATEGORY_INVITATION === $convocation->getCategory();
    }

    public function generateParams(Convocation $convocation): array
    {
        $user = $convocation->getUser();
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
            TemplateTag::ACTOR_FIRST_NAME => $user->getFirstName(),
            TemplateTag::ACTOR_LAST_NAME => $user->getLastName(),
            TemplateTag::ACTOR_USERNAME => $user->getUsername(),
            TemplateTag::ACTOR_TITLE => $user->getTitle() ?? '',
            TemplateTag::ACTOR_GENDER => $this->genderConverter->format($user->getGender()),
            TemplateTag::SITTING_URL => $this->params->get('url_client'),
            TemplateTag::ACTOR_ATTENDANCE => $convocation->getAttendance(),
            TemplateTag::ACTOR_DEPUTY => $convocation->getDeputy(),
        ];
    }
}
