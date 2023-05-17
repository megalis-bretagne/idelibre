<?php

namespace App\Service\Connector\Lsvote;

use App\Entity\Sitting;
use App\Service\Connector\Lsvote\Model\LsvoteEnveloppe;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class LsvoteClient
{

    const CHECK = '/api/v1/me';
    const SITTING_DELETE = '/api/v1/sittings/';
    const SITTING_CREATE = '/api/v1/sittings';

    public function __construct(private readonly HttpClientInterface $httpClient, private readonly SerializerInterface $serializer)
    {
    }


    public function checkApiKey(string $url, string $apiKey): bool
    {
        try {
            $this->httpClient->request(
                "GET",
                $url . self::CHECK,
                [
                    "headers" => [
                        "Authorization" => $apiKey,


                    ],
                    "verify_peer" => false,
                    "verify_host" => false
                ]);
        } catch (TransportExceptionInterface $e) {
            //todo log error
            return false;
        }

        return true;

    }

    public function sendSitting(string $url, string $apiKey, LsvoteEnveloppe $lsvoteSitting)
    {
        $serializedData = $this->serializer->serialize($lsvoteSitting, 'json');
        $res = $this->httpClient->request(
            Request::METHOD_POST,
            $url . self::SITTING_CREATE,
            [
                "headers" => [
                    "Authorization" => $apiKey,
                    'Accept' => 'application/json',
                    "content-type" => "application/json",
                ],

                "verify_peer" => false,
                "verify_host" => false,

                "body" => $serializedData

            ]
        );
    }

    public function deleteSitting(string $url, string $apiKey, string $sitingId): bool
    {
        try {
            $this->httpClient->request(
                "DELETE",
                $url . "/" . self::SITTING_DELETE . $sitingId,
                ["headers" => [
                    "Authorization" => $apiKey
                ]]);
        } catch (TransportExceptionInterface) {
            return false;
        }

        return true;

    }


    /*   public function resultSitting(string $url, string $apiKey, string $sitingId): bool
       {
           try {
               $this->httpClient->request(
                   "GET",
                   $url . "/" . self::SITTING_DELETE . $sitingId,
                   ["headers" => [
                       "Authorization" => $apiKey
                   ]]);
           } catch (TransportExceptionInterface) {
               return false;
           }

           return true;

       }
   */
}