<?php

declare(strict_types=1);

namespace App\Pdf;

interface GeneratorInterface
{
    /**
     * @param array<string, mixed> $data
     */
    public function generate(string $filename, string $view, array $data = []): string;
}
