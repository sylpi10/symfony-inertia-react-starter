<?php

declare(strict_types=1);

namespace App\Tests\Controller\Admin;

use App\Entity\Techno;
use App\Repository\TechnoRepository;
use App\Tests\InertiaWebTestCase;

final class TechnoControllerTest extends InertiaWebTestCase
{
    public function testAnonymousIsRedirectedToLogin(): void
    {
        $this->visit('GET', '/admin/technos');

        self::assertResponseRedirects('http://localhost/login');
    }

    public function testNonAdminIsDenied(): void
    {
        $this->loginAs('test@example.com');
        $this->visit('GET', '/admin/technos');

        self::assertResponseStatusCodeSame(403);
        $this->page()->component('Error')->where('status', 403);
    }

    public function testNonAdminCannotDelete(): void
    {
        $id = $this->techno('Symfony')->getId();

        $this->loginAs('test@example.com');
        $this->visit('DELETE', "/admin/technos/$id");

        self::assertResponseStatusCodeSame(403);
        self::assertNotNull($this->technos()->find($id));
    }

    public function testListIsPaginated(): void
    {
        $this->loginAs('admin@example.com');

        $this->visit('GET', '/admin/technos');
        $this->page()
            ->component('Admin/Technos/Index')
            ->count('technos', 4)
            ->where('technos.0.name', 'Symfony')
            ->where('page', 1)
            ->where('totalPages', 2);

        $this->visit('GET', '/admin/technos?page=2');
        $this->page()->count('technos', 2)->where('page', 2);

        // Past the last page: back to the last one
        $this->visit('GET', '/admin/technos?page=9');
        self::assertResponseRedirects('/admin/technos?page=2');
    }

    public function testCreate(): void
    {
        $this->loginAs('admin@example.com');
        $this->visit('GET', '/admin/technos/new');
        $this->page()->component('Admin/Technos/Form')->where('techno', null);

        $this->visit('POST', '/admin/technos', ['name' => ' Vite ', 'url' => 'https://vite.dev']);

        // 7 technos, 4 per page: the new one is on page 2
        self::assertResponseRedirects('/admin/technos?page=2', 303);
        $this->client->followRedirect();
        $this->page()->count('technos', 3)->where('technos.2.name', 'Vite');
        self::assertSame('"Vite" created.', $this->flash('success'));
    }

    public function testInvalidUpdateComesBackAsErrors(): void
    {
        $id = $this->techno('Symfony')->getId();

        $this->loginAs('admin@example.com');
        $this->visit('GET', "/admin/technos/$id/edit");
        $this->visit('PUT', "/admin/technos/$id", ['name' => '', 'url' => 'not-a-url']);

        self::assertResponseRedirects("http://localhost/admin/technos/$id/edit", 303);
        $this->client->followRedirect();
        $this->page()
            ->component('Admin/Technos/Form')
            ->where('errors.name', 'This value should not be blank.')
            ->where('errors.url', 'This value is not a valid URL.')
            ->where('techno.name', 'Symfony');
    }

    public function testUpdate(): void
    {
        $id = $this->techno('Symfony')->getId();

        $this->loginAs('admin@example.com');
        $this->visit('GET', "/admin/technos/$id/edit");
        $this->visit('PUT', "/admin/technos/$id", ['name' => 'Symfony 8', 'url' => '']);

        self::assertResponseRedirects('/admin/technos', 303);

        $techno = $this->technos()->find($id);
        self::assertSame('Symfony 8', $techno->getName());
        self::assertNull($techno->getUrl(), 'An empty URL is stored as null');
    }

    public function testDelete(): void
    {
        $id = $this->techno('Sass')->getId();

        $this->loginAs('admin@example.com');
        $this->visit('GET', '/admin/technos?page=2');
        $this->visit('DELETE', "/admin/technos/$id?page=2");

        self::assertResponseRedirects('/admin/technos?page=2', 303);
        self::assertNull($this->technos()->find($id));
    }

    public function testMoveSwapsWithTheNeighbor(): void
    {
        $id = $this->techno('Symfony')->getId();

        $this->loginAs('admin@example.com');
        $this->visit('GET', '/admin/technos');
        $this->visit('PATCH', "/admin/technos/$id/move?page=1", ['direction' => 'down']);

        self::assertResponseRedirects('/admin/technos?page=1', 303);
        $this->client->followRedirect();
        $this->page()
            ->where('technos.0.name', 'Inertia.js')
            ->where('technos.1.name', 'Symfony');
    }

    private function technos(): TechnoRepository
    {
        return static::getContainer()->get(TechnoRepository::class);
    }

    private function techno(string $name): Techno
    {
        $techno = $this->technos()->findOneBy(['name' => $name]);
        self::assertInstanceOf(Techno::class, $techno, sprintf('Techno "%s" not found: are the fixtures loaded?', $name));

        return $techno;
    }
}
