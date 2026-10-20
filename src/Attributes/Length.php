<?php

namespace Mithridatem\Validation\Attributes;

use Mithridatem\Validation\Contracts\PropertyConstraint;
use Mithridatem\Validation\ValidationError;
use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Length implements PropertyConstraint
{
    public function __construct(
        private readonly ?int $min = null,
        private readonly ?int $max = null
    ) {
        if ($this->min !== null && $this->min < 0) {
            throw new \InvalidArgumentException('Length minimum must be greater or equal to zero.');
        }

        if ($this->max !== null && $this->max < 0) {
            throw new \InvalidArgumentException('Length maximum must be greater or equal to zero.');
        }

        if ($this->min !== null && $this->max !== null && $this->min > $this->max) {
            throw new \InvalidArgumentException('Length minimum cannot be greater than maximum.');
        }
    }

    /** @return list<ValidationError> */
    public function validate(string $property, mixed $value): array
    {
        if ($value === null) {
            return [];
        }

        $length = mb_strlen((string) $value);

        if ($this->min !== null && $length < $this->min) {
            $message = sprintf(
                'La propriete "%s" doit contenir au moins %d caracteres.',
                $property,
                $this->min
            );

            return [new ValidationError(
                property: $property,
                message: $message,
                code: 'length.too_short',
                parameters: ['min' => $this->min],
            )];
        }

        if ($this->max !== null && $length > $this->max) {
            $message = sprintf(
                'La propriete "%s" doit contenir au plus %d caracteres.',
                $property,
                $this->max
            );

            return [new ValidationError(
                property: $property,
                message: $message,
                code: 'length.too_long',
                parameters: ['max' => $this->max],
            )];
        }

        return [];
    }
}
