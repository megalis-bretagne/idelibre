<?php

namespace App\Entity\Connector;

use App\Entity\Structure;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\DiscriminatorMap;
use Doctrine\ORM\Mapping\InheritanceType;

/**
 * @ORM\Entity()
 * @ORM\Table(name="connector")
 * @InheritanceType("SINGLE_TABLE")
 * @DiscriminatorMap({"comelus" = "ComelusConnector", "lsmessage" = "LsmessageConnector"})
 */
abstract class Connector implements ConnectorInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(type="guid")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $name;

    /**
     * @ORM\Column(type="json", options={"jsonb"=true})
     */
    protected $fields = [];

    /**
     * @ORM\ManyToOne(targetEntity=Structure::class)
     * @ORM\JoinColumn(nullable=false)
     */
    protected $structure;
}
