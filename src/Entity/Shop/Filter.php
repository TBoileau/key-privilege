<?php

declare(strict_types=1);

namespace App\Entity\Shop;

use Symfony\Component\Validator\Constraints as Assert;

class Filter
{
    public ?string $keywords = null;

    /**
     * @Assert\GreaterThan(0)
     */
    public int $min;

    /**
     * @Assert\GreaterThan(propertyPath="min")
     */
    public int $max;

    public ?Brand $brand = null;
}
