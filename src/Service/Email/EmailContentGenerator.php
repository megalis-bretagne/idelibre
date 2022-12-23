<?php

namespace App\Service\Email;

use App\Entity\User;
use App\Service\EmailTemplate\HtmlTag;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

class EmailContentGenerator
{
    public function __construct(private readonly ParameterBagInterface $bag, private readonly RouterInterface $router)
    {
    }

    private function getGeneralParameter(User $user): array
    {
        return [
            "#PRENOM_DESTINATAIRE#" => $user->getFirstName(),
            "#NOM_DESTINATAIRE#" => $user->getLastName(),
            "#NOM_PRODUIT#" => $this->bag->get('product_name'),
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
        $content = <<<HTML
            <p>Bonjour #PRENOM_DESTINATAIRE# #NOM_DESTINATAIRE#,</p>\r
            <p>Un administrateur de la plateforme #NOM_PRODUIT# vient de vous créer un compte sur la plateforme.</p>\r
            <p>Veuillez cliquer sur le lien pour initialiser votre mot de passe : #LIEN_MDP_INITIALISATION#</p>\r
            <p>Merci</p>
        HTML;

        $resetPasswordUrl = $this->generateResetPasswordUrl($token);

        $generalParameter = $this->getGeneralParameter($user);

        $parameterForHtml = $generalParameter + [
            "#LIEN_MDP_INITIALISATION#" => "<a href='$resetPasswordUrl'>Initialiser votre mot de passe</a>",
        ];

        $parameterForText = $generalParameter + [
            "#LIEN_MDP_INITIALISATION#" => $resetPasswordUrl,
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

    /**
     * @param array $params ["#maVar#" => $value]
     */
    public function generate(string $content, array $params): string
    {
        return strtr($content, $params);
    }
}
