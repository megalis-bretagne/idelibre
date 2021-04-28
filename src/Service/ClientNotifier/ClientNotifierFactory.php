<?php

namespace App\Service\ClientNotifier;

class ClientNotifierFactory
{
    private ClientNotifier $clientNotifier;
    private FakeClientNotifier $fakeClientNotifier;

    public function __construct(ClientNotifier $clientNotifier, FakeClientNotifier $fakeClientNotifier)
    {
        $this->clientNotifier = $clientNotifier;
        $this->fakeClientNotifier = $fakeClientNotifier;
    }

    public function chooseImplementation(): ClientNotifierInterface
    {
        if ('test' === getenv('APP_ENV')) {
            return $this->fakeClientNotifier;
        }

        return $this->clientNotifier;
    }
}
