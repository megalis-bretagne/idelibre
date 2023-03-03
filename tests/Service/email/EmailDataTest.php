<?php

namespace App\Tests\Service\email;

use App\Service\Email\EmailData;
use App\Tests\HasValidationError;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class EmailDataTest extends WebTestCase
{
    use ResetDatabase;
    use Factories;
    use HasValidationError;

    private ValidatorInterface $validator;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->validator = self::getContainer()->get(ValidatorInterface::class);
        self::ensureKernelShutdown();
    }

    public function testIsValid()
    {
        $emailData = (new EmailData('subject', 'content'));
        $this->assertHasValidationErrors($emailData, 0);
    }
}
