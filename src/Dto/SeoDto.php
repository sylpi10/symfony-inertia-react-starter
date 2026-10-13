<?php

declare(strict_types=1);

namespace App\Dto;

use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/**
 * Head tags of a page. Build it with App\Seo\SeoFactory.
 */
#[TypeScript]
final readonly class SeoDto
{
    public function __construct(
        public string $title,
        public ?string $description = null,
        public ?string $canonical = null,
        public ?string $image = null,
        public bool $noindex = false,
    ) {
    }
}
