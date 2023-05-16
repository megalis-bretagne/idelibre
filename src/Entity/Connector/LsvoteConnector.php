<?php

namespace App\Entity\Connector;

use App\Entity\Connector\Exception\LsmessageConnectorException;
use App\Entity\Connector\Exception\LsvoteConnectorException;
use App\Entity\Structure;
use App\Repository\LsvoteConnectorRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Ramsey\Uuid\Doctrine\UuidGenerator;

#[ORM\Entity(repositoryClass: LsvoteConnectorRepository::class)]
class LsvoteConnector extends Connector
{
    public const NAME = 'lsvote';


    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    protected $id;
    #[Column(type: 'string', length: 255)]
    protected $name = self::NAME;
    #[Column(type: 'json', options: ['jsonb' => true])]
    protected $fields = [
        'url' => null,
        'api_key' => null,
        'content' => null,
        'active' => false,
    ];
    /**
     * @ORM\ManyToOne(targetEntity=Structure::class)
     * @ORM\JoinColumn(nullable=false)
     */
    #[ManyToOne(targetEntity: Structure::class)]
    #[JoinColumn(nullable: false, onDelete: 'CASCADE')]
    protected $structure;

    public function __construct(Structure $structure)
    {
        $this->structure = $structure;
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getStructure(): ?Structure
    {
        return $this->structure;
    }

    public function getUrl(): ?string
    {
        return $this->fields['url'];
    }

    public function setUrl(?string $url): self
    {
        $this->fields['url'] = $url;

        return $this;
    }

    public function getApiKey(): ?string
    {
        return $this->fields['api_key'];
    }

    public function setApiKey(?string $apiKey): self
    {
        $this->fields['api_key'] = $apiKey;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->fields['content'];
    }

    public function setContent(?string $content): self
    {
        $this->fields['content'] = $content;

        return $this;
    }

    public function getActive(): bool
    {
        return $this->fields['active'] ?? false;
    }

    public function setActive(bool $active = false): self
    {
        $this->fields['active'] = $active;

        return $this;
    }
}
