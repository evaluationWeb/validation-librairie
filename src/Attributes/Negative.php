<?php

namespace Mithridatem\Validation\Attributes;

use Mithridatem\Validation\Contracts\PropertyConstraint;
use Mithridatem\Validation\ValidationError;
use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Negative implements PropertyConstraint
{
    /** @return list<ValidationError> */
    public function validate(string $property, mixed $value): array
    {
        if ($value === null) {
            return [];
        }

        if (!is_numeric($value)) {
            return [new ValidationError(
                property: $property,
                message: sprintf('La propriété "%s" doit être un nombre.', $property),
                code: 'negative.not_numeric',
            )];
        }

        if ($value >= 0) {
            return [new ValidationError(
                property: $property,
                message: sprintf('La propriété "%s" doit être un nombre négatif.', $property),
                code: 'negative.not_negative',
            )];
        }

        return [];
    }
}
