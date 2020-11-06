<?php


namespace App\Service\Convocation;

use App\Entity\Convocation;
use App\Entity\Sitting;
use App\Entity\Timestamp;
use App\Entity\User;
use App\Repository\ConvocationRepository;
use App\Service\Timestamp\TimestampManagerInterface;
use Doctrine\ORM\EntityManagerInterface;

class ConvocationManager
{
    private EntityManagerInterface $em;
    private TimestampManagerInterface $timestampManager;
    private ConvocationRepository $convocationRepository;


    public function __construct(EntityManagerInterface $em, TimestampManagerInterface $timestampManager, ConvocationRepository $convocationRepository)
    {
        $this->em = $em;
        $this->timestampManager = $timestampManager;
        $this->convocationRepository = $convocationRepository;
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
            if ($this->alreadyHasConvocation($actor, $sitting)) {
                continue;
            }
            $convocation = new Convocation();
            $convocation->setSitting($sitting)
                ->setActor($actor);
            $this->em->persist($convocation);
        }
        $this->em->flush();
    }


    /**
     * @param Convocation[] $convocations
     */
    public function removeConvocations(iterable $convocations)
    {
        foreach ($convocations as $convocation) {
            if ($this->isAlreadySent($convocation)) {
                continue;
            }
            $this->em->remove($convocation);
        }
        $this->em->flush();
    }




    private function alreadyHasConvocation(User $actor, Sitting $sitting): bool
    {
        $convocation = $this->convocationRepository->findOneBy(['actor' => $actor, 'sitting' => $sitting]);
        return !empty($convocation);
    }


    /**
     * @param Convocation[] $convocations
     */
    public function sendConvocations(iterable $convocations)
    {
        foreach ($convocations as $convocation) {
            $this->sendConvocation($convocation);
        }
        $this->em->flush();

        //Todo send email and notify clients
    }

    private function sendConvocation(Convocation $convocation)
    {
        if ($this->isAlreadySent($convocation)) {
            return;
        }
        $timeStamp = new Timestamp();
        $timeStamp->setContent("les infos de cet envoi");
        $this->timestampManager->signTimestamp($timeStamp);
        $this->em->persist($timeStamp);
        $convocation->setIsActive(true)
            ->setSentTimestamp($timeStamp);

        $this->em->persist($convocation);
    }


    private function isAlreadySent(Convocation $convocation)
    {
        return !!$convocation->getSentTimestamp();
    }
}
