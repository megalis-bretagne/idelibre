<?php


namespace App\Service\ClientEntity;


use Symfony\Component\Validator\Constraints as Assert;

class ClientAnnex
{
    public ?string $linkedFile = null;
    /**
     * @Assert\NotBlank()
     */
    public ?int $rank;
}
