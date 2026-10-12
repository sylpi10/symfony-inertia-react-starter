<?php

declare(strict_types=1);

namespace App\Dto\Page;

use App\Dto\TechnoDto;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
final readonly class TechnoIndexPageProps
{
    /**
     * @param list<TechnoDto> $technos
     */
    public function __construct(
        public array $technos,
        public int $page,
        public int $totalPages,
    ) {}
}
