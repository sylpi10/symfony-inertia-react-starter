<?php

declare(strict_types=1);

namespace App\Dto;

use App\Entity\User;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[
    UniqueEntity(
        fields: "email",
        entityClass: User::class,
        message: "An account already exists with this email.",
    ),
]
final readonly class RegisterUserInput
{
    public function __construct(
        #[Assert\NotBlank] #[Assert\Email] public string $email = "",

        #[Assert\NotBlank] #[
            Assert\Length(min: 8, max: 4096),
        ]
        public string $password = "",

        #[
            Assert\EqualTo(
                propertyPath: "password",
                message: "The passwords do not match.",
            ),
        ]
        public string $passwordConfirmation = "",
    ) {}
}
