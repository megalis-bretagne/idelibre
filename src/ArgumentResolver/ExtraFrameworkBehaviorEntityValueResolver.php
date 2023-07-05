<?php

namespace App\ArgumentResolver;

use Symfony\Bridge\Doctrine\ArgumentResolver\EntityValueResolver;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Symfony\Component\DependencyInjection\Attribute\AutowireDecorated;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

#[AsDecorator('doctrine.orm.entity_value_resolver')]
class ExtraFrameworkBehaviorEntityValueResolver implements ValueResolverInterface
{
    public function __construct(
         private  readonly  EntityValueResolver $inner
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

/*
 TODO  in vendor/apy/breadcrumbtrail-bundle/src/Resources/config/services.xml

replace kernel.controller => kernel.controller_arguments

<service id="APY\BreadcrumbTrailBundle\EventListener\BreadcrumbListener">
            <tag name="kernel.event_listener" event="kernel.controller_arguments" method="onKernelController" priority="-1" />
 </service>


*/