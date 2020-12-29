<?php

namespace App\Tests;

trait StringTrait
{
    public function genString(int $length): string
    {
        return str_repeat('a', $length);
    }
}
