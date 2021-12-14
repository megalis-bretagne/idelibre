<?php

namespace App\Controller\Api32;

use App\Entity\Structure;
use App\Repository\StructureRepository;
use App\Security\Http403Exception;
use Exception;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Request;

class VerifyToken
{
    private StructureRepository $structureRepository;

    public function __construct(StructureRepository $structureRepository)
    {
        $this->structureRepository = $structureRepository;
    }

    public function validate(Request $request): Structure
    {
        $envId = getenv('STRUCTURE_ID');
        if (!$envId) {
            throw new Exception('STRUCTURE_ID env is not defined');
        }

        $envToken = getenv('STRUCTURE_TOKEN');
        if (!$envToken) {
            throw new Exception('STRUCTURE_TOKEN env is not defined');
        }

        $authorizationHeader = $request->headers->get('Authorization');
        if (!$authorizationHeader) {
            throw new BadRequestException('header Authorization is required');
        }

        if ($authorizationHeader != $envToken) {
            throw new Http403Exception('Wrong api key');
        }

        return $this->structureRepository->find($envId);
    }
}
