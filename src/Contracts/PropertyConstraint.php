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
     * @return list<string> Error messages, or an empty list when valid.
     */
    public function validate(string $property, mixed $value): array;
}
