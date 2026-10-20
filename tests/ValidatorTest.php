<?php

namespace Mithridatem\Validation\Tests;

use Mithridatem\Validation\Tests\Fixtures\User;
use Mithridatem\Validation\Validator;
use Mithridatem\Validation\ValidationError;
use PHPUnit\Framework\TestCase;

class ValidatorTest extends TestCase
{
    private Validator $validator;

    protected function setUp(): void
    {
        $this->validator = new Validator();
    }

    public function testItPassesWhenAllConstraintsAreSatisfied(): void
    {
        $user = $this->createValidUser();

        $this->assertSame([], $this->validator->validate($user));
    }

    public function testItFailsWhenNotBlankConstraintIsViolated(): void
    {
        $user = new User();
        $user->setFirstname(null);

        $errors = $this->validator->validate($user);

        $this->assertSame(['firstname'], array_keys($errors));
        $this->assertCount(1, $errors['firstname']);
    }

    public function testItFailsWhenLengthConstraintMinimumIsViolated(): void
    {
        $user = $this->createValidUser();
        $user->setFirstname('Jo');

        $errors = $this->validator->validate($user);

        $this->assertSame(['firstname'], array_keys($errors));
        $this->assertCount(1, $errors['firstname']);
    }

    public function testItFailsWhenEmailConstraintIsViolated(): void
    {
        $user = $this->createValidUser();
        $user->setEmail('invalid-email');

        $errors = $this->validator->validate($user);

        $this->assertSame(['email'], array_keys($errors));
        $this->assertCount(1, $errors['email']);
    }

    public function testItPassesWhenPatternConstraintIsSatisfied(): void
    {
        $user = $this->createValidUser();
        $user->setPattern('User123');

        $this->assertSame([], $this->validator->validate($user));
    }

    public function testItFailsWhenPatternConstraintIsViolated(): void
    {
        $user = $this->createValidUser();
        $user->setPattern('User_123');

        $errors = $this->validator->validate($user);

        $this->assertSame(['pattern'], array_keys($errors));
        $this->assertCount(1, $errors['pattern']);
    }

    public function testItAllowsNullWhenPatternConstraintIsSet(): void
    {
        $user = $this->createValidUser();
        $user->setPattern(null);

        $this->assertSame([], $this->validator->validate($user));
    }

    public function testItPassesWhenNegativeConstraintIsSatisfied(): void
    {
        $user = $this->createValidUser();
        $user->setNegative(-10);

        $this->assertSame([], $this->validator->validate($user));
    }

    public function testItFailsWhenNegativeConstraintIsViolated(): void
    {
        $user = $this->createValidUser();
        $user->setNegative(5);

        $errors = $this->validator->validate($user);

        $this->assertSame(['negative'], array_keys($errors));
        $this->assertCount(1, $errors['negative']);
        $this->assertStringContainsString('doit être un nombre négatif', $errors['negative'][0]->message);
    }

    public function testItAllowsNullWhenNegativeConstraintIsSet(): void
    {
        $user = $this->createValidUser();
        $user->setNegative(null);

        $this->assertSame([], $this->validator->validate($user));
    }

    public function testItPassesWhenNegativeOrZeroIsNegative(): void
    {
        $user = $this->createValidUser();
        $user->setNegativeOrZero(-5);

        $this->assertSame([], $this->validator->validate($user));
    }

    public function testItPassesWhenNegativeOrZeroIsZero(): void
    {
        $user = $this->createValidUser();
        $user->setNegativeOrZero(0);

        $this->assertSame([], $this->validator->validate($user));
    }

    public function testItFailsWhenNegativeOrZeroIsPositive(): void
    {
        $user = $this->createValidUser();
        $user->setNegativeOrZero(3);

        $errors = $this->validator->validate($user);

        $this->assertSame(['negativeOrZero'], array_keys($errors));
        $this->assertCount(1, $errors['negativeOrZero']);
        $this->assertStringContainsString('doit être un nombre négatif ou égal à zéro', $errors['negativeOrZero'][0]->message);
    }

    public function testItAllowsNullWhenNegativeOrZeroIsSet(): void
    {
        $user = $this->createValidUser();
        $user->setNegativeOrZero(null);

        $this->assertSame([], $this->validator->validate($user));
    }


    public function testItPassesWhenPositiveConstraintIsSatisfied(): void
    {
        $user = $this->createValidUser();
        $user->setPositive(10);

        $this->assertSame([], $this->validator->validate($user));
    }

