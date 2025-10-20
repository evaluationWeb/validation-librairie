<?php

namespace Mithridatem\Validation\Tests;

use Mithridatem\Validation\Exception\ValidationException;
use Mithridatem\Validation\Tests\Fixtures\User;
use Mithridatem\Validation\Validator;
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

        $this->validator->validate($user);

        $this->assertTrue(true); // No exception thrown
    }

    public function testItFailsWhenNotBlankConstraintIsViolated(): void
    {
        $user = new User();
        $user->setFirstname(null);

        $this->expectException(ValidationException::class);
        $this->validator->validate($user);
    }

    public function testItFailsWhenLengthConstraintMinimumIsViolated(): void
    {
        $user = $this->createValidUser();
        $user->setFirstname('Jo');

        $this->expectException(ValidationException::class);
        $this->validator->validate($user);
    }

    public function testItFailsWhenEmailConstraintIsViolated(): void
    {
        $user = $this->createValidUser();
        $user->setEmail('invalid-email');

        $this->expectException(ValidationException::class);
        $this->validator->validate($user);
    }

    public function testItPassesWhenPatternConstraintIsSatisfied(): void
    {
        $user = $this->createValidUser();
        $user->setPattern('User123');

        $this->validator->validate($user);

        $this->assertTrue(true);
    }

    public function testItFailsWhenPatternConstraintIsViolated(): void
    {
        $user = $this->createValidUser();
        $user->setPattern('User_123');

        $this->expectException(ValidationException::class);
        $this->validator->validate($user);
    }

    public function testItAllowsNullWhenPatternConstraintIsSet(): void
    {
        $user = $this->createValidUser();
        $user->setPattern(null);

        $this->validator->validate($user);

        $this->assertTrue(true);
    }

    public function testItPassesWhenNegativeConstraintIsSatisfied(): void
    {
        $user = $this->createValidUser();
        $user->setNegative(-10);

        $this->validator->validate($user);

        $this->assertTrue(true);
    }

    public function testItFailsWhenNegativeConstraintIsViolated(): void
    {
        $user = $this->createValidUser();
        $user->setNegative(5);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('doit être un nombre négatif');

        $this->validator->validate($user);
    }

    public function testItAllowsNullWhenNegativeConstraintIsSet(): void
    {
        $user = $this->createValidUser();
        $user->setNegative(null);

        $this->validator->validate($user);

        $this->assertTrue(true);
    }

    public function testItPassesWhenNegativeOrZeroIsNegative(): void
    {
        $user = $this->createValidUser();
        $user->setNegativeOrZero(-5);

        $this->validator->validate($user);

        $this->assertTrue(true);
    }

    public function testItPassesWhenNegativeOrZeroIsZero(): void
    {
        $user = $this->createValidUser();
        $user->setNegativeOrZero(0);

        $this->validator->validate($user);

        $this->assertTrue(true);
    }

    public function testItFailsWhenNegativeOrZeroIsPositive(): void
    {
        $user = $this->createValidUser();
        $user->setNegativeOrZero(3);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('doit être un nombre négatif ou égal à zéro');

        $this->validator->validate($user);
    }

    public function testItAllowsNullWhenNegativeOrZeroIsSet(): void
    {
        $user = $this->createValidUser();
        $user->setNegativeOrZero(null);

        $this->validator->validate($user);

        $this->assertTrue(true);
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
