<?php

namespace App\Entity;

use App\Repository\EmailTemplateRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass=EmailTemplateRepository::class)
 * @UniqueEntity("name")
 */
class EmailTemplate
{
    public const CONVOCATION = 'convocation';
    public const INVITATION = 'invitation';
    public const RESET_PASSWORD = 'reset_password';

    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(type="guid")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     */
    private $name;

    /**
     * @ORM\Column(type="text")
     */
    private $content;

    /**
     * @ORM\ManyToOne(targetEntity=Structure::class)
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $structure;

    /**
     * @ORM\OneToOne(targetEntity=Type::class,inversedBy="emailTemplate")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    private $type;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $subject;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isDefault = false;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $category = self::CONVOCATION;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isAttachment = false;


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

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getStructure(): ?Structure
    {
        return $this->structure;
    }

    public function setStructure(?Structure $structure): self
    {
        $this->structure = $structure;

        return $this;
    }

    public function getType(): ?Type
    {
        return $this->type;
    }

    public function setType(?Type $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getSubject(): ?string
    {
        return $this->subject;
    }

    public function setSubject(string $subject): self
    {
        $this->subject = $subject;

        return $this;
    }

    public function getIsDefault(): bool
    {
        return $this->isDefault;
    }

    public function setIsDefault(bool $isDefault): self
    {
        $this->isDefault = $isDefault;

        return $this;
    }

    public function getCategory(): string
    {
        return $this->category;
    }

    public function setCategory(string $category): self
    {
        $this->category = $category;

        return $this;
    }

    public function getIsAttachment(): bool
    {
        return $this->isAttachment;
    }

    public function setIsAttachment(bool $isAttachment): self
    {
        $this->isAttachment = $isAttachment;

        return $this;
    }
}
