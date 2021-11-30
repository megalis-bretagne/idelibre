<?php

namespace App\Tests\Entity\Connector;

use App\Entity\Connector\ComelusConnector;
use App\Entity\Connector\Exception\ComelusConnectorException;
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
            ->setApiKey("1234")
            ->setContent('content')
            ->setSender('sender')
            ->setUrl('https://test.url.fr');
    }

    public function testSetUrl()
    {
        $connector = $this->createDummyConnector()
            ->setUrl('https://test.url.fr');

        $this->assertHasValidationErrors($connector, 0 );
    }


    public function testSetUrlTooLong()
    {
        $this->expectException(LsmessageConnectorException::class);
        $this->expectExceptionMessage('length should be <= 255');
        $this->createDummyConnector()
            ->setUrl('https://' . $this->genString(255). '.fr');
    }

    public function testApiKeyTooLong()
    {
        $this->expectException(LsmessageConnectorException::class);
        $this->expectExceptionMessage('length should be <= 255');
        $this->createDummyConnector()
            ->setApiKey($this->genString(260));
    }

    public function testContentTooLong()
    {
        $this->expectException(LsmessageConnectorException::class);
        $this->expectExceptionMessage('length should be <= 140');
        $this->createDummyConnector()
            ->setContent($this->genString(142));
    }

    public function testSenderTooLong()
    {
        $this->expectException(LsmessageConnectorException::class);
        $this->expectExceptionMessage('length should be <= 11');
        $this->createDummyConnector()
            ->setSender($this->genString(12));
    }




}
