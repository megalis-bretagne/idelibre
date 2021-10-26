<?php

namespace App\Service\ClientNotifier;

class ClientNotifierFactory
{
    public function __construct(
        private ClientNotifier $clientNotifier,
        private FakeClientNotifier $fakeClientNotifier
    ) {
    }

    public function chooseImplementation(): ClientNotifierInterface
    {
        if ('test' === getenv('APP_ENV')) {
            return $this->fakeClientNotifier;
        }

        return $this->clientNotifier;
    }
}
