<?php
date_default_timezone_set('Asia/Nicosia');

const KATEGORILER = ['cafe', 'bar', 'restoran'];
const SEHIRLER = ['Lefkoşa', 'Girne', 'Gazimağusa', 'İskele', 'Güzelyurt', 'Lefke'];

function pdoBaglantisi(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $dbDosyasi = __DIR__ . '/database/kktc.db';
    $ilkKurulum = !file_exists($dbDosyasi);

    $pdo = new PDO('sqlite:' . $dbDosyasi);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->exec('PRAGMA foreign_keys = ON');

    if ($ilkKurulum) {
        veritabaniKur($pdo);
        veritabaniTohumla($pdo);
    }

    return $pdo;
}

function veritabaniKur(PDO $pdo): void
{
    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS mekanlar (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            ad TEXT NOT NULL,
            kategori TEXT NOT NULL CHECK(kategori IN ('cafe','bar','restoran')),
            sehir TEXT NOT NULL,
            adres TEXT,
            telefon TEXT,
            aciklama TEXT,
            aktif INTEGER NOT NULL DEFAULT 1
        )"
    );

    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS kampanyalar (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            mekan_id INTEGER NOT NULL,
            baslik TEXT NOT NULL,
            aciklama TEXT,
            tarih TEXT,
            aktif INTEGER NOT NULL DEFAULT 1,
            FOREIGN KEY (mekan_id) REFERENCES mekanlar(id) ON DELETE CASCADE
        )"
    );

    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS yonetici (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            kullanici_adi TEXT NOT NULL UNIQUE,
            sifre_hash TEXT NOT NULL
        )"
    );
}

