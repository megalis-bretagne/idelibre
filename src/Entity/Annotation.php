<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Annotationv3.
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
    private $authorid;   //userId  (faire aussi le author => User)


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
    private $projetId;  //ajouter  project => Project

    /**
     * @var string|null
     *
     * @ORM\Column(name="seance_id", type="guid", nullable=true)
     */
    private $seanceId;   //ça a l'air inutile pusiqu'on a forcement soit le projet sit l'annexe (sauf si on joue la convocation ??? à terster avec la 3.2)

    /**
     * @var string|null
     *
     * @ORM\Column(name="annexe_id", type="guid", nullable=true)
     */
    private $annexeId;  //ajouter annex => Annex

    /**
     * @var string|null
     *
     * @ORM\Column(name="shareduseridlist", type="string", nullable=true)
     */
    private $shareduseridlist; //Tres mauvaise idée faire une jointable recipients entre annotation et user !

    /**
     * @var int|null
     *
     * @ORM\Column(name="date", type="bigint", nullable=true)
     */
    private $date;  // ('la date est un big int pour le timestamp' 'est peut etre le moement de changer et on fera la conversion dans le dto ?)
}
