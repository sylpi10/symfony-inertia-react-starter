<?php

declare(strict_types=1);

namespace App\Dto;

use App\Enum\MoveDirection;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class MoveTechnoInput
{
    public function __construct(
        #[Assert\NotNull]
        public ?MoveDirection $direction = null,
    ) {}
}
