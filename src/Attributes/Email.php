<?php

namespace Mithridatem\Validation\Attributes;

use Mithridatem\Validation\Contracts\PropertyConstraint;
use Mithridatem\Validation\ValidationError;
use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Email implements PropertyConstraint
{
    /** @return list<ValidationError> */
    public function validate(string $property, mixed $value): array
    {
        if ($value === null) {
            return [];
        }

        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            return [new ValidationError(
                property: $property,
                message: sprintf(
                    'La propriete "%s" doit contenir une adresse email valide.',
                    $property
                ),
                code: 'email.invalid',
            )];
        }

        return [];
    }
}
