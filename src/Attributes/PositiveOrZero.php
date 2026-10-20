<?php

namespace Mithridatem\Validation\Attributes;

use Mithridatem\Validation\Contracts\PropertyConstraint;
use Mithridatem\Validation\ValidationError;
use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class PositiveOrZero implements PropertyConstraint
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
                code: 'positive_or_zero.not_numeric',
            )];
        }

        if ($value < 0) {
            return [new ValidationError(
                property: $property,
                message: sprintf('La propriété "%s" doit être un nombre positif ou égal à zéro.', $property),
                code: 'positive_or_zero.not_positive_or_zero',
            )];
        }

        return [];
    }
}
