<?php

namespace App\Entity\Gdpr;

use App\Repository\DataControllerGdprRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DataControllerGdprRepository::class)]
class DataControllerGdpr
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    private $name;

    #[ORM\Column(type: 'string', length: 255)]
    private $address;

    #[ORM\Column(type: 'string', length: 255)]
    private $siret;

    #[ORM\Column(type: 'string', length: 255)]
    private $ape;

    #[ORM\Column(type: 'string', length: 255)]
    private $phone;

    #[ORM\Column(type: 'string', length: 255)]
    private $email;

    #[ORM\Column(type: 'string', length: 255)]
    private $representative;

    #[ORM\Column(type: 'string', length: 255)]
    private $quality;

    #[ORM\Column(type: 'string', length: 255)]
    private $dpoName;

    #[ORM\Column(type: 'string', length: 255)]
    private $dpoEmail;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getSiret(): ?string
    {
        return $this->siret;
    }

    public function setSiret(string $siret): self
    {
        $this->siret = $siret;

        return $this;
    }

    public function getApe(): ?string
    {
        return $this->ape;
    }

    public function setApe(string $ape): self
    {
        $this->ape = $ape;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getRepresentative(): ?string
    {
        return $this->representative;
    }

    public function setRepresentative(string $representative): self
    {
        $this->representative = $representative;

        return $this;
    }

    public function getQuality(): ?string
    {
        return $this->quality;
    }

    public function setQuality(string $quality): self
    {
        $this->quality = $quality;

        return $this;
    }

    public function getDpoName(): ?string
    {
        return $this->dpoName;
    }

    public function setDpoName(string $dpoName): self
    {
        $this->dpoName = $dpoName;

        return $this;
    }

    public function getDpoEmail(): ?string
    {
        return $this->dpoEmail;
    }

    public function setDpoEmail(string $dpoEmail): self
    {
        $this->dpoEmail = $dpoEmail;

        return $this;
    }
}
