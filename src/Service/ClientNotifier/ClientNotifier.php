<?php

namespace App\Service\ClientNotifier;

use App\Entity\Convocation;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ClientNotifier /*implements ClientNotifierInterface*/
{
    private string $passphrase;
    private HttpClientInterface $httpClient;

    public function __construct(ParameterBagInterface $bag, HttpClientInterface $httpClient)
    {
        $this->passphrase = $bag->get('nodejs_passphrase');
        $this->httpClient = $httpClient;
    }


    /**
     * @param Convocation[] $convocations
     */
    public function newSittingNotification(array $convocations) {
        $userIds = [];
        foreach ($convocations as $convocation) {
            if($convocation->getIsActive()) {
                $userIds[] = $convocation->getUser()->getId();
            }
        }
        $this->$this->sendNotification("http://node-idelibre:3000/0.2.0/notification/sittings/new");

    }


    /**
     * @param string $url
     * @param string[] $userIds
     */
    public function sendNotification(string $url, array $userIds)
    {
        try {
            $this->httpClient->request('POST', $url, [
                'json' => [
                    'userIds' => $userIds,
                    'passphrase' => '$this->passphrase'
                ]
            ]);
        } catch (TransportExceptionInterface $e) {
            dump($e);
        }
    }

    public function modifiedSittingNotification(string $userId)
    {
        try {
            $this->httpClient->request('POST', 'http://node-idelibre:3000/sittings/modify/' . $userId . '/' . $this->passphrase);
        } catch (TransportExceptionInterface $e) {
            dump($e);
        }
    }

    public function removedSittingNotification(string $userId)
    {
        try {
            $this->httpClient->request('POST', 'http://node-idelibre:3000/sittings/removed/' . $userId . '/' . $this->passphrase);
        } catch (TransportExceptionInterface $e) {
            dump($e);
        }
    }
}
