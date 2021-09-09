<?php

declare(strict_types=1);

namespace App\Pdf;

use Knp\Snappy\Pdf;
use Twig\Environment;

final class Generator implements GeneratorInterface
{
    private Pdf $pdf;

    private Environment $twig;

    private string $publicDir;

    public function __construct(Pdf $pdf, Environment $twig, string $publicDir)
    {
        $this->pdf = $pdf;
        $this->twig = $twig;
        $this->publicDir = $publicDir;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function generate(string $filename, string $view, array $data = []): string
    {
        $html = $this->twig->render($view, $data);

        $this->pdf->generateFromHtml(
            $html,
            sprintf('%s/pdf/%s.pdf', $this->publicDir, $filename)
        );

        return sprintf('pdf/%s.pdf', $filename);
    }
}
