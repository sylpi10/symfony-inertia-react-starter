<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Tests\InertiaWebTestCase;

final class AboutTest extends InertiaWebTestCase
{
    public function testAboutPageContent(): void
    {
        $this->visit('GET', '/about');
        self::assertResponseIsSuccessful();
        $this->page()
            ->component('About')
            ->where('title', 'About Page');
    }
}
