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
    /**
     * @var mixed
     */
    private $baseUrl;

    public function __construct(ParameterBagInterface $bag, HttpClientInterface $httpClient)
    {
        $this->passphrase = $bag->get('nodejs_passphrase');
        $this->httpClient = $httpClient;
        $this->baseUrl = $bag->get('nodejs_notification_url');
    }

    /**
     * @param Convocation[] $convocations
     */
    public function newSittingNotification(array $convocations)
    {
        $userIds = [];
        foreach ($convocations as $convocation) {
            if ($convocation->getIsActive()) {
                $userIds[] = $convocation->getUser()->getId();
            }
        }
        $this->$this->sendNotification('/sittings/new', $userIds);
    }

    /**
     * @param string[] $userIds
     */
    public function sendNotification(string $path, array $userIds)
    {
        try {
            $this->httpClient->request('POST', "$this->baseUrl/$path", [
                'json' => [
                    'userIds' => $userIds,
                    'passphrase' => $this->passphrase,
                ],
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
