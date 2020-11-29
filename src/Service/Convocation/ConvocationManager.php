<?php


namespace App\Service\Convocation;

use App\Entity\Convocation;
use App\Entity\Sitting;
use App\Entity\Timestamp;
use App\Entity\User;
use App\Repository\ConvocationRepository;
use App\Service\Timestamp\TimestampManager;
use App\Service\Timestamp\TimestampServiceInterface;
use Doctrine\ORM\EntityManagerInterface;

class ConvocationManager
{
    private EntityManagerInterface $em;
    private TimestampServiceInterface $timestampService;
    private ConvocationRepository $convocationRepository;
    private TimestampManager $timestampManager;


    public function __construct(EntityManagerInterface $em, TimestampServiceInterface $timestampService, ConvocationRepository $convocationRepository, TimestampManager $timestampManager)
    {
        $this->em = $em;
        $this->timestampService = $timestampService;
        $this->convocationRepository = $convocationRepository;
        $this->timestampManager = $timestampManager;
    }

    public function createConvocations(Sitting $sitting): void
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
    public function addConvocations(iterable $actors, Sitting $sitting): void
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
    public function deleteConvocationsNotSent(iterable $convocations): void
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
    public function sendConvocations(iterable $convocations): void
    {
        foreach ($convocations as $convocation) {
            $this->sendConvocation($convocation);
        }
        $this->em->flush();

        //Todo send email and notify clients
    }

    private function sendConvocation(Convocation $convocation): void
    {
        if ($this->isAlreadySent($convocation)) {
            return;
        }
        $timeStamp = new Timestamp();
        $timeStamp->setContent("les infos de cet envoi");
        $this->timestampService->signTimestamp($timeStamp);
        $this->em->persist($timeStamp);
        $convocation->setIsActive(true)
            ->setSentTimestamp($timeStamp);

        $this->em->persist($convocation);
    }


    private function isAlreadySent(Convocation $convocation): bool
    {
        return !!$convocation->getSentTimestamp();
    }

    /**
     * @param Convocation[] $convocations
     */
    public function deleteConvocations(iterable $convocations): void
    {
        foreach ($convocations as $convocation) {
            $this->em->remove($convocation);
            $this->deleteAssociatedTimestamp($convocation);
        }
    }

    private function deleteAssociatedTimestamp(Convocation $convocation): void
    {
        if ($convocation->getSentTimestamp()) {
            $this->timestampManager->delete($convocation->getSentTimestamp());
        }
        if ($convocation->getReceivedTimestamp()) {
            $this->timestampManager->delete($convocation->getReceivedTimestamp());
        }
    }

    /**
     * @param Convocation[] $convocations
     */
    public function deactivate(iterable $convocations): void
    {
        foreach ($convocations as $convocation) {
            $convocation->setIsActive(false);
            $this->em->persist($convocation);
        }
    }
}
