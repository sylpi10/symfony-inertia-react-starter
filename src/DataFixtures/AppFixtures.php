<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Techno;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class AppFixtures extends Fixture
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $technos = [
            ['Symfony', 'https://symfony.com'],
            ['Inertia.js', 'https://inertiajs.com'],
            ['React', 'https://react.dev'],
            ['TypeScript', 'https://www.typescriptlang.org'],
            ['SSR', 'https://inertiajs.com/docs/v3/advanced/server-side-rendering'],
            ['Sass', 'https://sass-lang.com'],
        ];

        foreach ($technos as $position => [$name, $url]) {
            $manager->persist((new Techno())
                ->setName($name)
                ->setUrl($url)
                ->setPosition($position));
        }

        $user = (new User())
            ->setEmail('test@example.com')
            ->setIsVerified(true);
        $user->setPassword($this->passwordHasher->hashPassword($user, 'test1234'));
        $manager->persist($user);

        $manager->flush();
    }
}
