<?php

declare(strict_types=1);

namespace App\Pdf;

use App\Entity\Order\Order;
use Jurosh\PDFMerge\PDFMerger;

final class OrderGenerator extends Generator
{
    /**
     * @param array<string, mixed> $data
     */
    public function generate(string $filename, string $view, array $data = []): string
    {
        /** @var Order $order */
        $order = $data['order'];

        $groupOfLines = array_chunk($order->getLines()->toArray(), 15);

        $pdfMerger = new PDFMerger();

        foreach ($groupOfLines as $page => $lines) {
            $pdfMerger->addPDF(
                sprintf(
                    '%s/%s',
                    $this->publicDir,
                    $this->generatePage(
                        $filename . '-p' . ($page + 1),
                        $view,
                        [
                            'order' => $order,
                            'lines' => $lines,
                            'page' => $page + 1,
                            'pages' => count($groupOfLines)
                        ]
                    )
                )
            );
        }

        if (is_file(sprintf('%s/pdf/%s.pdf', $this->publicDir, $filename))) {
            unlink(sprintf('%s/pdf/%s.pdf', $this->publicDir, $filename));
        }

        $pdfMerger->merge('file', sprintf('%s/pdf/%s.pdf', $this->publicDir, $filename));

        return sprintf('pdf/%s.pdf', $filename);
    }
}
