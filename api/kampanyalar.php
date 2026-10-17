<?php
declare(strict_types=1);

require_once __DIR__ . '/../config.php';

header('Content-Type: application/json; charset=UTF-8');

$pdo = pdoBaglantisi();
$today = date('Y-m-d');

$sql = "SELECT
            k.id,
            k.baslik,
            k.aciklama,
            k.tarih,
            m.id AS mekan_id,
            m.ad AS mekan_adi,
            m.sehir,
            m.kategori
        FROM kampanyalar k
        INNER JOIN mekanlar m ON m.id = k.mekan_id
        WHERE k.aktif = 1
          AND m.aktif = 1
          AND (k.tarih = '' OR k.tarih = :today)
        ORDER BY CASE WHEN k.tarih = :today THEN 0 ELSE 1 END, m.sehir, m.ad";

$stmt = $pdo->prepare($sql);
$stmt->execute([':today' => $today]);
$kampanyalar = $stmt->fetchAll();

echo json_encode([
    'success' => true,
    'date' => $today,
    'count' => count($kampanyalar),
    'data' => $kampanyalar,
], JSON_UNESCAPED_UNICODE);
