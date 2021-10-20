<?php

namespace App\ArgumentResolver;

use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

class DataArgumentResolver implements ArgumentValueResolverInterface
{

    public function supports(Request $request, ArgumentMetadata $argument)
    {
        if(!$this->isPostOrPut($request) || !$this->isApiPath($request)) {
            return false;
        }

        return $argument->getName() === 'data' && $argument->getType() === 'array';
    }

    public function resolve(Request $request, ArgumentMetadata $argument)
    {
        $data = json_decode($request->getContent(), true);

        if(json_last_error()) {
            Throw new BadRequestException("malformed Json", 400);
        }

        yield $data;
    }


    private function isApiPath(Request $request): bool
    {
        return str_starts_with($request->getPathInfo(), '/api/v2/');
    }

    private function isPostOrPut(Request $request): bool
    {
        return in_array($request->getMethod(), ['POST', 'PUT']);
    }
}
