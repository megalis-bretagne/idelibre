<?php

namespace App\Tests;

trait HasValidationError
{
    private function assertHasValidationErrors($file, int $number)
    {
        $errors = $this->validator->validate($file);

        $this->assertCount($number, $errors, $errors);
    }
}
