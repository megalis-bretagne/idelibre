<?php


namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class OneAtMax extends Constraint
{
    public $message = 'Le text ne doit pas contenir de @';
}
