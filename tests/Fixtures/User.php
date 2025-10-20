<?php

namespace Mithridatem\Validation\Tests\Fixtures;

use Mithridatem\Validation\Attributes\Email;
use Mithridatem\Validation\Attributes\Length;
use Mithridatem\Validation\Attributes\Negative;
use Mithridatem\Validation\Attributes\NegativeOrZero;
use Mithridatem\Validation\Attributes\NotBlank;
use Mithridatem\Validation\Attributes\Pattern;
use Mithridatem\Validation\Attributes\Positive;
use Mithridatem\Validation\Attributes\PositiveOrZero;

class User
{
    #[NotBlank]
    #[Length(min: 3, max: 40)]
    private ?string $firstname = null;

    #[Length(max: 50)]
    private ?string $lastname = null;

    #[Email]
    private ?string $email = null;

    #[Pattern(pattern: '/^[a-zA-Z0-9]+$/')]
    private ?string $pattern = null;

    #[Negative]
    private ?int $negative = null;

    #[NegativeOrZero]
    private ?int $negativeOrZero = null;

    #[Positive]
    private ?int $positive = null;

    #[PositiveOrZero]
    private ?int $positiveOrZero = null;

    public function setFirstname(?string $firstname): void
    {
        $this->firstname = $firstname;
    }

    public function setLastname(?string $lastname): void
    {
        $this->lastname = $lastname;
    }

    public function setEmail(?string $email): void
    {
        $this->email = $email;
    }

    public function setPattern(?string $pattern): void
    {
        $this->pattern = $pattern;
    }

    public function setNegative(?int $negative): void
    {
        $this->negative = $negative;
    }

    public function setNegativeOrZero(?int $negativeOrZero): void
    {
        $this->negativeOrZero = $negativeOrZero;
    }

    public function setPositive(?int $positive): void
    {
        $this->positive = $positive;
    }


    public function setPositiveOrZero(?int $positiveOrZero): void
    {
        $this->positiveOrZero = $positiveOrZero;
    }
}
