<?php


namespace App\Service\ClientEntity;


use Doctrine\Common\Collections\Collection;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

class ClientProject
{

    /**
     * @Assert\NotBlank(message="Un projet doit avoir un nom")
     * @Assert\Length(max="Le nom du projet ne doit pas excéder 500 carractères")
     */
    public ?string $name;
    public ?string $themeId;
    public ?string $rapporteurId;
    public ?string $linkedFile;
    public Collection $annexes;

}
