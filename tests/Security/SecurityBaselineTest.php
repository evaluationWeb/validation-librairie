<?php

declare(strict_types=1);

namespace Mithridatem\Validation\Tests\Security;

use Mithridatem\Validation\Attributes\Email;
use Mithridatem\Validation\Attributes\Length;
use Mithridatem\Validation\Attributes\Negative;
use Mithridatem\Validation\Attributes\NotBlank;
use Mithridatem\Validation\Attributes\Pattern;
use Mithridatem\Validation\Attributes\Positive;
use Mithridatem\Validation\ValidationError;
use Mithridatem\Validation\Validator;
use PHPUnit\Framework\TestCase;

/** Characterization only: no change to runtime validation semantics. */
final class SecurityBaselineTest extends TestCase
{
    public function testMissingTypedPropertyIsTreatedAsNullAndRejectedByNotBlank(): void
    {
        $entity = new class {
            #[NotBlank]
            private string $token;
        };

        $errors = (new Validator())->validate($entity);

        self::assertSame('not_blank.null', $errors['token'][0]->code);
        self::assertCount(1, $errors['token']);
    }

    public function testOptionalNullIsAcceptedByFormatConstraints(): void
    {
        $entity = new class {
            #[Email]
            public ?string $email = null;

            #[Pattern('/^[A-Z]+$/D')]
            public ?string $reference = null;

            #[Length(min: 3)]
            public ?string $name = null;
        };

        self::assertSame([], (new Validator())->validate($entity));
    }

    public function testNotBlankCombinedWithEmailRejectsMissingValue(): void
    {
        $entity = new class {
            #[NotBlank]
            #[Email]
            public ?string $email = null;
        };

        $errors = (new Validator())->validate($entity);
        self::assertSame(['email'], array_keys($errors));
        self::assertSame(['not_blank.null'], array_map(
            static fn (ValidationError $error): string => $error->code,
            $errors['email']
        ));
    }

    public function testInvalidEmailDoesNotEchoSubmittedValueInBuiltInErrorMessage(): void
    {
        $input = '<script>alert("test")</script>';
        $errors = (new Email())->validate('email', $input);

        self::assertCount(1, $errors);
        self::assertSame('email.invalid', $errors[0]->code);
        self::assertStringNotContainsString($input, $errors[0]->message);
        self::assertSame([], $errors[0]->parameters);
    }

    public function testPatternRejectsUnexpectedInputTypeWithoutMatching(): void
    {
        $errors = (new Pattern('/^[a-z]+$/D'))->validate('input', ['unexpected']);

        self::assertCount(1, $errors);
        self::assertSame('pattern.invalid_type', $errors[0]->code);
    }

    public function testMalformedPatternDefinitionFailsDuringConstruction(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new Pattern('/[unterminated/');
    }

    public function testNumericConstraintRejectsNonNumericInputs(): void
    {
        foreach ([[], new \stdClass(), '1 OR 1=1', 'not-a-number'] as $value) {
            $errors = (new Positive())->validate('amount', $value);
            self::assertCount(1, $errors);
            self::assertSame('positive.not_numeric', $errors[0]->code);
        }
    }

    public function testNumericStringAcceptanceIsExplicitlyCharacterized(): void
    {
        self::assertSame([], (new Positive())->validate('amount', '12.5'));
        self::assertSame([], (new Negative())->validate('amount', '-12.5'));
    }

    public function testValidatorCollectsMultipleErrorsAndDoesNotReusePriorResults(): void
    {
        $entity = new class {
            #[NotBlank]
            #[Length(min: 3)]
            public ?string $username = '';
        };
        $validator = new Validator();

        $errors = $validator->validate($entity);
        self::assertSame(
            ['not_blank.blank', 'length.too_short'],
            array_map(static fn (ValidationError $e): string => $e->code, $errors['username'])
        );

        $entity->username = 'valid';
        self::assertSame([], $validator->validate($entity));
    }

    public function testValidationErrorMetadataSerializesWithoutSubmittedValue(): void
    {
        $input = '<img src=x onerror=alert(1)>';
        $entity = new class {
            #[Length(max: 5)]
            public string $description = '';
        };
        $entity->description = $input;
        $errors = (new Validator())->validate($entity);

        $json = json_encode(['errors' => $errors], JSON_THROW_ON_ERROR);
        self::assertStringNotContainsString($input, $json);
        self::assertSame('length.too_long', $errors['description'][0]->code);
        self::assertSame(['max' => 5], $errors['description'][0]->parameters);
    }
}
