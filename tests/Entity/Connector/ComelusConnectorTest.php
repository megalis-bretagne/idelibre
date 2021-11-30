<?php

namespace App\Tests\Entity\Connector;

use App\Entity\Connector\ComelusConnector;
use App\Entity\Connector\Exception\ComelusConnectorException;
use App\Entity\Structure;
use App\Tests\HasValidationError;
use App\Tests\StringTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ComelusConnectorTest extends WebTestCase
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
     * @throws ComelusConnectorException
     */
    private function createDummyConnector(): ComelusConnector
    {
        return (new ComelusConnector(new Structure()))
            ->setActive(true)
            ->setApiKey("1234")
            ->setDescription("Ceci est une description")
            ->setMailingListId('azerty')
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
        $this->expectException(ComelusConnectorException::class);
        $this->expectExceptionMessage('length should be <= 255');
        $this->createDummyConnector()
            ->setUrl('https://' . $this->genString(255). '.fr');
    }

    public function testApiKeyTooLong()
    {
        $this->expectException(ComelusConnectorException::class);
        $this->expectExceptionMessage('length should be <= 255');
        $this->createDummyConnector()
            ->setApiKey($this->genString(260));
    }




}
