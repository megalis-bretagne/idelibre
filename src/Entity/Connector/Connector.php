<?php

namespace App\Entity\Connector;

use App\Entity\Structure;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\DiscriminatorMap;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\InheritanceType;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use Ramsey\Uuid\Doctrine\UuidGenerator;

#[Entity]
#[Table(name: 'connector')]
#[InheritanceType(value: 'SINGLE_TABLE')]
#[DiscriminatorMap(value: [
    'comelus' => 'ComelusConnector',
    'lsmessage' => 'LsmessageConnector',
    'lsvote' => 'LsvoteConnector'
])]
abstract class Connector implements ConnectorInterface
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    protected $id;
    #[Column(type: 'string', length: 255)]
    protected $name;
    #[Column(type: 'json', options: ['jsonb' => true])]
    protected $fields = [];
    #[ManyToOne(targetEntity: Structure::class)]
    #[JoinColumn(nullable: false, onDelete: 'CASCADE')]
    protected $structure;
}
