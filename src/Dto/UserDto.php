<?php

declare(strict_types=1);

namespace App\Dto;

use App\Entity\User;

final readonly class UserDto
{
    /**
     * @param list<string> $roles
     */
    public function __construct(
        public int $id,
        public string $email,
        public array $roles,
        public bool $isVerified,
    ) {}

    public static function fromEntity(User $user): self
    {
        return new self(
            id: $user->getId(),
            email: $user->getEmail(),
            roles: $user->getRoles(),
            isVerified: $user->isVerified(),
        );
    }
}
