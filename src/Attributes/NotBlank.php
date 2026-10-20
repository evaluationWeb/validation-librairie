<?php

namespace Mithridatem\Validation\Attributes;

use Mithridatem\Validation\Contracts\PropertyConstraint;
use Mithridatem\Validation\ValidationError;
use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class NotBlank implements PropertyConstraint
{
    /** @return list<ValidationError> */
    public function validate(string $property, mixed $value): array
    {
        if ($value === null) {
            return [new ValidationError(
                property: $property,
                message: sprintf(
                    'La propriete "%s" ne peut pas etre nulle.',
                    $property
                ),
                code: 'not_blank.null',
            )];
        }

        if (is_string($value) && trim($value) === '') {
            return [new ValidationError(
                property: $property,
                message: sprintf(
                    'La propriete "%s" ne peut pas etre vide.',
                    $property
                ),
                code: 'not_blank.blank',
            )];
        }

        return [];
    }
}
