<?php

namespace App\Sidebar\Annotation;

use Attribute;
/**
 * @Annotation
 */
#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
class Sidebar
{
    public array $active = [];
    public bool $reset = false;

    public function __construct(array $active = [], bool $reset = false)
    {
        $this->active = $active;
        $this->reset = $reset;
    }
}
