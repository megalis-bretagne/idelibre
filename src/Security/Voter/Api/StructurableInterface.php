<?php

namespace App\Security\Voter\Api;

use App\Entity\Structure;

interface StructurableInterface
{
    public function getStructure() : Structure;
}
