<?php

namespace App\ArgumentResolver;


namespace App\ArgumentResolver;

use Closure;
use Psr\Log\LoggerInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\DependencyInjection\EnvVarProcessorInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class VaultEnvResolver implements EnvVarProcessorInterface
{
    public const VAULT_URL = 'http://vault-server:8200';
    public const VAULT_TOKEN = 'hvs.fFrLukTXVJ6Ae4LdTTREF0jY';


    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly LoggerInterface     $logger,
        private readonly string              $vaultUri = self::VAULT_URL,
        private readonly string              $vaultToken = self::VAULT_TOKEN,
        private readonly FilesystemAdapter   $filesystemAdapter = new FilesystemAdapter()
    )
    {
    }


    public function getEnv(string $prefix, string $name, Closure $getEnv): mixed
    {
        $nameValue = $getEnv($name);
        $params = explode(':', $nameValue);

        return $this->getValue(...$params);
    }

    public static function getProvidedTypes(): array
    {
        return ['vault' => 'string'];
    }

    private function getValue(string $secretKV, string $key)
    {
        $options = [
            'headers' => [
                'X-Vault-Token' => $this->vaultToken
            ],
        ];



        return  $this->filesystemAdapter->get($key, function (ItemInterface $item) use ($options, $secretKV, $key): ?string {
            dump('into the closure');
            $item->expiresAfter(1);
            try {
                $res = $this->httpClient->request(
                    'GET',
                    $this->vaultUri . '/v1/kv/data/' . $secretKV,
                    $options
                );

                $data = $res->toArray()['data'];
            } catch (TransportExceptionInterface|ClientExceptionInterface|DecodingExceptionInterface|RedirectionExceptionInterface|ServerExceptionInterface $e) {
                $this->logger->critical($e->getMessage());
                dd($e->getMessage());
                return null;
            }


            $values = $data['data'] ?? [];


            return $values[$key] ?? null;
        });
    }
}