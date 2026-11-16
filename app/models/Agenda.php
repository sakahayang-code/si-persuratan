<?php
// persuratan/app/models/Agenda.php

class Agenda {
    public static function getAll(array $filters = []): array {
        $db = Database::getInstance();
        $sql = "SELECT * FROM agenda_surat WHERE 1=1";
        $params = [];

        if (!empty($filters['jenis'])) {
            $sql .= " AND jenis = :jenis";
            $params[':jenis'] = $filters['jenis'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (nomor_agenda LIKE :search OR nomor_surat LIKE :search OR pengirim_penerima LIKE :search OR perihal LIKE :search)";
            $params[':search'] = '%' . $filters['search'] . '%';
        }

        if (!empty($filters['date_from'])) {
            $sql .= " AND tanggal_agenda >= :date_from";
            $params[':date_from'] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $sql .= " AND tanggal_agenda <= :date_to";
            $params[':date_to'] = $filters['date_to'];
        }

        $sql .= " ORDER BY tanggal_agenda DESC, id DESC";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function getRecapByMonth(int $year): array {
        $db = Database::getInstance();
        $stmt = $db->prepare("
            SELECT strftime('%m', tanggal_agenda) as bulan, jenis, COUNT(*) as total
            FROM agenda_surat
            WHERE strftime('%Y', tanggal_agenda) = :year
            GROUP BY bulan, jenis
            ORDER BY bulan ASC
        ");
        $stmt->execute([':year' => (string)$year]);
        $rows = $stmt->fetchAll();

        $recap = [];
        for ($m = 1; $m <= 12; $m++) {
            $key = str_pad((string)$m, 2, '0', STR_PAD_LEFT);
            $recap[$key] = ['masuk' => 0, 'keluar' => 0];
        }

        foreach ($rows as $r) {
            $m = $r['bulan'];
            if (isset($recap[$m])) {
                $recap[$m][$r['jenis']] = (int)$r['total'];
            }
        }

        return $recap;
    }
}
