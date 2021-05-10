<?php

namespace App\Service\ClientNotifier;

use App\Entity\Convocation;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ClientNotifier implements ClientNotifierInterface
{
    private string $passphrase;
    private HttpClientInterface $httpClient;
    private string $baseNotificationUrl;
    private LoggerInterface $logger;
    private string $nodejsUrl;

    public function __construct(ParameterBagInterface $bag, HttpClientInterface $httpClient, LoggerInterface $logger)
    {
        $this->passphrase = $bag->get('nodejs_passphrase');
        $this->httpClient = $httpClient;
        $this->baseNotificationUrl = $bag->get('nodejs_notification_url');
        $this->logger = $logger;
        $this->nodejsUrl = $bag->get('nodejs_host');
    }

    /**
     * @param Convocation[] $convocations
     */
    public function newSittingNotification(array $convocations)
    {
        $this->sendNotification('/sittings/new', $this->getConvocationActiveUserList($convocations));
    }

    public function modifiedSittingNotification(array $convocations)
    {
        $this->sendNotification('/sittings/modify', $this->getConvocationActiveUserList($convocations));
    }

    public function removedSittingNotification(array $convocations)
    {
        $this->sendNotification('/sittings/modify', $this->getUserList($convocations));
    }

    /**
     * @param Convocation[] $convocations
     *
     * @return string[]
     */
    private function getUserList(array $convocations): array
    {
        $userIds = [];
        foreach ($convocations as $convocation) {
            $userIds[] = $convocation->getUser()->getId();
        }

        return $userIds;
    }

    /**
     * @param Convocation[] $convocations
     *
     * @return string[]
     */
    private function getConvocationActiveUserList(array $convocations): array
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
            $this->httpClient->request('POST', "$this->baseNotificationUrl/$path", [
                'json' => [
                    'userIds' => $userIds,
                    'passphrase' => $this->passphrase,
                ],
            ]);
        } catch (TransportExceptionInterface $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function checkConnection(): bool
    {
        try {
            $this->httpClient->request('GET', "$this->nodejsUrl/version");
        } catch (TransportExceptionInterface | ClientException $e) {
            $this->logger->error($e);

            return false;
        }

        return true;
    }
}
