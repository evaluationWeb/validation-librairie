<?php

namespace Mithridatem\Validation\Tests\Fixtures;

use Mithridatem\Validation\Attributes\Email;
use Mithridatem\Validation\Attributes\Length;
use Mithridatem\Validation\Attributes\NotBlank;

class User
{
    #[NotBlank]
    #[Length(min: 3, max: 40)]
    private ?string $firstname = null;

    #[Length(max: 50)]
    private ?string $lastname = null;

    #[Email]
    private ?string $email = null;

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
}
