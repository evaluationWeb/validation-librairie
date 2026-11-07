<?php

declare(strict_types=1);

namespace Mithridatem\Validation\Tests\Security;

use Mithridatem\Validation\Attributes\Length;
use Mithridatem\Validation\Attributes\Negative;
use Mithridatem\Validation\Attributes\NegativeOrZero;
use Mithridatem\Validation\Attributes\Pattern;
use Mithridatem\Validation\Attributes\Positive;
use Mithridatem\Validation\Attributes\PositiveOrZero;
use Mithridatem\Validation\ValidationError;
use PHPUnit\Framework\TestCase;

final class AdversarialConstraintsTest extends TestCase
{
    public function testPatternReportsBacktrackingEngineFailureAsNoMatch(): void
    {
        // A local PCRE match limit makes this deterministic and inexpensive,
        // without changing global ini settings or consuming significant CPU.
        $constraint = new Pattern('/(*LIMIT_MATCH=100)^(a+)+$/');
        $errors = $constraint->validate('code', str_repeat('a', 70) . '!');

        self::assertSame(PREG_BACKTRACK_LIMIT_ERROR, preg_last_error());
        self::assertCount(1, $errors);
        self::assertSame('pattern.no_match', $errors[0]->code);
        self::assertSame(['pattern' => '/(*LIMIT_MATCH=100)^(a+)+$/'], $errors[0]->parameters);
    }

    public function testPatternWithUnicodeModifierReportsMalformedUtf8AsNoMatch(): void
    {
        $constraint = new Pattern('/^.+$/u');
        $errors = $constraint->validate('field', "\xC3\x28");

        self::assertSame(PREG_BAD_UTF8_ERROR, preg_last_error());
        self::assertCount(1, $errors);
        self::assertSame('pattern.no_match', $errors[0]->code);
    }

    public function testLengthOfArrayCurrentlyUsesPhpArrayToStringConversion(): void
    {
        $warnings = [];
        set_error_handler(static function (int $severity, string $message) use (&$warnings): bool {
            $warnings[] = [$severity, $message];
            return true;
        }, E_WARNING);
        try {
            $errors = (new Length(max: 4))->validate('name', ['unexpected']);
        } finally {
            restore_error_handler();
        }

        self::assertNotEmpty($warnings);
        self::assertSame(E_WARNING, $warnings[0][0]);
        self::assertStringContainsString('Array to string conversion', $warnings[0][1]);
        self::assertCount(1, $errors);
        self::assertSame('length.too_long', $errors[0]->code); // "Array" is 5 chars.
    }

    public function testLengthOfNonStringableObjectRaisesPhpError(): void
    {
        $this->expectException(\Error::class);
        (new Length(min: 1))->validate('name', new \stdClass());
    }

    public function testLengthCountsMultibyteCharactersRatherThanBytes(): void
    {
        self::assertSame([], (new Length(min: 2, max: 2))->validate('name', 'éà'));
        $errors = (new Length(max: 1))->validate('name', 'éà');
        self::assertSame('length.too_long', $errors[0]->code);
    }

    public function testPositiveAndNegativeAcceptMatchingInfiniteFloats(): void
    {
        self::assertSame([], (new Positive())->validate('amount', INF));
        self::assertSame([], (new PositiveOrZero())->validate('amount', INF));
        self::assertSame([], (new Negative())->validate('amount', -INF));
        self::assertSame([], (new NegativeOrZero())->validate('amount', -INF));
    }

    public function testAllNumericSignConstraintsCurrentlyAcceptNan(): void
    {
        // Every comparison to NAN is false, so sign rejection conditions
        // never trigger after is_numeric(NAN) succeeds.
        foreach ([new Positive(), new PositiveOrZero(), new Negative(), new NegativeOrZero()] as $constraint) {
            self::assertSame([], $constraint->validate('amount', NAN));
        }
    }

    public function testExponentNotationIsAcceptedByNumericConstraint(): void
    {
        self::assertSame([], (new Positive())->validate('amount', '1e3'));
        self::assertSame([], (new Positive())->validate('amount', '1e309'));
    }

    public function testBuiltinPatternErrorContainsRuleButNotSubmittedValue(): void
    {
        $input = '<script>alert(1)</script>';
        $errors = (new Pattern('/^[A-Z]+$/D'))->validate('name', $input);

        self::assertCount(1, $errors);
        self::assertInstanceOf(ValidationError::class, $errors[0]);
        self::assertStringNotContainsString($input, $errors[0]->message);
        self::assertSame(['pattern' => '/^[A-Z]+$/D'], $errors[0]->parameters);
    }
}
