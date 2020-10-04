<?php


namespace App\Service\Party;


use App\Entity\Party;
use App\Entity\Structure;
use Doctrine\ORM\EntityManagerInterface;

class PartyManager
{

    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function save(Party $party, Structure $structure)
    {
        $party->setStructure($structure);
        $this->em->persist($party);
        $this->em->flush();
    }
}
