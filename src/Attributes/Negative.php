<?php

namespace Mithridatem\Validation\Attributes;

use Mithridatem\Validation\Contracts\PropertyConstraint;
use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Negative implements PropertyConstraint
{
    /** @return list<string> */
    public function validate(string $property, mixed $value): array
    {
        if ($value === null) {
            return [];
        }

        if (!is_numeric($value)) {
            return [
                sprintf('La propriété "%s" doit être un nombre.', $property)
            ];
        }

        if ($value >= 0) {
            return [
                sprintf('La propriété "%s" doit être un nombre négatif.', $property)
            ];
        }

        return [];
    }
}
