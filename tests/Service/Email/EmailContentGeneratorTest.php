<?php

namespace App\Tests\Service\Email;

use App\Entity\EmailTemplate;
use App\Service\Email\EmailGenerator;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class EmailContentGeneratorTest extends WebTestCase
{
    public function testGenerateNotification()
    {
        $emailTemplate = new EmailTemplate();
        $emailTemplate->setContent('test de génération de message : #variable#');
        $emailTemplate->setSubject('test de génération de titre : #variable#');
        $generator = new EmailGenerator();
        $emailData = $generator->generateNotification($emailTemplate, ['#variable#' => 'test']);
        $this->assertEquals(
            'test de génération de message : test',
            $emailData->getContent()
        );
        $this->assertEquals(
            'test de génération de titre : test',
            $emailData->getSubject()
        );
    }
}
