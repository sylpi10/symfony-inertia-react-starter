<?php

declare(strict_types=1);

namespace App\Dto;

use App\Entity\Techno;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript(name: 'Techno')]
final readonly class TechnoDto
{
    public function __construct(
        public int $id,
        public string $name,
        public ?string $url,
    ) {
    }

    public static function fromEntity(Techno $techno): self
    {
        return new self(
            id: (int) $techno->getId(),
            name: (string) $techno->getName(),
            url: $techno->getUrl(),
        );
    }
}
