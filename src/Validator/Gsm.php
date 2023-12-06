<?php

namespace App\Validator;

use Attribute;
use Symfony\Component\Validator\Constraint;

#[Attribute]
class Gsm extends Constraint
{
    public string $message = 'Le format du numéro de téléphone doit se présenter comme suit : 06XXXXXXXX ou 07XXXXXXXX';
}
