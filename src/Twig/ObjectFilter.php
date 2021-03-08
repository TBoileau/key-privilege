<?php

declare(strict_types=1);

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

use function Symfony\Component\String\u;

class ObjectFilter extends AbstractExtension
{
    public function getFilters(): array
    {
        return [new TwigFilter('class', [$this, 'getClass'])];
    }

    public function getClass(object $object): string
    {
        return u($object::class)->replace("Proxies\__CG__\\", "")->toString();
    }
}
