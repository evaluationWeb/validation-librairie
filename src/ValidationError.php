<?php

namespace Mithridatem\Validation;

/**
 * An immutable validation failure, independent of its display message.
 */
final readonly class ValidationError
{
    /** @param array<string, mixed> $parameters Constraint metadata, such as a length bound. */
    public function __construct(
        public string $property,
        public string $message,
        public string $code,
        public array $parameters = [],
    ) {
    }
}
