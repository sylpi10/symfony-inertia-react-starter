<?php

declare(strict_types=1);

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class ResetPasswordInput
{
    public function __construct(
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
