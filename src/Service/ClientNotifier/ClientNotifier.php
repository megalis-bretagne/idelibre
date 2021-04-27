<?php

namespace App\Service\ClientNotifier;

use App\Entity\Convocation;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ClientNotifier implements ClientNotifierInterface
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
        $this->sendNotification('/sittings/new', $this->getUserList($convocations));
    }


    public function modifiedSittingNotification(array $convocations)
    {
        $this->sendNotification('/sittings/modify', $this->getUserList($convocations));

    }

    public function removedSittingNotification(array $convocations)
    {
        $this->sendNotification('/sittings/modify', $this->getUserList($convocations));
    }


    /**
     * @param Convocation[] $convocations
     * @return string[]
     */
    private function getUserList(array $convocations): array
    {
        $userIds = [];
        foreach ($convocations as $convocation) {
            if ($convocation->getIsActive()) {
                $userIds[] = $convocation->getUser()->getId();
            }
        }

        return $userIds;
    }


    /**
     * @param string[] $userIds
     */
    private function sendNotification(string $path, array $userIds)
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

}
