<?php

declare(strict_types=1);

namespace App\Dto\Page;

use App\Dto\TechnoDto;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
final readonly class TechnoFormPageProps
{
    public function __construct(
        public ?TechnoDto $techno,
    ) {}
}
