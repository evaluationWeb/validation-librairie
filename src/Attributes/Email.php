<?php

namespace Mithridatem\Validation\Attributes;

use Mithridatem\Validation\Contracts\PropertyConstraint;
use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Email implements PropertyConstraint
{
    /** @return list<string> */
    public function validate(string $property, mixed $value): array
    {
        if ($value === null) {
            return [];
        }

        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            return [sprintf(
                'La propriete "%s" doit contenir une adresse email valide.',
                $property
            )];
        }

        return [];
    }
}
