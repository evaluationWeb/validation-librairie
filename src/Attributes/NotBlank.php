<?php

namespace Mithridatem\Validation\Attributes;

use Mithridatem\Validation\Contracts\PropertyConstraint;
use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class NotBlank implements PropertyConstraint
{
    /** @return list<string> */
    public function validate(string $property, mixed $value): array
    {
        if ($value === null) {
            return [sprintf(
                'La propriete "%s" ne peut pas etre nulle.',
                $property
            )];
        }

        if (is_string($value) && trim($value) === '') {
            return [sprintf(
                'La propriete "%s" ne peut pas etre vide.',
                $property
            )];
        }

        return [];
    }
}
