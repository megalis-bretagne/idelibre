<?php


namespace App\Entity\Connector;

interface ConnectorInterface
{
    public function getActive(): bool;
}
