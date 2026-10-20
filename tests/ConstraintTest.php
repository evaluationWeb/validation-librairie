<?php

namespace Mithridatem\Validation\Tests;

use Mithridatem\Validation\Attributes\Email;
use Mithridatem\Validation\Attributes\Length;
use Mithridatem\Validation\Attributes\Negative;
use Mithridatem\Validation\Attributes\NegativeOrZero;
use Mithridatem\Validation\Attributes\NotBlank;
use Mithridatem\Validation\Attributes\Pattern;
use Mithridatem\Validation\Attributes\Positive;
use Mithridatem\Validation\Attributes\PositiveOrZero;
use Mithridatem\Validation\Contracts\PropertyConstraint;
use Mithridatem\Validation\ValidationError;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ConstraintTest extends TestCase
{
    #[DataProvider('invalidValues')]
    public function testItReturnsStructuredErrors(
        PropertyConstraint $constraint,
        mixed $value,
        string $code,
        string $message,
        array $parameters = [],
    ): void {
        $errors = $constraint->validate('field', $value);

        $this->assertCount(1, $errors);
        $this->assertInstanceOf(ValidationError::class, $errors[0]);
        $this->assertSame('field', $errors[0]->property);
        $this->assertSame($code, $errors[0]->code);
        $this->assertSame($message, $errors[0]->message);
        $this->assertSame($parameters, $errors[0]->parameters);
    }

    public static function invalidValues(): iterable
    {
        yield 'null' => [new NotBlank(), null, 'not_blank.null', 'La propriete "field" ne peut pas etre nulle.'];
        yield 'blank' => [new NotBlank(), '  ', 'not_blank.blank', 'La propriete "field" ne peut pas etre vide.'];
        yield 'short' => [new Length(min: 3), 'ab', 'length.too_short', 'La propriete "field" doit contenir au moins 3 caracteres.', ['min' => 3]];
        yield 'long' => [new Length(max: 3), 'abcd', 'length.too_long', 'La propriete "field" doit contenir au plus 3 caracteres.', ['max' => 3]];
        yield 'email' => [new Email(), 'invalid', 'email.invalid', 'La propriete "field" doit contenir une adresse email valide.'];
        yield 'pattern type' => [new Pattern('/^a$/'), 123, 'pattern.invalid_type', 'La propriété "field" doit être une chaîne de caractères pour être validée par une expression régulière.'];
        yield 'pattern match' => [new Pattern('/^a$/'), 'b', 'pattern.no_match', 'La propriété "field" doit correspondre au pattern : /^a$/.', ['pattern' => '/^a$/']];

        $numeric = [
            'negative' => [new Negative(), 'not_negative', 'négatif'],
            'negative_or_zero' => [new NegativeOrZero(), 'not_negative_or_zero', 'négatif ou égal à zéro'],
            'positive' => [new Positive(), 'not_positive', 'positif'],
            'positive_or_zero' => [new PositiveOrZero(), 'not_positive_or_zero', 'positif ou égal à zéro'],
        ];

        foreach ($numeric as $prefix => [$constraint, $suffix, $description]) {
            yield $prefix . ' type' => [$constraint, 'abc', $prefix . '.not_numeric', 'La propriété "field" doit être un nombre.'];
            $value = str_starts_with($prefix, 'negative') ? 1 : -1;
            yield $prefix . ' sign' => [$constraint, $value, $prefix . '.' . $suffix, sprintf('La propriété "field" doit être un nombre %s.', $description)];
        }
    }

    #[DataProvider('validValues')]
    public function testItReturnsAnEmptyListForValidValues(PropertyConstraint $constraint, mixed $value): void
    {
        $this->assertSame([], $constraint->validate('field', $value));
    }

    public static function validValues(): iterable
    {
        yield 'required' => [new NotBlank(), 'value'];
        yield 'zero is not blank' => [new NotBlank(), 0];
        yield 'false is not blank' => [new NotBlank(), false];
        yield 'min boundary' => [new Length(min: 3, max: 5), 'abc'];
        yield 'max boundary' => [new Length(min: 3, max: 5), 'abcde'];
        yield 'unicode' => [new Length(min: 2, max: 2), 'éà'];
        yield 'email' => [new Email(), 'user@example.com'];
        yield 'pattern' => [new Pattern('/^a$/'), 'a'];
        yield 'negative' => [new Negative(), -1];
        yield 'negative zero' => [new NegativeOrZero(), 0];
        yield 'positive' => [new Positive(), 1];
        yield 'positive zero' => [new PositiveOrZero(), 0];
        yield 'numeric string' => [new Positive(), '1.5'];

        foreach ([new Length(min: 3), new Email(), new Pattern('/^a$/'), new Negative(), new NegativeOrZero(), new Positive(), new PositiveOrZero()] as $index => $constraint) {
            yield 'optional null ' . $index => [$constraint, null];
        }
    }
}
