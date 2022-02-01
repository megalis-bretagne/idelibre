<?php

namespace App\Entity\Connector;

use App\Entity\Structure;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\CustomIdGenerator;
use Doctrine\ORM\Mapping\DiscriminatorMap;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\InheritanceType;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use Ramsey\Uuid\Doctrine\UuidGenerator;

#[Entity]
#[Table(name: 'connector')]
#[InheritanceType(value: 'SINGLE_TABLE')]
#[DiscriminatorMap(value: ['comelus' => 'ComelusConnector', 'lsmessage' => 'LsmessageConnector'])]
abstract class Connector implements ConnectorInterface
{
    #[Id]
    #[GeneratedValue(strategy: 'CUSTOM')]
    #[CustomIdGenerator(UuidGenerator::class)]
    #[Column(type: 'uuid', unique: true)]
    protected $id;

    #[Column(type: 'string', length: 255)]
    protected $name;

    #[Column(type: 'json', options: ['jsonb' => true])]
    protected $fields = [];

    #[ManyToOne(targetEntity: Structure::class)]
    #[JoinColumn(nullable: false, onDelete: 'CASCADE')]
    protected $structure;
}
