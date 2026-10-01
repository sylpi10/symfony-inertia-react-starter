<?php

declare(strict_types=1);

namespace App\Dto\Page;

use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
final readonly class AboutPageProps
{
    public function __construct(
        public string $title,
    ) {}
}
