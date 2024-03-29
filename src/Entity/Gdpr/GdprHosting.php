<?php

namespace App\Entity\Gdpr;

use App\Repository\GdprHostingRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;

#[Entity(repositoryClass: GdprHostingRepository::class)]
class GdprHosting
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private $id;

    #[Column(type: 'string', length: 255)]
    #[Length(max: '255')]
    private $companyName;

    #[Column(type: 'string', length: 512)]
    #[Length(max: '512')]
    private $address;

    #[Column(type: 'string', length: 255)]
    #[Length(max: '255')]
    private $representative;

    #[Column(type: 'string', length: 255)]
    #[Length(max: '255')]
    private $quality;

    #[Column(type: 'string', length: 255)]
    #[Length(max: '255')]
    private $siret;

    #[Column(type: 'string', length: 255)]
    #[Length(max: '255')]
    private $ape;

    #[Column(type: 'string', length: 255)]
    #[Length(max: '255')]
    private $companyPhone;

    #[Column(type: 'string', length: 255)]
    #[Length(max: '255')]
    #[Email]
    private $companyEmail;

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getCompanyName(): ?string
    {
        return $this->companyName;
    }

    public function setCompanyName(string $companyName): self
    {
        $this->companyName = $companyName;

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

    public function getCompanyPhone(): ?string
    {
        return $this->companyPhone;
    }

    public function setCompanyPhone(string $companyPhone): self
    {
        $this->companyPhone = $companyPhone;

        return $this;
    }

    public function getCompanyEmail(): ?string
    {
        return $this->companyEmail;
    }

    public function setCompanyEmail(string $companyEmail): self
    {
        $this->companyEmail = $companyEmail;

        return $this;
    }
}
