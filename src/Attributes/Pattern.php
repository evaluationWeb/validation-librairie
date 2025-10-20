<?php

namespace Mithridatem\Validation\Attributes;

use Attribute;
use Mithridatem\Validation\Contracts\PropertyConstraint;
use Mithridatem\Validation\Exception\ValidationException;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Pattern implements PropertyConstraint
{
    public function __construct(
        private readonly string $pattern,
    ) {
        if (trim($this->pattern) === '') {
            throw new \InvalidArgumentException('The pattern provided must not be empty.');
        }

        if (@preg_match($this->pattern, '') === false) {
            throw new \InvalidArgumentException(sprintf(
                'The pattern "%s" is not a valid regular expression.',
                $this->pattern
            ));
        }
    }

    public function validate(string $property, mixed $value): void
    {
        if ($value === null) {
            return;
        }

        if (!is_string($value)) {
            throw new ValidationException(sprintf(
                'La propriété "%s" doit être une chaîne de caractères pour être validée par une expression régulière.',
                $property
            ));
        }

        if (!preg_match($this->pattern, $value)) {
            throw new ValidationException(sprintf(
                'La propriété "%s" doit correspondre au pattern : %s.',
                $property,
                $this->pattern
            ));
        }
    }
}
