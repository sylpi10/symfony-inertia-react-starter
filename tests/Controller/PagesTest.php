<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Tests\InertiaWebTestCase;

final class PagesTest extends InertiaWebTestCase
{
    public function testHomeListsTheTechnos(): void
    {
        $this->visit('GET', '/');

        self::assertResponseIsSuccessful();
        $this->page()
            ->component('Home')
            ->count('technos', 6)
            ->where('technos.0.name', 'Symfony')
            ->where('auth.user', null);
    }

    public function testUnknownUrlRendersTheErrorPage(): void
    {
        $this->visit('GET', '/does-not-exist');

        self::assertResponseStatusCodeSame(404);
        $this->page()->component('Error')->where('status', 404);
    }
}
