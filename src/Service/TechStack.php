<?php

namespace App\Service;

final class TechStack
{
    /**
     * @return list<string>
     */
    public function getStack(): array
    {
        return ["Symfony", "Inertia.js", "React", "TypeScript", "SSR", "Sass"];
    }
}
