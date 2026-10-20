<?php

namespace Mithridatem\Validation\Tests;

use Mithridatem\Validation\ValidationError;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ValidationErrorTest extends TestCase
{
    public function testItExposesMetadataAndDefaultsToEmptyParameters(): void
    {
        $error = new ValidationError('email', 'Invalid email.', 'email.invalid');

        $this->assertSame('email', $error->property);
        $this->assertSame('Invalid email.', $error->message);
        $this->assertSame('email.invalid', $error->code);
        $this->assertSame([], $error->parameters);
    }

    #[DataProvider('immutableProperties')]
    public function testItsPropertiesCannotBeReassigned(string $property, mixed $value): void
    {
        $error = new ValidationError('name', 'Too short.', 'length.too_short', ['min' => 3]);

        $this->expectException(\Error::class);
        $error->{$property} = $value;
    }

    public static function immutableProperties(): iterable
    {
        yield 'property' => ['property', 'other'];
        yield 'message' => ['message', 'Other message.'];
        yield 'code' => ['code', 'other.code'];
        yield 'parameters' => ['parameters', ['min' => 4]];
    }

    public function testItSerializesToJsonWithAllPublicMetadata(): void
    {
        $error = new ValidationError('name', 'Too short.', 'length.too_short', ['min' => 3]);
        $json = json_encode(['errors' => ['name' => [$error]]], JSON_THROW_ON_ERROR);

        $this->assertSame(['errors' => ['name' => [[
            'property' => 'name',
            'message' => 'Too short.',
            'code' => 'length.too_short',
            'parameters' => ['min' => 3],
        ]]]], json_decode($json, true, 512, JSON_THROW_ON_ERROR));
    }
}
