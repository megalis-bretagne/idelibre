<?php

namespace App\Service\Connector\Lsvote;

use App\Service\Connector\Lsvote\Model\LsvoteEnveloppe;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Throwable;

class LsvoteClient
{
    public const CHECK = '/api/v1/me';
    public const API_SITTING_URI = '/api/v1/sittings';

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly SerializerInterface $serializer
    ) {
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
                ]
            );
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

            $content = json_decode($response->getContent(), true);

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
                Request::METHOD_DELETE,
                $url . "/" . self::API_SITTING_URI . "/" . $sittingId,
                ["headers" => [
                    "Authorization" => $apiKey
                ]]
            );
        } catch (TransportExceptionInterface $e) {
            throw new LsvoteException($e->getMessage());
        }

        return true;
    }

    /**
     * @throws LsvoteException
     * @throws LsvoteNotFoundException
     */
    public function reSendSitting(string $url, string $apiKey, string $sittingId, LsvoteEnveloppe $lsvoteSitting)
    {
        $serializedData = $this->serializer->serialize($lsvoteSitting, 'json');
        try {
            $response = $this->httpClient->request(
                Request::METHOD_PUT,
                $url . self::API_SITTING_URI . '/' . $sittingId,
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
            //            dd( json_decode($response->getContent(), true));
            $content = json_decode($response->getContent(), true);

            return $content['id'];
        } catch (ClientException $e) {
            if ($e->getCode() === 404) {
                throw new LsvoteNotFoundException($e->getMessage());
            }
        } catch (Throwable $e) {
            throw new LsvoteException($e->getMessage());
        }
    }


    /**
     * @throws LsvoteException
     */
    public function resultSitting(string $url, string $apiKey, string $sittingId): array
    {
        try {
            $response = $this->httpClient->request(
                "GET",
                $url . self::API_SITTING_URI . "/" . $sittingId . '/result',
                ["headers" => [
                    "Authorization" => $apiKey
                ],
                    "verify_peer" => false,
                    "verify_host" => false,
                ]
            );

            return json_decode($response->getContent(), true);
        } catch (Throwable $e) {
            if ($response?->getContent(false)) {
                throw new LsvoteException($response->getContent(false));
            }
            throw new LsvoteException($e->getMessage());
        }
    }
}
