<?php


namespace App\Service\Connector;


use App\Entity\Connector\ComelusConnector;
use App\Entity\Connector\LsmessageConnector;
use App\Entity\Structure;
use Doctrine\ORM\EntityManagerInterface;

class LsmessageConnectorManager
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function createConnector(Structure $structure): void
    {
        // TODO check if already exists
        $connector = new LsmessageConnector($structure);
        $this->em->persist($connector);
        $this->em->flush();
    }


    public function save(LsmessageConnector $lsmessageConnector): void
    {
        $this->em->persist($lsmessageConnector);
        $this->em->flush();
    }


}
