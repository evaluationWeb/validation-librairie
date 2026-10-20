<?php

namespace Mithridatem\Validation;

use Mithridatem\Validation\Contracts\PropertyConstraint;
use ReflectionAttribute;
use ReflectionClass;

class Validator
{
    /**
     * Inspect attributes on the given entity and run each constraint.
     *
     * @return array<string, list<ValidationError>> All validation errors grouped by property.
     */
    public function validate(object $entity): array
    {
        $errors = [];
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
                $violations = $constraint->validate($property->getName(), $value);

                if ($violations !== []) {
                    $name = $property->getName();
                    $errors[$name] = array_merge($errors[$name] ?? [], $violations);
                }
            }
        }

        return $errors;
    }
}
