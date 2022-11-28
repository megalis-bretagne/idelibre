<?php

namespace App\Tests\Entity\Connector;

use App\Entity\Connector\Exception\LsmessageConnectorException;
use App\Entity\Connector\LsmessageConnector;
use App\Entity\Structure;
use App\Tests\HasValidationError;
use App\Tests\StringTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class LsmessageConnectorTest extends WebTestCase
{
    use HasValidationError;
    use StringTrait;

    private ValidatorInterface $validator;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->validator = self::getContainer()->get('validator');
    }

    /**
     * @throws LsmessageConnectorException
     */
    private function createDummyConnector(): LsmessageConnector
    {
        return (new LsmessageConnector(new Structure()))
            ->setActive(true)
            ->setApiKey('1234')
            ->setContent('content')
            ->setSender('sender')
            ->setUrl('https://test.url.fr');
    }

    public function testSetUrl()
    {
        $connector = $this->createDummyConnector()
            ->setUrl('https://test.url.fr');

        $this->assertHasValidationErrors($connector, 0);
    }
}
