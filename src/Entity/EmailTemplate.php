<?php

namespace App\Entity;

use App\Repository\EmailTemplateRepository;
use App\Service\Email\EmailData;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\UniqueConstraint;
use Exception;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;


#[Entity(repositoryClass: EmailTemplateRepository::class)]
#[Table]
#[UniqueEntity(fields: ['name', 'structure'], message: "l'intitulé doit être unique", errorPath: 'name')]
#[UniqueConstraint(name: 'IDX_EMAIL_NAME_STRUCTURE', columns: ['name', 'structure_id'])]
class EmailTemplate
{
    public const CATEGORY_CONVOCATION = 'convocation';
    public const CATEGORY_INVITATION = 'invitation';

    #[Id]
    #[GeneratedValue(strategy: 'UUID')]
    #[Column(type: 'guid')]
    private $id;

    #[Column(type: 'string', length: 255)]
    #[NotBlank]
    #[Length(max: '255')]
    private $name;

    #[Column(type: 'text')]
    #[NotBlank]
    private $content;

    #[ManyToOne(targetEntity: Structure::class)]
    #[JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[NotNull]
    private $structure;

    #[OneToOne(inversedBy: 'emailTemplate', targetEntity: Type::class)]
    #[JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private $type;

    #[Column(type: 'string', length: 255)]
    #[NotBlank]
    #[Length(max: '255')]
    private $subject;

    #[Column(type: 'boolean')]
    private $isDefault = false;

    #[Column(type: 'string', length: 255)]
    private $category = self::CATEGORY_CONVOCATION;

    #[Column(type: 'boolean')]
    private $isAttachment = false;

    #[Column(type: 'string', length: 255)]
    private $format = EmailData::FORMAT_HTML;

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

    public function getFormat(): ?string
    {
        return $this->format;
    }

    /**
     * @throws Exception
     */
    public function setFormat(string $format): self
    {
        if (!in_array($format, [EmailData::FORMAT_HTML, EmailData::FORMAT_TEXT])) {
            throw new Exception('invalid format');
        }
        $this->format = $format;

        return $this;
    }
}
