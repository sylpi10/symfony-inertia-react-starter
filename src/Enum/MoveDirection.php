<?php

declare(strict_types=1);

namespace App\Enum;

use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
enum MoveDirection: string
{
    case Up = 'up';
    case Down = 'down';
}
