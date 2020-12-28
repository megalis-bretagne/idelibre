<?php

namespace App\Tests;

use ReflectionClass;

trait privateMethodTrait
{
    public function getPrivateMethod(string $className, string $name)
    {
        $class = new ReflectionClass($className);
        $method = $class->getMethod($name);
        $method->setAccessible(true);

        return $method;
    }
}
