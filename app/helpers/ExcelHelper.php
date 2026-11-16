<?php
// persuratan/app/helpers/ExcelHelper.php

class ExcelHelper {
    /**
     * Export data to Excel-compatible CSV with UTF-8 BOM
     */
    public static function exportCsv(array $headers, array $rows, string $filename = 'export.csv'): void {
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');
        // UTF-8 BOM so Excel opens with correct encoding and special characters
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        fputcsv($output, $headers, ';');
        foreach ($rows as $row) {
            fputcsv($output, $row, ';');
        }
        fclose($output);
        exit;
    }

    /**
     * Export styled Excel table format (.xls)
     */
    public static function exportTable(string $title, array $headers, array $rows, string $filename = 'export.xls'): void {
        header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
        echo '<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8">';
        echo '<style>
            th { background-color: #2563EB; color: #ffffff; font-weight: bold; padding: 8px; border: 1px solid #ddd; }
            td { padding: 6px; border: 1px solid #ddd; }
            h2 { font-family: sans-serif; color: #1E293B; margin-bottom: 5px; }
            p { font-family: sans-serif; color: #64748B; font-size: 12px; margin-top: 0; }
        </style></head><body>';
        echo '<h2>' . htmlspecialchars($title) . '</h2>';
        echo '<p>Dicetak pada: ' . date('d F Y H:i:s') . ' WIB</p>';
        echo '<table border="1" cellpadding="6" cellspacing="0">';
        echo '<thead><tr>';
        foreach ($headers as $h) {
            echo '<th>' . htmlspecialchars($h) . '</th>';
        }
        echo '</tr></thead><tbody>';
        foreach ($rows as $row) {
            echo '<tr>';
            foreach ($row as $cell) {
                echo '<td>' . htmlspecialchars((string)$cell) . '</td>';
            }
            echo '</tr>';
        }
        echo '</tbody></table></body></html>';
        exit;
    }
}
