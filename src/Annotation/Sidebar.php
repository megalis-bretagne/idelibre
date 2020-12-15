<?php


namespace App\Annotation;

/**
 * @Annotation
 */
class Sidebar
{
    public array $active = [];
    public bool $reset = false;
}
