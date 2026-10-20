<?php

namespace Mithridatem\Validation\Contracts;

use Mithridatem\Validation\ValidationError;

/**
 * Validate a property value attached to a given attribute-based constraint.
 */
interface PropertyConstraint
{
    /**
     * Validate the given value for the provided property name.
     *
     * @return list<ValidationError> Validation errors, or an empty list when valid.
     */
    public function validate(string $property, mixed $value): array;
}