function veritabaniTohumla(PDO $pdo): void
{
    $mekanlar = [
        // Lefkoşa - Cafe
        ['Cafe No:3', 'cafe', 'Lefkoşa'],
        ['Mado', 'cafe', 'Lefkoşa'],
        ['Bibliotheque Café & Restaurant', 'cafe', 'Lefkoşa'],
        ['The Gym (The Gym Kafe)', 'cafe', 'Lefkoşa'],
        ['Pralina Experience', 'cafe', 'Lefkoşa'],
        ['Özerlat Coffee', 'cafe', 'Lefkoşa'],
        ['Gustav Roasting Co.', 'cafe', 'Lefkoşa'],
        ['Güngörmez Kahvehanesi', 'cafe', 'Lefkoşa'],

        // Lefkoşa - Restoran
        ['Saraba Ev Yemekleri', 'restoran', 'Lefkoşa'],
        ['Çember Restaurant', 'restoran', 'Lefkoşa'],
        ['Budak Pastanesi', 'restoran', 'Lefkoşa'],
        ['Bereket Fırını', 'restoran', 'Lefkoşa'],
        ['Hamur', 'restoran', 'Lefkoşa'],
        ['Mamülcüoğlu', 'restoran', 'Lefkoşa'],
        ['Ahmet Kanan Sütlü Mamulleri', 'restoran', 'Lefkoşa'],
        ['Cafe Baygün', 'restoran', 'Lefkoşa'],

        // Girne - Bar
        ['BarDuck Gastro Bar', 'bar', 'Girne'],
        ['Bond Gastro Pub', 'bar', 'Girne'],
        ['Ego Bar', 'bar', 'Girne'],
        ['Sky Lounge & Bar', 'bar', 'Girne'],

        // Girne - Restoran
        ["Niazi's Restaurant", 'restoran', 'Girne'],
        ['Archway Restaurant', 'restoran', 'Girne'],
        ['Epic Burger Girne', 'restoran', 'Girne'],
        ['Driftwood', 'restoran', 'Girne'],
        ['Bellapais Gardens Restaurant', 'restoran', 'Girne'],
        ['Kybele Restaurant', 'restoran', 'Girne'],
        ['Chamada Beach Club', 'restoran', 'Girne'],
        ['Escape Beach Club', 'restoran', 'Girne'],
        ['Silver Rocks Restaurant', 'restoran', 'Girne'],
        ['Da Vinci Irina', 'restoran', 'Girne'],
        ['Take Eat Easy', 'restoran', 'Girne'],
        ['Grida Balık Restaurant', 'restoran', 'Girne'],
        ['Peris Fish Restaurant', 'restoran', 'Girne'],
        ['Set Fish', 'restoran', 'Girne'],
        ['The Ambiance', 'restoran', 'Girne'],
        ['Nazende Ocakbaşı', 'restoran', 'Girne'],
        ['Kömür Steak & Ocakbaşı', 'restoran', 'Girne'],
        ['Develi Kebap', 'restoran', 'Girne'],
        ['The Dragon', 'restoran', 'Girne'],
        ['JOYA Italiano & Steak House', 'restoran', 'Girne'],
        ['Nima Restaurant & Lounge Bar', 'restoran', 'Girne'],
        ['Tahta Palas Kahvaltı', 'restoran', 'Girne'],
        ['Fora Balık (Merit Park)', 'restoran', 'Girne'],
        ['Shell Wi (Les Ambassadeur)', 'restoran', 'Girne'],
        ['Mezzo Restaurant (Kaya Palazzo)', 'restoran', 'Girne'],

        // Gazimağusa - Cafe
        ['Petek Pastanesi', 'cafe', 'Gazimağusa'],

        // Gazimağusa - Bar
        ['Castello Cyprus Bar', 'bar', 'Gazimağusa'],

        // Gazimağusa - Restoran
        ["Niazi's Restoran (Mağusa Şubesi)", 'restoran', 'Gazimağusa'],
        ['Eziç Mağusa Restoran', 'restoran', 'Gazimağusa'],
        ['Othello Meze Restaurant', 'restoran', 'Gazimağusa'],
        ['Aspava Restaurant & Bar', 'restoran', 'Gazimağusa'],
        ['Rouge 21', 'restoran', 'Gazimağusa'],
        ['The Palm House', 'restoran', 'Gazimağusa'],
        ['Minder Restoran', 'restoran', 'Gazimağusa'],
        ['Desdemona Meyhanesi', 'restoran', 'Gazimağusa'],
        ['Lokkum Restoran', 'restoran', 'Gazimağusa'],

        // Örnek kayıtlar (İskele, Güzelyurt, Lefke)
        ['Örnek Cafe - İskele', 'cafe', 'İskele', '', '', 'Örnek kayıt, panelden düzenleyin.'],
        ['Örnek Bar - Güzelyurt', 'bar', 'Güzelyurt', '', '', 'Örnek kayıt, panelden düzenleyin.'],
        ['Örnek Restoran - Lefke', 'restoran', 'Lefke', '', '', 'Örnek kayıt, panelden düzenleyin.'],
    ];

    $mekanEkle = $pdo->prepare(
        'INSERT INTO mekanlar (ad, kategori, sehir, adres, telefon, aciklama, aktif)
         VALUES (:ad, :kategori, :sehir, :adres, :telefon, :aciklama, 1)'
    );

    foreach ($mekanlar as $mekan) {
        $mekanEkle->execute([
            ':ad' => $mekan[0],
            ':kategori' => $mekan[1],
            ':sehir' => $mekan[2],
            ':adres' => $mekan[3] ?? '',
            ':telefon' => $mekan[4] ?? '',
            ':aciklama' => $mekan[5] ?? '',
        ]);
    }

    $adminEkle = $pdo->prepare('INSERT INTO yonetici (kullanici_adi, sifre_hash) VALUES (:kullanici_adi, :sifre_hash)');
    $adminEkle->execute([
        ':kullanici_adi' => 'admin',
        ':sifre_hash' => password_hash('admin123', PASSWORD_DEFAULT),
    ]);

    $kampanyaEkle = $pdo->prepare(
        'INSERT INTO kampanyalar (mekan_id, baslik, aciklama, tarih, aktif)
         VALUES (:mekan_id, :baslik, :aciklama, :tarih, 1)'
    );

    $mekanIdBul = $pdo->prepare('SELECT id FROM mekanlar WHERE ad = :ad LIMIT 1');

    $mekanIdBul->execute([':ad' => 'Petek Pastanesi']);
    $petekId = $mekanIdBul->fetchColumn();

    if ($petekId) {
        $kampanyaEkle->execute([
            ':mekan_id' => $petekId,
            ':baslik' => 'Bugüne Özel Tatlı İndirimi',
            ':aciklama' => 'Seçili tatlılarda gün boyu %15 indirim.',
            ':tarih' => date('Y-m-d'),
        ]);
    }

    $mekanIdBul->execute([':ad' => 'BarDuck Gastro Bar']);
    $barduckId = $mekanIdBul->fetchColumn();

    if ($barduckId) {
        $kampanyaEkle->execute([
            ':mekan_id' => $barduckId,
            ':baslik' => 'Sürekli Happy Hour',
            ':aciklama' => 'Hafta içi her gün 17:00 - 19:00 arası özel menü.',
            ':tarih' => '',
        ]);
    }
}

function guvenliMetin(?string $metin): string
{
    return htmlspecialchars((string) $metin, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function gecerliKategoriMi(string $kategori): bool
{
    return in_array($kategori, KATEGORILER, true);
}

function gecerliSehirMi(string $sehir): bool
{
    return in_array($sehir, SEHIRLER, true);
}
