<?php

declare(strict_types=1);

namespace App\Dto\Page;

use App\Dto\TechnoDto;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
final readonly class AboutPageProps
{
    /**
     * @param list<TechnoDto> $technos
     */
    public function __construct(
        public string $title,
        public string $para,
        public array $technos,
    ) {}
}
