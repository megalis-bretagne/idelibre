<?php


namespace App\Service\Email;

use App\Entity\EmailTemplate;
use App\Service\EmailTemplate\HtmlTag;

class EmailGenerator
{
    /**
     * @param array $params ["#maVar#" => 'value']
     */
    public function generateNotification(EmailTemplate $emailTemplate, array $params): EmailData
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
}
