<?php
// persuratan/app/helpers/PdfHelper.php

use Dompdf\Dompdf;
use Dompdf\Options;

class PdfHelper {
    public static function streamHtml(string $html, string $filename = 'dokumen.pdf', string $paper = 'A4', string $orientation = 'portrait'): void {
        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('defaultFont', 'Helvetica');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper($paper, $orientation);
        $dompdf->render();

        $dompdf->stream($filename, ["Attachment" => false]);
        exit;
    }

    public static function saveHtml(string $html, string $filepath, string $paper = 'A4', string $orientation = 'portrait'): bool {
        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('defaultFont', 'Helvetica');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper($paper, $orientation);
        $dompdf->render();

        $output = $dompdf->output();
        return file_put_contents($filepath, $output) !== false;
    }
}
