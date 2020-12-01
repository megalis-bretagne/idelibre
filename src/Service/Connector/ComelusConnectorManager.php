<?php


namespace App\Service\Connector;


use App\Entity\Connector\ComelusConnector;
use App\Entity\Structure;
use Doctrine\ORM\EntityManagerInterface;

class ComelusConnectorManager
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function createConnector(Structure $structure): void
    {
        // TODO check if already exists
        $connector = new ComelusConnector($structure);
        $this->em->persist($connector);
        $this->em->flush();
    }


    public function save(ComelusConnector $comelusConnector): void
    {
        $this->em->persist($comelusConnector);
        $this->em->flush();
    }


}
