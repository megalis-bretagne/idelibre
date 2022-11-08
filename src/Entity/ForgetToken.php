<?php

namespace App\Entity;

use DateTime;
use DateTimeInterface;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\OneToOne;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use Symfony\Component\Validator\Constraints\Length;

#[Entity(repositoryClass: 'App\Repository\ForgetTokenRepository')]
class ForgetToken
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private $id;

    #[Column(type: 'string', length: 255)]
    #[Length(max: '255')]
    private $token;

    #[Column(type: 'datetime')]
    private $expireAt;

    #[OneToOne(targetEntity: User::class, cascade: ['persist'])]
    #[JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private $user;

    public function __construct(User $user)
    {
        $this->user = $user;
        $this->expireAt = new DateTime('+1 hour');
        $this->token = bin2hex(random_bytes(60));
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function getExpireAt(): ?DateTimeInterface
    {
        return $this->expireAt;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setToken(string $newToken)
    {
        $this->token = $newToken;
    }
}
