<?php

declare(strict_types=1);

namespace App\Dto;

use App\Entity\Techno;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class TechnoInput
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 50)]
    public string $name;

    #[Assert\Url(requireTld: true)]
    #[Assert\Length(max: 255)]
    public ?string $url;

    public function __construct(string $name = '', ?string $url = null)
    {
        $this->name = trim($name);
        $url = trim((string) $url);
        $this->url = '' === $url ? null : $url;
    }

    public function applyTo(Techno $techno): Techno
    {
        return $techno->setName($this->name)->setUrl($this->url);
    }
}
