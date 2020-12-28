<?php

namespace App\Sidebar\Annotation;

/**
 * @Annotation
 */
class Sidebar
{
    public array $active = [];
    public bool $reset = false;
}
