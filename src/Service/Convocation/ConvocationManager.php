<?php


namespace App\Service\Convocation;

use App\Entity\Convocation;
use App\Entity\File;
use App\Entity\Sitting;
use App\Entity\Timestamp;
use App\Entity\User;
use App\Service\Timestamp\TimestampManagerInterface;
use Doctrine\ORM\EntityManagerInterface;

class ConvocationManager
{
    private EntityManagerInterface $em;
    private TimestampManagerInterface $timestampManager;


    public function __construct(EntityManagerInterface $em, TimestampManagerInterface $timestampManager)
    {
        $this->em = $em;
        $this->timestampManager = $timestampManager;
    }

    public function createConvocations(Sitting $sitting)
    {
        foreach ($sitting->getType()->getAssociatedUsers() as $actor) {
            $convocation = new Convocation();
            $convocation->setSitting($sitting)
                ->setActor($actor);
            $this->em->persist($convocation);
        }
    }

    /**
     * @param User[] $actors
     */
    public function addConvocations(iterable $actors, Sitting $sitting)
    {
        foreach ($actors as $actor) {
            $convocation = new Convocation();
            $convocation->setSitting($sitting)
                ->setActor($actor);
            $this->em->persist($convocation);
        }
        $this->em->flush();
    }


    public function sendConvocation(Convocation $convocation){
        $timeStamp = new Timestamp();
        $timeStamp->setContent("les infos de cet envoi");
        $this->timestampManager->signTimestamp($timeStamp);
        $this->em->persist($timeStamp);
        $convocation->setIsActive(true)
            ->setSentTimestamp($timeStamp);

        $this->em->persist($convocation);
        $this->em->flush();

        //Todo send email and notify clients
    }


}
