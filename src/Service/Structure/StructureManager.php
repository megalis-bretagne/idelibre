<?php

namespace App\Service\Structure;

use App\Entity\Structure;
use App\Service\Seance\SittingManager;
use App\Service\User\ImpersonateStructure;
use Doctrine\ORM\EntityManagerInterface;

class StructureManager
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly SittingManager $sittingManager,
        private readonly ImpersonateStructure $impersonateStructure
    ) {
    }

    public function save(Structure $structure): void
    {
        $this->em->persist($structure);
        $this->em->flush();
    }

    public function delete(Structure $structure): void
    {
        $this->impersonateStructure->logoutEverySuperAdmin($structure);
        $this->sittingManager->deleteByStructure($structure);
        $this->em->remove($structure);
        $this->em->flush();
    }

    public function replaceReplyTo(Structure $structure, ?string $replyTo): void
    {
        $structure->setReplyTo($replyTo);
        $this->em->persist($structure);
        $this->em->flush();
    }
}
