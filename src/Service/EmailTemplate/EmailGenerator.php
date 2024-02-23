<?php

namespace App\Service\EmailTemplate;

use App\Entity\Convocation;
use App\Entity\EmailTemplate;
use App\Entity\Sitting;
use App\Entity\User;
use App\Service\Base64_encoder\Encoder;
use App\Service\Email\EmailData;
use App\Service\Util\DateUtil;
use App\Service\Util\GenderConverter;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

class EmailGenerator
{
    public function __construct(
        private readonly DateUtil $dateUtil,
        private readonly GenderConverter $genderConverter,
        private readonly EmailTemplateManager $emailTemplateManager,
        private readonly ParameterBagInterface $bag,
        private readonly RouterInterface $router,
        private readonly Encoder $encoder
    ) {
    }

    /**
     * @param array $params ["#maVar#" => 'value']
     */
    public function generateFromTemplate(EmailTemplate $emailTemplate, array $params): EmailData
    {
        $emailData = new EmailData(
            $this->generate($emailTemplate->getSubject(), $params),
            $this->generate($this->encoder->encodeImages($emailTemplate->getContent()) , $params),
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
            $emailTemplate = $this->emailTemplateManager->getDefaultInvitationTemplate(
                $convocation->getSitting()->getStructure()
            );
        }

        if (null === $emailTemplate && !$isInvitation) {
            $emailTemplate = $this->emailTemplateManager->getDefaultConvocationTemplate(
                $convocation->getSitting()->getStructure()
            );
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
            TemplateTag::SITTING_URL => $this->bag->get('url_client'),
            TemplateTag::ACTOR_ATTENDANCE => $convocation->getAttendance(),
            TemplateTag::ACTOR_DEPUTY => $convocation->getDeputy(),
            TemplateTag::CONFIRM_PRESENCE_URL => $this->generateAttendanceUrl($convocation->getAttendanceToken()?->getToken()),
        ];
    }

    private function getGeneralParameter(User $user): array
    {
        return [
            TemplateTag::FIRST_NAME_RECIPIENT => $user->getFirstName(),
            TemplateTag::LAST_NAME_RECIPIENT => $user->getLastName(),
            TemplateTag::PRODUCT_NAME => $this->bag->get('product_name'),
        ];
    }

    public function generateSubject(User $user, string $subject): string
    {
        return $this->generate($subject, $this->getGeneralParameter($user));
    }

    private function generateContentHtml(string $content, array $parameterForHtml): string
    {
        return HtmlTag::START_HTML . $this->generate($content, $parameterForHtml) . HtmlTag::END_HTML;
    }

    private function generateContentText(string $content, array $parameterForText): string
    {
        return strip_tags(html_entity_decode($this->generate($content, $parameterForText)));
    }

    public function generateInitPassword(User $user, string $token): array
    {
        $prenom = TemplateTag::FIRST_NAME_RECIPIENT;
        $nom = TemplateTag::LAST_NAME_RECIPIENT;
        $lien = TemplateTag::INITIALIZATION_PASSWORD_URL;
        $productName = TemplateTag::PRODUCT_NAME;
        $content = <<<HTML
            <p>Bonjour $prenom $nom,</p>\r
            <p>Un administrateur de la plateforme $productName vient de vous créer un compte sur la plateforme.</p>\r
            <p>Veuillez cliquer sur le lien pour initialiser votre mot de passe : $lien</p>\r
            <p>Merci</p>
        HTML;

        $resetPasswordUrl = $this->generateResetPasswordUrl($token);
        $generalParameter = $this->getGeneralParameter($user);

        $parameterForHtml = $generalParameter + [
            TemplateTag::INITIALIZATION_PASSWORD_URL => "<a href='$resetPasswordUrl'>Initialiser votre mot de passe</a>",
        ];

        $parameterForText = $generalParameter + [
            TemplateTag::INITIALIZATION_PASSWORD_URL => $resetPasswordUrl,
        ];

        return [
            'html' => $this->generateContentHtml($content, $parameterForHtml),
            'text' => $this->generateContentText($content, $parameterForText),
        ];
    }

    public function generateForgetPassword(User $user, string $token): array
    {
        $prenom = TemplateTag::FIRST_NAME_RECIPIENT;
        $nom = TemplateTag::LAST_NAME_RECIPIENT;
        $lien = TemplateTag::FORGET_PASSWORD_URL;
        $productName = TemplateTag::PRODUCT_NAME;
        $content = <<<HTML
            <p>Bonjour $prenom $nom,</p>\r
            <p>Vous avez effectué une demande remise &agrave; z&eacute;ro de mot de passe de l'application $productName.</p>\r
            <p>Veuillez cliquer sur le lien pour le r&eacute;initialiser : $lien</p>\r
            <p>Si vous n'&ecirc;tes pas &agrave; l'origine de cette demande, veuillez contacter votre administrateur.</p>\r
            <p>Merci</p>
        HTML;

        $resetPasswordUrl = $this->generateResetPasswordUrl($token);

        $generalParameter = $this->getGeneralParameter($user);

        $parameterForHtml = $generalParameter + [
            TemplateTag::FORGET_PASSWORD_URL => "<a href='$resetPasswordUrl'>Réinitialiser votre mot de passe oublié</a>",
        ];

        $parameterForText = $generalParameter + [
            TemplateTag::FORGET_PASSWORD_URL => $resetPasswordUrl,
        ];

        return [
            'html' => $this->generateContentHtml($content, $parameterForHtml),
            'text' => $this->generateContentText($content, $parameterForText),
        ];
    }

    public function generateReloadPassword(User $user, string $token): array
    {
        $prenom = TemplateTag::FIRST_NAME_RECIPIENT;
        $nom = TemplateTag::LAST_NAME_RECIPIENT;
        $lien = TemplateTag::UPDATE_PASSWORD_URL;
        $productName = TemplateTag::PRODUCT_NAME;
        $content = <<<HTML
            <p>Bonjour $prenom $nom,</p>\r
            <p>Une demande de réinitialisation de votre mot de passe a &eacute;t&eacute; faite par un administrateur de l'application $productName</p>\r
            <p>Veuillez cliquer sur le lien pour le r&eacute;initialiser : $lien</p>\r
            <p>Merci</p>
        HTML;

        $resetPasswordUrl = $this->generateResetPasswordUrl($token);

        $generalParameter = $this->getGeneralParameter($user);

        $parameterForHtml = $generalParameter + [
            TemplateTag::UPDATE_PASSWORD_URL => "<a href='$resetPasswordUrl'>Réinitialiser votre mot de passe</a>",
        ];

        $parameterForText = $generalParameter + [
            TemplateTag::UPDATE_PASSWORD_URL => $resetPasswordUrl,
        ];

        return [
            'html' => $this->generateContentHtml($content, $parameterForHtml),
            'text' => $this->generateContentText($content, $parameterForText),
        ];
    }

    private function generateResetPasswordUrl($token): string
    {
        return $this->router->generate(
            'app_reset',
            ['token' => $token],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
    }

    public function generateAttendanceUrl(?string $token): string
    {
        if (!$token) {
            return 'Impossible de confirmer sa présence';
        }

        return $this->router->generate(
            'app_attendance_confirmation',
            ['token' => $token],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
    }

    public function generateSittingParams(Sitting $sitting)
    {
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
            TemplateTag::SITTING_URL => $this->bag->get('url_client'),
        ];
    }

    public function generateEmailTemplateSubject(Sitting $sitting, string $subject): string
    {
        return $this->generate($subject, $this->generateSittingParams($sitting));
    }
}
