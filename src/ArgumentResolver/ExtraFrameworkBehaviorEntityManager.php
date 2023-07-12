<?php

namespace App\ArgumentResolver;

use Symfony\Bridge\Doctrine\ArgumentResolver\EntityValueResolver;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Symfony\Component\DependencyInjection\Attribute\AutowireDecorated;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

#[AsDecorator('doctrine.orm.entity_value_resolver')]
class ExtraFrameworkBehaviorEntityManager implements ValueResolverInterface
{
    public function __construct(
        #[AutowireDecorated] private readonly EntityValueResolver $inner
    ) {
    }

    public function resolve(Request $request, ArgumentMetadata $argument): array
    {

        $object = $this->inner->resolve($request, $argument);

        if (!empty($object)) {
            $request->attributes->set($argument->getName(), $object[0]);
        }

        return $object;
    }
}
