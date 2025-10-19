<?php

namespace Mithridatem\Validation\Contracts;

/**
 * Validate a property value attached to a given attribute-based constraint.
 */
interface PropertyConstraint
{
    /**
     * Validate the given value for the provided property name.
     *
     * @throws \Mithridatem\Validation\Exception\ValidationException When the value violates the constraint.
     */
    public function validate(string $property, mixed $value): void;
}
