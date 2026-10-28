<?php
declare(strict_types=1);

require_once __DIR__ . '/../config.php';

header('Content-Type: application/json; charset=UTF-8');

$sehir = isset($_GET['sehir']) ? trim((string) $_GET['sehir']) : 'Tümü';

if ($sehir !== '' && $sehir !== 'Tümü' && !in_array($sehir, SEHIRLER, true)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'Geçersiz şehir. Şunlardan biri olmalı: Tümü, ' . implode(', ', SEHIRLER),
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($sehir === '' || $sehir === 'Tümü') {
    $sehir = 'Tümü';
    $sayilar = mekanSayilari(null);
    $sehirler = [];

    foreach (SEHIRLER as $tekSehir) {
        $sehirler[$tekSehir] = mekanSayilari($tekSehir)['tum'];
    }
} else {
    $sayilar = mekanSayilari($sehir);
    $sehirler = [];
}

echo json_encode([
    'success' => true,
    'sehir' => $sehir,
    'toplam' => $sayilar['tum'],
    'kategori' => [
        'tum' => $sayilar['tum'],
        'cafe' => $sayilar['cafe'],
        'bar' => $sayilar['bar'],
        'restoran' => $sayilar['restoran'],
    ],
    'sehirler' => $sehirler,
], JSON_UNESCAPED_UNICODE);
