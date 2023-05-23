<?php

namespace App\Service\Connector\Lsvote;

use App\Service\Connector\Lsvote\Model\LsvoteEnveloppe;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Throwable;

class LsvoteClient
{

    const CHECK = '/api/v1/me';
    const API_SITTING_URI = '/api/v1/sittings/';

    public function __construct(private readonly HttpClientInterface $httpClient, private readonly SerializerInterface $serializer)
    {
    }


    /**
     * @throws LsvoteException
     */
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
            throw new LsvoteException($e);
        }

        return true;
    }

    /**
     * @throws LsvoteException
     */
    public function sendSitting(string $url, string $apiKey, LsvoteEnveloppe $lsvoteSitting)
    {
        $serializedData = $this->serializer->serialize($lsvoteSitting, 'json');
        try {
            $response = $this->httpClient->request(
                Request::METHOD_POST,
                $url . self::API_SITTING_URI,
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

            $content = json_decode($response->getContent());
            return $content['id'];

        } catch (Throwable $e) {
            throw new LsvoteException($e);
        }
    }

    /**
     * @throws LsvoteException
     */
    public function deleteSitting(string $url, string $apiKey, string $sittingId): bool
    {
        try {
            $this->httpClient->request(
                "DELETE",
                $url . "/" . self::API_SITTING_URI . $sittingId,
                ["headers" => [
                    "Authorization" => $apiKey
                ]]);
        } catch (TransportExceptionInterface $e) {
            throw new LsvoteException($e);
        }

        return true;

    }

    public function resultSitting(string $url, string $apiKey, string $sittingId): bool
    {
       try {
           $this->httpClient->request(
               "GET",
               $url . '/' . self::API_SITTING_URI . $sittingId . '/result',
               ["headers" => [
                   "Authorization" => $apiKey
               ]]);
       } catch (TransportExceptionInterface) {
           return false;
       }

       return true;

    }
}