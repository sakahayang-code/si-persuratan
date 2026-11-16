<?php
// persuratan/app/helpers/Numbering.php

class Numbering {
    private static array $romanMonths = [
        1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI',
        7 => 'VII', 8 => 'VIII', 9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII'
    ];

    /**
     * Generate next official letter number atomically
     */
    public static function generateNextNumber(string $kodeKlasifikasi, ?string $customFormat = null): string {
        $db = Database::getInstance();
        $currentYear = (int)date('Y');
        $currentMonth = (int)date('n');
        $romanMonth = self::$romanMonths[$currentMonth];

        $db->beginTransaction();
        try {
            // Find existing counter rule for this classification and year
            $stmt = $db->prepare("
                SELECT * FROM nomor_surat 
                WHERE kode_klasifikasi = :kode AND tahun = :tahun
            ");
            $stmt->execute([':kode' => $kodeKlasifikasi, ':tahun' => $currentYear]);
            $rule = $stmt->fetch();

            if (!$rule) {
                // Check if general rule exists
                $stmtGen = $db->prepare("SELECT * FROM nomor_surat WHERE (kode_klasifikasi IS NULL OR kode_klasifikasi = '') LIMIT 1");
                $stmtGen->execute();
                $genRule = $stmtGen->fetch();

                $pattern = $customFormat ?: ($genRule['format_pola'] ?? '{nomor}/{kode}/BPTI/{tahun}');
                $nextCounter = 1;

                $ins = $db->prepare("
                    INSERT INTO nomor_surat (kode_klasifikasi, format_pola, counter, tahun, reset_period, last_number)
                    VALUES (:kode, :format, :counter, :tahun, 'yearly', '')
                ");
                $ins->execute([
                    ':kode' => $kodeKlasifikasi,
                    ':format' => $pattern,
                    ':counter' => $nextCounter,
                    ':tahun' => $currentYear
                ]);
                $ruleId = (int)$db->lastInsertId();
            } else {
                $ruleId = (int)$rule['id'];
                $pattern = $customFormat ?: $rule['format_pola'];
                $nextCounter = (int)$rule['counter'] + 1;
            }

            // Format number padded to 3 digits
            $formattedNumber = str_pad((string)$nextCounter, 3, '0', STR_PAD_LEFT);

            // Replace pattern placeholders
            $placeholders = [
                '{nomor}' => $formattedNumber,
                '{kode}' => $kodeKlasifikasi,
                '{bulan_romawi}' => $romanMonth,
                '{bulan}' => str_pad((string)$currentMonth, 2, '0', STR_PAD_LEFT),
                '{tahun}' => (string)$currentYear
            ];

            $resultNumber = str_replace(array_keys($placeholders), array_values($placeholders), $pattern);

            // Update counter in database
            $upd = $db->prepare("
                UPDATE nomor_surat 
                SET counter = :counter, last_number = :last_number, updated_at = datetime('now', 'localtime')
                WHERE id = :id
            ");
            $upd->execute([
                ':counter' => $nextCounter,
                ':last_number' => $resultNumber,
                ':id' => $ruleId
            ]);

            $db->commit();
            return $resultNumber;
        } catch (Throwable $e) {
            $db->rollBack();
            // Fallback emergency number
            return sprintf("%03d/%s/BPTI/%s/%d", rand(100, 999), $kodeKlasifikasi, $romanMonth, $currentYear);
        }
    }

    /**
     * Generate automatic Agenda Number (e.g. AG-M-004/2026 or AG-K-004/2026)
     */
    public static function generateAgendaNumber(string $type = 'masuk'): string {
        $db = Database::getInstance();
        $prefix = ($type === 'masuk') ? 'AG-M' : 'AG-K';
        $currentYear = date('Y');

        if ($type === 'masuk') {
            $stmt = $db->query("SELECT COUNT(*) FROM surat_masuk WHERE strftime('%Y', created_at) = '$currentYear'");
        } else {
            $stmt = $db->query("SELECT COUNT(*) FROM surat_keluar WHERE strftime('%Y', created_at) = '$currentYear'");
        }
        $count = (int)$stmt->fetchColumn() + 1;

        return sprintf("%s-%03d/%s", $prefix, $count, $currentYear);
    }
}
