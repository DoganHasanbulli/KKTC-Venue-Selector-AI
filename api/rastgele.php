<?php
declare(strict_types=1);

require_once __DIR__ . '/../config.php';

header('Content-Type: application/json; charset=UTF-8');

$pdo = pdoBaglantisi();
$today = date('Y-m-d');

$sehir = trim($_GET['sehir'] ?? 'Tümü');
$kategori = trim($_GET['kategori'] ?? 'tum');
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

$sehirTumMu = in_array(mb_strtolower($sehir), ['tüm', 'tümü', 'tum'], true);
$kategoriTumMu = in_array(mb_strtolower($kategori), ['tüm', 'tümü', 'tum'], true);

if (!$sehirTumMu && !gecerliSehirMi($sehir)) {
    echo json_encode([
        'success' => false,
        'message' => 'Geçersiz şehir seçimi yaptınız.',
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if (!$kategoriTumMu && !gecerliKategoriMi($kategori)) {
    echo json_encode([
        'success' => false,
        'message' => 'Geçersiz kategori seçimi yaptınız.',
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$sql = "SELECT
            m.id,
            m.ad,
            m.kategori,
            m.sehir,
            m.adres,
            m.telefon,
            m.aciklama,
            (
                SELECT k.baslik
                FROM kampanyalar k
                WHERE k.mekan_id = m.id
                  AND k.aktif = 1
                  AND (k.tarih = '' OR k.tarih = :today)
                ORDER BY CASE WHEN k.tarih = :today THEN 0 ELSE 1 END, k.id DESC
                LIMIT 1
            ) AS kampanya_baslik,
            (
                SELECT k.aciklama
                FROM kampanyalar k
                WHERE k.mekan_id = m.id
                  AND k.aktif = 1
                  AND (k.tarih = '' OR k.tarih = :today)
                ORDER BY CASE WHEN k.tarih = :today THEN 0 ELSE 1 END, k.id DESC
                LIMIT 1
            ) AS kampanya_aciklama
        FROM mekanlar m
        WHERE m.aktif = 1";

$params = [':today' => $today];

if (!$sehirTumMu) {
    $sql .= ' AND m.sehir = :sehir';
    $params[':sehir'] = $sehir;
}

if (!$kategoriTumMu) {
    $sql .= ' AND m.kategori = :kategori';
    $params[':kategori'] = $kategori;
}

if ($id > 0) {
    $sql .= ' AND m.id = :id LIMIT 1';
    $params[':id'] = $id;
} else {
    $sql .= ' ORDER BY RANDOM() LIMIT 1';
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$mekan = $stmt->fetch();

if (!$mekan) {
    $mesaj = 'Bu kategoride/şehirde henüz mekan yok.';

    echo json_encode([
        'success' => false,
        'message' => $mesaj,
        'data' => null,
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$mekan['kampanya_var'] = !empty($mekan['kampanya_baslik']);
$mekan['populer'] = populerMi($mekan['ad']);

echo json_encode([
    'success' => true,
    'message' => 'Mekan başarıyla seçildi.',
    'data' => $mekan,
], JSON_UNESCAPED_UNICODE);
