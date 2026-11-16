<?php
// persuratan/public/api/sync.php

require_once __DIR__ . '/../../config/app.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit;
}

$rawInput = file_get_contents('php://input');
$data = json_decode($rawInput, true);

if (!$data) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid JSON input']);
    exit;
}

try {
    $type = $data['type'] ?? 'surat_masuk';
    $payload = $data['payload'] ?? $data;

    $newId = null;
    if ($type === 'surat_masuk') {
        $newId = SuratMasuk::create($payload);
    } elseif ($type === 'surat_keluar') {
        $newId = SuratKeluar::create($payload);
    } elseif ($type === 'tindak_lanjut') {
        $newId = TindakLanjut::create($payload);
    }

    echo json_encode([
        'success' => true,
        'message' => 'Data offline berhasil disinkronkan ke server.',
        'id' => $newId,
        'synced_at' => date('Y-m-d H:i:s')
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Gagal memproses sinkronisasi: ' . $e->getMessage()
    ]);
}
