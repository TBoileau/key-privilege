<?php

declare(strict_types=1);

namespace App\Entity\Order;

class Sav
{
    public Line $line;

    public string $description;

    public ?string $comment = null;

    /**
     * @var array<int, string>
     */
    public array $attachments = [];
}
