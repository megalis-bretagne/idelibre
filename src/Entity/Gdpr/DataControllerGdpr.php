<?php

namespace App\Entity\Gdpr;

use App\Entity\Structure;
use App\Repository\DataControllerGdprRepository;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\OneToOne;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;

#[Entity(repositoryClass: DataControllerGdprRepository::class)]
class DataControllerGdpr
{
    #[Id]
    #[GeneratedValue(strategy: 'UUID')]
    #[Column(type: 'uuid', unique: true)]
    private $id;

    #[Column(type: 'string', length: 255)]
    #[Length(max: '255')]
    private $name;

    #[Column(type: 'string', length: 512)]
    #[Length(max: '512')]
    private $address;

    #[Column(type: 'string', length: 255)]
    #[Length(max: '255')]
    private $siret;

    #[Column(type: 'string', length: 255)]
    #[Length(max: '255')]
    private $ape;

    #[Column(type: 'string', length: 255)]
    #[Length(max: '255')]
    private $phone;

    #[Column(type: 'string', length: 255)]
    #[Length(max: '255')]
    #[Email]
    private $email;

    #[Column(type: 'string', length: 255)]
    #[Length(max: '255')]
    private $representative;

    #[Column(type: 'string', length: 255)]
    #[Length(max: '255')]
    private $quality;

    #[Column(type: 'string', length: 255)]
    #[Length(max: '255')]
    private $dpoName;

    #[Column(type: 'string', length: 255)]
    #[Length(max: '255')]
    #[Email]
    private $dpoEmail;

    #[OneToOne(inversedBy: 'dataControllerGdpr', targetEntity: Structure::class, cascade: ['persist', 'remove'])]
    #[JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private $structure;

    public function getId(): ?string
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

    public function getStructure(): ?Structure
    {
        return $this->structure;
    }

    public function setStructure(Structure $structure): self
    {
        $this->structure = $structure;

        return $this;
    }
}
