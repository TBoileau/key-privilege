<?php

declare(strict_types=1);

namespace App\Pdf;

use App\Entity\Order\Order;
use Jurosh\PDFMerge\PDFMerger;
use Knp\Snappy\Pdf;
use Twig\Environment;

class Generator implements GeneratorInterface
{
    private Pdf $pdf;

    private Environment $twig;

    protected string $publicDir;

    public function __construct(Pdf $pdf, Environment $twig, string $publicDir)
    {
        $this->pdf = $pdf;
        $this->twig = $twig;
        $this->publicDir = $publicDir;
    }

    /**
     * @param array<string, mixed> $data
     */
    protected function generatePage(string $filename, string $view, array $data = []): string
    {
        if (is_file(sprintf('%s/pdf/%s.pdf', $this->publicDir, $filename))) {
            unlink(sprintf('%s/pdf/%s.pdf', $this->publicDir, $filename));
        }

        $this->pdf->generateFromHtml(
            $this->twig->render($view, $data),
            sprintf('%s/pdf/%s.pdf', $this->publicDir, $filename)
        );

        return sprintf('pdf/%s.pdf', $filename);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function generate(string $filename, string $view, array $data = []): string
    {
        return $this->generatePage($filename, $view, $data);
    }
}