    public function testItFailsWhenPositiveConstraintIsViolated(): void
    {
        $user = $this->createValidUser();
        $user->setPositive(-5);

        $errors = $this->validator->validate($user);

        $this->assertSame(['positive'], array_keys($errors));
        $this->assertCount(1, $errors['positive']);
        $this->assertStringContainsString('doit être un nombre positif', $errors['positive'][0]->message);
    }

    public function testItAllowsNullWhenPositiveConstraintIsSet(): void
    {
        $user = $this->createValidUser();
        $user->setPositive(null);

        $this->assertSame([], $this->validator->validate($user));
    }


    public function testItPassesWhenPositiveOrZeroIsPositive(): void
    {
        $user = $this->createValidUser();
        $user->setPositiveOrZero(5);

        $this->assertSame([], $this->validator->validate($user));
    }

    public function testItPassesWhenPositiveOrZeroIsZero(): void
    {
        $user = $this->createValidUser();
        $user->setPositiveOrZero(0);

        $this->assertSame([], $this->validator->validate($user));
    }

    public function testItFailsWhenPositiveOrZeroIsNegative(): void
    {
        $user = $this->createValidUser();
        $user->setPositiveOrZero(-3);

        $errors = $this->validator->validate($user);

        $this->assertSame(['positiveOrZero'], array_keys($errors));
        $this->assertCount(1, $errors['positiveOrZero']);
        $this->assertStringContainsString('doit être un nombre positif ou égal à zéro', $errors['positiveOrZero'][0]->message);
    }

    public function testItAllowsNullWhenPositiveOrZeroIsSet(): void
    {
        $user = $this->createValidUser();
        $user->setPositiveOrZero(null);

        $this->assertSame([], $this->validator->validate($user));
    }

    public function testItCollectsAllErrorsAcrossPropertiesAndConstraints(): void
    {
        $user = $this->createValidUser();
        $user->setFirstname('');
        $user->setEmail('invalid');
        $user->setPositive(-1);

        $this->assertEquals([
            'firstname' => [
                new ValidationError('firstname', 'La propriete "firstname" ne peut pas etre vide.', 'not_blank.blank'),
                new ValidationError('firstname', 'La propriete "firstname" doit contenir au moins 3 caracteres.', 'length.too_short', ['min' => 3]),
            ],
            'email' => [new ValidationError('email', 'La propriete "email" doit contenir une adresse email valide.', 'email.invalid')],
            'positive' => [new ValidationError('positive', 'La propriété "positive" doit être un nombre positif.', 'positive.not_positive')],
        ], $this->validator->validate($user));
    }

    public function testErrorsDoNotLeakBetweenValidations(): void
    {
        $user = $this->createValidUser();
        $user->setEmail('invalid');
        $this->assertArrayHasKey('email', $this->validator->validate($user));

        $user->setEmail('valid@example.com');
        $this->assertSame([], $this->validator->validate($user));
    }

    public function testUninitializedPropertyIsValidatedAsNull(): void
    {
        $entity = new class {
            #[\Mithridatem\Validation\Attributes\NotBlank]
            private string $name;
        };

        $this->assertEquals([
            'name' => [new ValidationError('name', 'La propriete "name" ne peut pas etre nulle.', 'not_blank.null')],
        ], $this->validator->validate($entity));
    }

    public function testObjectWithoutConstraintsReturnsEmptyArray(): void
    {
        $this->assertSame([], $this->validator->validate(new \stdClass()));
    }

    public function testInvalidConstraintConfigurationStillThrows(): void
    {
        $entity = new class {
            #[\Mithridatem\Validation\Attributes\Length(min: -1)]
            public string $name = 'test';
        };

        $this->expectException(\InvalidArgumentException::class);
        $this->validator->validate($entity);
    }

    public function testCustomConstraintCanReturnSeveralMessages(): void
    {
        $entity = new class {
            #[MultipleErrors]
            public string $name = 'invalid';
        };

        $this->assertEquals(['name' => [
            new ValidationError('name', 'First error.', 'custom.first'),
            new ValidationError('name', 'Second error.', 'custom.second'),
        ]], $this->validator->validate($entity));
    }

    private function createValidUser(): User
    {
        $user = new User();
        $user->setFirstname('Mathieu');
        $user->setLastname('Mithridate');
        $user->setEmail('mathieu@example.com');

        return $user;
    }
}

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class MultipleErrors implements \Mithridatem\Validation\Contracts\PropertyConstraint
{
    public function validate(string $property, mixed $value): array
    {
        return [
            new ValidationError($property, 'First error.', 'custom.first'),
            new ValidationError($property, 'Second error.', 'custom.second'),
        ];
    }
}
