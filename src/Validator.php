<?php

namespace Mithridatem\Validation;

use Mithridatem\Validation\Contracts\PropertyConstraint;
use Mithridatem\Validation\Exception\ValidationException;
use ReflectionAttribute;
use ReflectionClass;

class Validator
{
    /**
     * Inspect attributes on the given entity and run each constraint.
     *
     * @throws ValidationException If any constraint is violated.
     */
    public function validate(object $entity): void
    {
        $reflection = new ReflectionClass($entity);

        foreach ($reflection->getProperties() as $property) {
            $value = null;

            if ($property->isInitialized($entity)) {
                if (!$property->isPublic()) {
                    $property->setAccessible(true);
                }

                $value = $property->getValue($entity);
            }

            $attributes = $property->getAttributes(
                PropertyConstraint::class,
                ReflectionAttribute::IS_INSTANCEOF
            );

            foreach ($attributes as $attribute) {
                /** @var PropertyConstraint $constraint */
                $constraint = $attribute->newInstance();
                $constraint->validate($property->getName(), $value);
            }
        }
    }
}
