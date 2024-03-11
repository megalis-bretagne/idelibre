<?php

namespace App\Entity\Connector;

use App\Entity\Connector\Exception\ComelusConnectorException;
use App\Entity\Structure;
use App\Repository\Connector\ComelusConnectorRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Ramsey\Uuid\Doctrine\UuidGenerator;

#[Entity(repositoryClass: ComelusConnectorRepository::class)]
class ComelusConnector extends Connector
{
    public const NAME = 'comelus';
    public const MAX_URL_LENGTH = 255;
    public const MAX_API_KEY_LENGTH = 255;
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
        'description' => null,
        'mailing_list_id' => null,
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

    /**
     * @throws ComelusConnectorException
     */
    public function setUrl(?string $url): self
    {
        $this->validateLength($url, self::MAX_URL_LENGTH);
        $this->fields['url'] = $url;

        return $this;
    }

    public function getApiKey(): ?string
    {
        return $this->fields['api_key'];
    }

    /**
     * @throws ComelusConnectorException
     */
    public function setApiKey(?string $apiKey): self
    {
        $this->validateLength($apiKey, self::MAX_API_KEY_LENGTH);
        $this->fields['api_key'] = $apiKey;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->fields['description'];
    }

    public function setDescription(?string $description): self
    {
        $this->fields['description'] = $description;

        return $this;
    }

    public function getMailingListId(): ?string
    {
        return $this->fields['mailing_list_id'];
    }

    public function setMailingListId(?string $mailingListId): self
    {
        $this->fields['mailing_list_id'] = $mailingListId;

        return $this;
    }

    public function getActive(): bool
    {
        return $this->fields['active'];
    }

    public function setActive(bool $active = false): self
    {
        $this->fields['active'] = $active;

        return $this;
    }

    /**
     * @throws ComelusConnectorException
     */
    private function validateLength(?string $string, int $length): void
    {
        if (!$string) {
            return;
        }
        if (strlen($string) > $length) {
            throw new ComelusConnectorException("length should be <= $length");
        }
    }
}
