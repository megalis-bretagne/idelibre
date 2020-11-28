<?php


namespace App\Service\Timestamp;

use App\Entity\Timestamp;
use Doctrine\ORM\EntityManagerInterface;

class TimestampManager
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function delete(Timestamp $timestamp)
    {
        $this->em->remove($timestamp);
    }
}
