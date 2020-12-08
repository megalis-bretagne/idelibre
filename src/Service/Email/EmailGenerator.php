<?php


namespace App\Service\Email;

use App\Entity\EmailTemplate;

class EmailGenerator
{
    /**
     * @param array $params ["#maVar#" => 'value']
     */
    public function generateNotification(EmailTemplate $emailTemplate, array $params): EmailData
    {
        return new EmailData(
            $this->generate($emailTemplate->getSubject(), $params),
            $this->generate($emailTemplate->getContent(), $params)
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
