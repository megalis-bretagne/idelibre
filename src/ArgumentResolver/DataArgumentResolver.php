<?php

namespace App\ArgumentResolver;

use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

class DataArgumentResolver implements ValueResolverInterface
{
    public function supports(Request $request, ArgumentMetadata $argument): bool
    {
        if (!$this->isPostOrPut($request) || !$this->isApiPath($request)) {
            return false;
        }

        return 'data' === $argument->getName() && 'array' === $argument->getType();
    }

    public function resolve(Request $request, ArgumentMetadata $argument): \Generator
    {

        if(!$this->supports($request, $argument)) {
            return [];
        }

        $data = json_decode($request->getContent(), true);

        if (json_last_error()) {
            throw new BadRequestException('malformed Json', 400);
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
