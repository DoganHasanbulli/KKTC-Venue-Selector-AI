<?php
declare(strict_types=1);

require_once __DIR__ . '/../config.php';

header('Content-Type: application/json; charset=UTF-8');

$pdo = pdoBaglantisi();
$today = date('Y-m-d');

$sehir = trim($_GET['sehir'] ?? 'Tümü');
$kategori = trim($_GET['kategori'] ?? 'tum');
$arama = trim($_GET['arama'] ?? '');

$sehirTumMu = in_array(mb_strtolower($sehir), ['tüm', 'tümü', 'tum'], true);
$kategoriTumMu = in_array(mb_strtolower($kategori), ['tüm', 'tümü', 'tum'], true);

if (!$sehirTumMu && !gecerliSehirMi($sehir)) {
    echo json_encode(['success' => false, 'message' => 'Geçersiz şehir filtresi.'], JSON_UNESCAPED_UNICODE);
    exit;
}

if (!$kategoriTumMu && !gecerliKategoriMi($kategori)) {
    echo json_encode(['success' => false, 'message' => 'Geçersiz kategori filtresi.'], JSON_UNESCAPED_UNICODE);
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
            EXISTS(
                SELECT 1
                FROM kampanyalar k
                WHERE k.mekan_id = m.id
                  AND k.aktif = 1
                  AND (k.tarih = '' OR k.tarih = :today)
            ) AS kampanya_var
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

if ($arama !== '') {
    $sql .= ' AND (m.ad LIKE :arama OR m.adres LIKE :arama OR m.aciklama LIKE :arama)';
    $params[':arama'] = '%' . $arama . '%';
}

$sql .= ' ORDER BY m.sehir, m.kategori, m.ad';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$mekanlar = $stmt->fetchAll();

foreach ($mekanlar as &$mekan) {
    $mekan['populer'] = populerMi($mekan['ad']);
}
unset($mekan);

echo json_encode([
    'success' => true,
    'count' => count($mekanlar),
    'data' => $mekanlar,
], JSON_UNESCAPED_UNICODE);
