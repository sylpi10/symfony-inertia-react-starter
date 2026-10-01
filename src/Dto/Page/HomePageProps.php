<?php

declare(strict_types=1);

namespace App\Dto\Page;

use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
final readonly class HomePageProps
{
    /**
     * @param list<string> $technos
     */
    public function __construct(
        public string $title,
        public string $para,
        public array $technos,
    ) {}
}
