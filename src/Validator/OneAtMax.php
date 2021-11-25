<?php

namespace App\Validator;

use Attribute;
use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
#[Attribute]
class OneAtMax extends Constraint
{
    public $message = 'Le text ne doit pas contenir de @';
}
