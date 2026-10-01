<?php

declare(strict_types=1);

namespace App\Dto;

use App\Entity\User;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class ForgotPasswordInput
{
    #[Assert\NotBlank]
    #[Assert\Email]
    public string $email;

    public function __construct(string $email = '')
    {
        $this->email = User::normalizeEmail($email);
    }
}
