<?php

namespace Mithridatem\Validation\Attributes;

use Mithridatem\Validation\Contracts\PropertyConstraint;
use Mithridatem\Validation\Exception\ValidationException;
use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Positive implements PropertyConstraint
{
    public function validate(string $property, mixed $value): void
    {
        if ($value === null) {
            return;
        }

        if (!is_numeric($value)) {
            throw new ValidationException(
                sprintf('La propriété "%s" doit être un nombre.', $property)
            );
        }

        if ($value <= 0) {
            throw new ValidationException(
                sprintf('La propriété "%s" doit être un nombre positif.', $property)
            );
        }
    }
}
