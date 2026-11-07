<?php

declare(strict_types=1);

namespace Mithridatem\Validation\Tests\Security;

use Attribute;
use JsonException;
use Mithridatem\Validation\Attributes\NotBlank;
use Mithridatem\Validation\Contracts\PropertyConstraint;
use Mithridatem\Validation\ValidationError;
use Mithridatem\Validation\Validator;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class CustomConstraintSecurityTest extends TestCase
{
    protected function setUp(): void
    {
        ConstructorProbeConstraint::$constructed = 0;
        ConstructorProbeConstraint::$validated = 0;
    }

    public function testCustomAttributeRunsCodeOnlyWhenValidationIsRequested(): void
    {
        $entity = new class {
            #[ConstructorProbeConstraint]
            private string $value = 'sample';
        };

        self::assertSame(0, ConstructorProbeConstraint::$constructed);
        self::assertSame([], (new Validator())->validate($entity));
        self::assertSame(1, ConstructorProbeConstraint::$constructed);
        self::assertSame(1, ConstructorProbeConstraint::$validated);
    }

    public function testCustomConstraintCanReadPrivatePropertyValue(): void
    {
        $entity = new class {
            #[EchoValueConstraint]
            private string $secret = 'example-secret-not-for-client';
        };

        $errors = (new Validator())->validate($entity);
        self::assertSame('example-secret-not-for-client', $errors['secret'][0]->parameters['raw']);
    }

    public function testCustomConstraintCanReturnMislabelledProperty(): void
    {
        $entity = new class {
            #[WrongPropertyConstraint]
            public string $field = 'x';
        };

        $errors = (new Validator())->validate($entity);
        self::assertArrayHasKey('field', $errors);
        self::assertSame('another-field', $errors['field'][0]->property);
    }

    public function testCustomConstraintCanReturnInvalidListElementWithoutValidatorRejectingIt(): void
    {
        $entity = new class {
            #[InvalidElementConstraint]
            public string $field = 'x';
        };

        $errors = (new Validator())->validate($entity);
        self::assertSame('not-an-error-object', $errors['field'][0]);
    }

    public function testCustomConstraintExceptionInterruptsValidation(): void
    {
        $entity = new class {
            #[ThrowsConstraint]
            public string $field = 'x';

            #[NotBlank]
            public ?string $second = null;
        };

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('application constraint failed');
        (new Validator())->validate($entity);
    }

    public function testJsonSerializationOfCustomMetadataIncludesSensitivePayload(): void
    {
        $entity = new class {
            #[EchoValueConstraint]
            public string $secret = 'example-secret-not-for-client';
        };

        $json = json_encode((new Validator())->validate($entity), JSON_THROW_ON_ERROR);
        self::assertStringContainsString('example-secret-not-for-client', $json);
    }

    public function testNonFiniteCustomMetadataThrowsWhenUsingJsonThrowOnError(): void
    {
        $error = new ValidationError('amount', 'Invalid amount', 'custom.amount', ['value' => INF]);

        $this->expectException(JsonException::class);
        json_encode($error, JSON_THROW_ON_ERROR);
    }

    public function testHtmlEscapingIsApplicationResponsibility(): void
    {
        $message = '<img src=x onerror=alert(1)>';
        $error = new ValidationError('field', $message, 'custom.invalid');

        // Raw error fields are not HTML-escaped by the value object.
        self::assertSame($message, $error->message);

        $escaped = htmlspecialchars($error->message, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        self::assertStringNotContainsString('<img', $escaped);
        self::assertStringContainsString('&lt;img', $escaped);
    }
}

#[Attribute(Attribute::TARGET_PROPERTY)]
final class ConstructorProbeConstraint implements PropertyConstraint
{
    public static int $constructed = 0;
    public static int $validated = 0;

    public function __construct()
    {
        ++self::$constructed;
    }

    public function validate(string $property, mixed $value): array
    {
        ++self::$validated;
        return [];
    }
}

#[Attribute(Attribute::TARGET_PROPERTY)]
final class EchoValueConstraint implements PropertyConstraint
{
    public function validate(string $property, mixed $value): array
    {
        return [new ValidationError($property, 'Not accepted', 'custom.echo', ['raw' => $value])];
    }
}

#[Attribute(Attribute::TARGET_PROPERTY)]
final class WrongPropertyConstraint implements PropertyConstraint
{
    public function validate(string $property, mixed $value): array
    {
        return [new ValidationError('another-field', 'Invalid', 'custom.wrong_property')];
    }
}

#[Attribute(Attribute::TARGET_PROPERTY)]
final class InvalidElementConstraint implements PropertyConstraint
{
    public function validate(string $property, mixed $value): array
    {
        // Deliberate contract violation: PHP's array return type does not validate its elements.
        return ['not-an-error-object'];
    }
}

#[Attribute(Attribute::TARGET_PROPERTY)]
final class ThrowsConstraint implements PropertyConstraint
{
    public function validate(string $property, mixed $value): array
    {
        throw new RuntimeException('application constraint failed');
    }
}
