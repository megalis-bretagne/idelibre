<?php


namespace App\Service\Convocation;

use App\Entity\Convocation;
use App\Entity\File;
use App\Entity\Sitting;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class ConvocationManager
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
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
}
