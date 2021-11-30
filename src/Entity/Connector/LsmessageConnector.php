<?php

namespace App\Entity\Connector;

use App\Entity\Connector\Exception\LsmessageConnectorException;
use App\Entity\Structure;
use App\Repository\Connector\LsmessageConnectorRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;

#[Entity(repositoryClass: LsmessageConnectorRepository::class)]
class LsmessageConnector extends Connector
{
    public const NAME = 'lsmessage';
    public const MAX_URL_LENGTH = 255;
    public const MAX_API_KEY_LENGTH = 255;
    public const MAX_CONTENT_LENGTH = 140;
    public const MAX_SENDER_LENGTH = 11;
    #[Id]
    #[GeneratedValue(strategy: 'UUID')]
    #[Column(type: 'guid')]
    protected $id;
    #[Column(type: 'string', length: 255)]
    protected $name = self::NAME;
    #[Column(type: 'json', options: ['jsonb' => true])]
    protected $fields = [
        'url' => null,
        'api_key' => null,
        'sender' => null,
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

    /**
     * @throws LsmessageConnectorException
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
     * @throws LsmessageConnectorException
     */
    public function setApiKey(?string $apiKey): self
    {
        $this->validateLength($apiKey, self::MAX_API_KEY_LENGTH);
        $this->fields['api_key'] = $apiKey;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->fields['content'];
    }

    /**
     * @throws LsmessageConnectorException
     */
    public function setContent(?string $content): self
    {
        $this->validateLength($content, self::MAX_CONTENT_LENGTH);
        $this->fields['content'] = $content;

        return $this;
    }

    public function getSender(): ?string
    {
        return $this->fields['sender'];
    }

    /**
     * @throws LsmessageConnectorException
     */
    public function setSender(?string $sender): self
    {
        $this->validateLength($sender, self::MAX_SENDER_LENGTH);
        $this->fields['sender'] = $sender;

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

    /**
     * @throws LsmessageConnectorException
     */
    private function validateLength(?string $string, int $length)
    {
        if (!$string) {
            return;
        }
        if (strlen($string) > $length) {
            throw new LsmessageConnectorException("length should be <= $length");
        }
    }
}
