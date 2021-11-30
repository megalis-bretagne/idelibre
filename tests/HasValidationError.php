<?php

namespace App\Tests;

trait HasValidationError
{
    private function assertHasValidationErrors(mixed $entity, int $number): void
    {
        $errors = $this->validator->validate($entity);

        $this->assertCount($number, $errors, $errors);
    }
}
