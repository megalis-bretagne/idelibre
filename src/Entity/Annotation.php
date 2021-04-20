<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Annotationv3
 *
 * @ORM\Table(name="annotationv3")
 * @ORM\Entity
 */
class Annotation
{
    /**
     * @var string
     *
     * @ORM\Column(name="id", type="guid", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="annotationv3_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="authorid", type="guid", nullable=false)
     */
    private $authorid;

    /**
     * @var string|null
     *
     * @ORM\Column(name="authorname", type="string", nullable=true)
     */
    private $authorname;

    /**
     * @var int|null
     *
     * @ORM\Column(name="page", type="integer", nullable=true)
     */
    private $page;

    /**
     * @var string|null
     *
     * @ORM\Column(name="rect", type="string", nullable=true)
     */
    private $rect;

    /**
     * @var string|null
     *
     * @ORM\Column(name="text", type="string", nullable=true)
     */
    private $text;

    /**
     * @var string|null
     *
     * @ORM\Column(name="projet_id", type="guid", nullable=true)
     */
    private $projetId;

    /**
     * @var string|null
     *
     * @ORM\Column(name="seance_id", type="guid", nullable=true)
     */
    private $seanceId;

    /**
     * @var string|null
     *
     * @ORM\Column(name="annexe_id", type="guid", nullable=true)
     */
    private $annexeId;

    /**
     * @var string|null
     *
     * @ORM\Column(name="shareduseridlist", type="string", nullable=true)
     */
    private $shareduseridlist;

    /**
     * @var int|null
     *
     * @ORM\Column(name="date", type="bigint", nullable=true)
     */
    private $date;


}
