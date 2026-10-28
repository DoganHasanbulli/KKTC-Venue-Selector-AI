<?php
date_default_timezone_set('Asia/Nicosia');

const KATEGORILER = ['cafe', 'bar', 'restoran'];
const SEHIRLER = ['Lefkoşa', 'Girne', 'Gazimağusa', 'İskele', 'Güzelyurt', 'Lefke'];

const KATEGORI_ETIKETLERI = [
    'cafe' => 'Cafe',
    'bar' => 'Bar',
    'restoran' => 'Restoran',
];

const POPULER_MEKANLAR = [
    'Cafe No:3',
    'Mado',
    'Özerlat Coffee',
    'Çember Restaurant',
    'Sofra Ev Yemekleri',
    'BarDuck Gastro Bar',
    "Niazi's Restaurant",
    'Grida Balık Restaurant',
    'Bellapais Gardens Restaurant',
    'Kybele Restaurant',
    'Escape Beach Club',
    'Chamada Beach Club',
    'Develi Kebap',
    'Petek Pastanesi',
    'Othello Meze Restaurant',
    "Niazi's Restoran (Mağusa Şubesi)",
    'Castello Cyprus Bar',
    'Balıkçı Limanı Restaurant',
    'Kolonaki',
    'Hüsrev Kebap Salonu',
    'Panagiouda',
    'Bedesten Restoran',
    'The House Cafe',
];

function pdoBaglantisi(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $dbDosyasi = __DIR__ . '/database/kktc.db';

    $dbKlasoru = dirname($dbDosyasi);
    if (!is_dir($dbKlasoru)) {
        mkdir($dbKlasoru, 0775, true);
    }

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
    /*
     * Örnek veriler.
     * İletişim bilgileri (telefon/adres) gösterim amaçlıdır; teslimden önce
     * admin panelinden güncel bilgilerle değiştirilmesi önerilir.
     */
    $mekanlar = [
        // ================= Lefkoşa - Cafe =================
        ['ad' => 'Cafe No:3', 'kategori' => 'cafe', 'sehir' => 'Lefkoşa', 'adres' => 'Dereboyu Caddesi No:3, Kumsal, Lefkoşa', 'telefon' => '+90 392 228 33 33', 'aciklama' => 'Dereboyu\'nun üçüncü nesil kahve durağı; filtre kahve ve ev yapımı tatlılarıyla ünlü.'],
        ['ad' => 'Mado', 'kategori' => 'cafe', 'sehir' => 'Lefkoşa', 'adres' => 'Dereboyu Caddesi, Lefkoşa', 'telefon' => '+90 392 227 40 40', 'aciklama' => 'Dondurma ve tatlı çeşitleriyle bilinen, kahvaltıdan gece geç saatlere açık kafe.'],
        ['ad' => 'Bibliotheque Café & Restaurant', 'kategori' => 'cafe', 'sehir' => 'Lefkoşa', 'adres' => 'Osmanpaşa Caddesi, Köşklüçiftlik, Lefkoşa', 'telefon' => '+90 392 227 12 12', 'aciklama' => 'Kitap rafları ve sakin atmosferiyle çalışmaya ve sohbete uygun kafe-restoran.'],
        ['ad' => 'The Gym Kafe', 'kategori' => 'cafe', 'sehir' => 'Lefkoşa', 'adres' => 'Dereboyu Caddesi, Lefkoşa', 'telefon' => '+90 392 228 90 90', 'aciklama' => 'Spor temalı dekoru ve sağlıklı menüsüyle gençlerin favorisi.'],
        ['ad' => 'Pralina Experience', 'kategori' => 'cafe', 'sehir' => 'Lefkoşa', 'adres' => 'Dereboyu Caddesi, Lefkoşa', 'telefon' => '+90 392 229 00 11', 'aciklama' => 'Çikolata ve tatlı odaklı deneyim mağazası; özel günler için ideal.'],
        ['ad' => 'Özerlat Coffee', 'kategori' => 'cafe', 'sehir' => 'Lefkoşa', 'adres' => 'Arasta Sokak, Suriçi, Lefkoşa', 'telefon' => '+90 392 227 55 66', 'aciklama' => 'Kıbrıs\'ın köklü kahve markası; geleneksel Kıbrıs kahvesi ve modern demlemeler.'],
        ['ad' => 'Gustav Roasting Co.', 'kategori' => 'cafe', 'sehir' => 'Lefkoşa', 'adres' => 'Dereboyu Caddesi, Lefkoşa', 'telefon' => '+90 392 228 71 71', 'aciklama' => 'Kendi çekirdeğini kavuran butik kahveci; pour-over ve espresso seçenekleri.'],
        ['ad' => 'Güngörmez Kahvehanesi', 'kategori' => 'cafe', 'sehir' => 'Lefkoşa', 'adres' => 'Güngörmez Sokak, Suriçi, Lefkoşa', 'telefon' => '+90 392 227 08 08', 'aciklama' => 'Suriçi\'nin tarihi dokusunda geleneksel kahvehane keyfi.'],

        // ================= Lefkoşa - Restoran =================
        ['ad' => 'Saraba Ev Yemekleri', 'kategori' => 'restoran', 'sehir' => 'Lefkoşa', 'adres' => 'Kızılbaş Sokak, Lefkoşa', 'telefon' => '+90 392 228 44 55', 'aciklama' => 'Günlük ev yemekleri ve memleket mutfağıyla öğle arası klasiği.'],
        ['ad' => 'Çember Restaurant', 'kategori' => 'restoran', 'sehir' => 'Lefkoşa', 'adres' => 'Girne Kapısı, Lefkoşa', 'telefon' => '+90 392 227 66 77', 'aciklama' => 'Kıbrıs mezeleri ve ızgara etleriyle tanınan köklü restoran.'],
        ['ad' => 'Budak Pastanesi', 'kategori' => 'restoran', 'sehir' => 'Lefkoşa', 'adres' => 'Dereboyu Caddesi, Lefkoşa', 'telefon' => '+90 392 228 11 22', 'aciklama' => 'Pastane-fırın; börek, poğaça ve tatlı çeşitleri.'],
        ['ad' => 'Bereket Fırını', 'kategori' => 'restoran', 'sehir' => 'Lefkoşa', 'adres' => 'Osmanpaşa Caddesi, Lefkoşa', 'telefon' => '+90 392 227 33 44', 'aciklama' => 'Odun ateşinde ekmek ve unlu mamuller.'],
        ['ad' => 'Hamur', 'kategori' => 'restoran', 'sehir' => 'Lefkoşa', 'adres' => 'Dereboyu Caddesi, Lefkoşa', 'telefon' => '+90 392 229 55 88', 'aciklama' => 'Pizza ve fırın yemekleri odaklı modern mekan.'],
        ['ad' => 'Mamülcüoğlu', 'kategori' => 'restoran', 'sehir' => 'Lefkoşa', 'adres' => 'Atatürk Caddesi, Lefkoşa', 'telefon' => '+90 392 228 22 33', 'aciklama' => 'Pide, lahmacun ve kebap çeşitleriyle bilinen esnaf lokantası.'],
        ['ad' => 'Ahmet Kanan Sütlü Mamulleri', 'kategori' => 'restoran', 'sehir' => 'Lefkoşa', 'adres' => 'Kızılbaş Sokak, Lefkoşa', 'telefon' => '+90 392 227 99 00', 'aciklama' => 'Sütlaç, macun ve geleneksel sütlü tatlılar.'],
        ['ad' => 'Cafe Baygün', 'kategori' => 'restoran', 'sehir' => 'Lefkoşa', 'adres' => 'Ledra Caddesi, Lefkoşa', 'telefon' => '+90 392 228 88 99', 'aciklama' => 'Sınır kapısı yakınında buluşma noktası; kahvaltı ve atıştırmalık.'],

        // ================= Girne - Bar =================
        ['ad' => 'BarDuck Gastro Bar', 'kategori' => 'bar', 'sehir' => 'Girne', 'adres' => 'Ziya Rızkı Caddesi, Girne', 'telefon' => '+90 392 815 11 22', 'aciklama' => 'Gastro pub menüsü ve geniş bira seçkisiyle Girne limanına yakın.'],
        ['ad' => 'Bond Gastro Pub', 'kategori' => 'bar', 'sehir' => 'Girne', 'adres' => 'Eski Liman, Girne', 'telefon' => '+90 392 815 33 44', 'aciklama' => 'Canlı müzik ve kokteylleriyle hareketli atmosfer.'],
        ['ad' => 'Ego Bar', 'kategori' => 'bar', 'sehir' => 'Girne', 'adres' => 'Ziya Rızkı Caddesi, Girne', 'telefon' => '+90 392 815 55 66', 'aciklama' => 'Gece geç saatlere kadar açık, DJ performanslarıyla bilinen bar.'],
        ['ad' => 'Sky Lounge & Bar', 'kategori' => 'bar', 'sehir' => 'Girne', 'adres' => 'Mete Adanır Stadyumu yakını, Girne', 'telefon' => '+90 392 815 77 88', 'aciklama' => 'Şehir manzaralı terası ve imza kokteylleriyle roof bar.'],

        // ================= Girne - Restoran =================
        ['ad' => "Niazi's Restaurant", 'kategori' => 'restoran', 'sehir' => 'Girne', 'adres' => 'Ziya Rızkı Caddesi, Girne', 'telefon' => '+90 392 815 21 40', 'aciklama' => 'Kıbrıs mutfağının simgesi; mezeleri ve kebaplarıyla ünlü.'],
        ['ad' => 'Archway Restaurant', 'kategori' => 'restoran', 'sehir' => 'Girne', 'adres' => 'Eski Liman, Girne', 'telefon' => '+90 392 815 41 41', 'aciklama' => 'Liman manzaralı terasıyla balık ve deniz ürünleri.'],
        ['ad' => 'Epic Burger Girne', 'kategori' => 'restoran', 'sehir' => 'Girne', 'adres' => 'Karaoğlanoğlu Caddesi, Girne', 'telefon' => '+90 392 815 60 60', 'aciklama' => 'El yapımı burger ve patates çeşitleri.'],
        ['ad' => 'Driftwood', 'kategori' => 'restoran', 'sehir' => 'Girne', 'adres' => 'Ziya Rızkı Caddesi, Girne', 'telefon' => '+90 392 815 42 42', 'aciklama' => 'Rustik dekoru ve dünya mutfağı menüsüyle samimi mekan.'],
        ['ad' => 'Bellapais Gardens Restaurant', 'kategori' => 'restoran', 'sehir' => 'Girne', 'adres' => 'Bellapais Manastırı yolu, Girne', 'telefon' => '+90 392 815 60 77', 'aciklama' => 'Abbey manzarası eşliğinde Akdeniz mutfağı.'],
        ['ad' => 'Kybele Restaurant', 'kategori' => 'restoran', 'sehir' => 'Girne', 'adres' => 'Beşparmak Dağları yolu, Girne', 'telefon' => '+90 392 815 27 60', 'aciklama' => 'Beşparmak Dağları manzaralı, tarihi atmosferli özel mekan.'],
        ['ad' => 'Chamada Beach Club', 'kategori' => 'restoran', 'sehir' => 'Girne', 'adres' => 'Alsancak, Girne', 'telefon' => '+90 392 815 74 74', 'aciklama' => 'Denize sıfır beach club; gündüz plaj, akşam müzik.'],
        ['ad' => 'Escape Beach Club', 'kategori' => 'restoran', 'sehir' => 'Girne', 'adres' => 'Alsancak, Girne', 'telefon' => '+90 392 815 71 71', 'aciklama' => 'Ünlü kumsalıyla yaz aylarının buluşma noktası.'],
        ['ad' => 'Silver Rocks Restaurant', 'kategori' => 'restoran', 'sehir' => 'Girne', 'adres' => 'Lapta, Girne', 'telefon' => '+90 392 815 88 88', 'aciklama' => 'Deniz kenarında et ve balık menüsü.'],
        ['ad' => 'Da Vinci Irina', 'kategori' => 'restoran', 'sehir' => 'Girne', 'adres' => 'Çatalköy, Girne', 'telefon' => '+90 392 815 90 90', 'aciklama' => 'İtalyan mutfağı ve geniş makarna seçenekleri.'],
        ['ad' => 'Take Eat Easy', 'kategori' => 'restoran', 'sehir' => 'Girne', 'adres' => 'Karakum, Girne', 'telefon' => '+90 392 815 30 30', 'aciklama' => 'Hızlı servis; pizza ve sandviç odaklı.'],
        ['ad' => 'Grida Balık Restaurant', 'kategori' => 'restoran', 'sehir' => 'Girne', 'adres' => 'Girne Limanı, Girne', 'telefon' => '+90 392 815 15 15', 'aciklama' => 'Taze balık ve deniz mahsulleri; liman manzarası.'],
        ['ad' => 'Peris Fish Restaurant', 'kategori' => 'restoran', 'sehir' => 'Girne', 'adres' => 'Çatalköy, Girne', 'telefon' => '+90 392 815 40 40', 'aciklama' => 'Deniz ürünleri ağırlıklı akşam yemeği.'],
        ['ad' => 'Set Fish', 'kategori' => 'restoran', 'sehir' => 'Girne', 'adres' => 'Karaoğlanoğlu, Girne', 'telefon' => '+90 392 815 22 11', 'aciklama' => 'Balık restoranı; mevsimsel menü.'],
        ['ad' => 'The Ambiance', 'kategori' => 'restoran', 'sehir' => 'Girne', 'adres' => 'Karakum, Girne', 'telefon' => '+90 392 815 23 32', 'aciklama' => 'Modern dekor ve füzyon menü.'],
        ['ad' => 'Nazende Ocakbaşı', 'kategori' => 'restoran', 'sehir' => 'Girne', 'adres' => 'Ziya Rızkı Caddesi, Girne', 'telefon' => '+90 392 815 66 00', 'aciklama' => 'Geleneksel ocakbaşı ve kebap çeşitleri.'],
        ['ad' => 'Kömür Steak & Ocakbaşı', 'kategori' => 'restoran', 'sehir' => 'Girne', 'adres' => 'Girne', 'telefon' => '+90 392 815 18 18', 'aciklama' => 'Kömür ateşinde et ve steak menüsü.'],
        ['ad' => 'Develi Kebap', 'kategori' => 'restoran', 'sehir' => 'Girne', 'adres' => 'Girne', 'telefon' => '+90 392 815 28 28', 'aciklama' => 'Gaziantep usulü kebap ve lahmacun.'],
        ['ad' => 'The Dragon', 'kategori' => 'restoran', 'sehir' => 'Girne', 'adres' => 'Girne', 'telefon' => '+90 392 815 19 19', 'aciklama' => 'Çin ve Uzak Doğu mutfağı.'],
        ['ad' => 'JOYA Italiano & Steak House', 'kategori' => 'restoran', 'sehir' => 'Girne', 'adres' => 'Girne', 'telefon' => '+90 392 815 34 34', 'aciklama' => 'İtalyan mutfağı ve steakhouse menüsü.'],
        ['ad' => 'Nima Restaurant & Lounge Bar', 'kategori' => 'restoran', 'sehir' => 'Girne', 'adres' => 'Girne', 'telefon' => '+90 392 815 44 44', 'aciklama' => 'Lounge atmosferinde akşam yemeği.'],
        ['ad' => 'Tahta Palas Kahvaltı', 'kategori' => 'restoran', 'sehir' => 'Girne', 'adres' => 'Girne', 'telefon' => '+90 392 815 50 50', 'aciklama' => 'Serpme kahvaltı uzmanı.'],
        ['ad' => 'Fora Balık (Merit Park)', 'kategori' => 'restoran', 'sehir' => 'Girne', 'adres' => 'Merit Park Hotel, Karakum, Girne', 'telefon' => '+90 392 815 39 00', 'aciklama' => 'Su kenarında balık restoranı.'],
        ['ad' => 'Shell Wi (Les Ambassadeur)', 'kategori' => 'restoran', 'sehir' => 'Girne', 'adres' => 'Les Ambassadeur, Girne', 'telefon' => '+90 392 815 70 70', 'aciklama' => 'Deniz ürünleri ve dünya mutfağı.'],
        ['ad' => 'Mezzo Restaurant (Kaya Palazzo)', 'kategori' => 'restoran', 'sehir' => 'Girne', 'adres' => 'Kaya Palazzo, Alsancak, Girne', 'telefon' => '+90 392 815 90 10', 'aciklama' => 'Şık ortamda Akdeniz mutfağı.'],

        // ================= Gazimağusa - Cafe =================
        ['ad' => 'Petek Pastanesi', 'kategori' => 'cafe', 'sehir' => 'Gazimağusa', 'adres' => 'Salamis Yolu, Gazimağusa', 'telefon' => '+90 392 365 11 11', 'aciklama' => 'Kıbrıs\'ın sevilen pastanesi; pastalar, börekler ve tatlı çeşitleri.'],

        // ================= Gazimağusa - Bar =================
        ['ad' => 'Castello Cyprus Bar', 'kategori' => 'bar', 'sehir' => 'Gazimağusa', 'adres' => 'Eski Şehir, Gazimağusa', 'telefon' => '+90 392 365 22 22', 'aciklama' => 'Tarihi surlar içinde bar; kokteyl ve canlı müzik.'],

        // ================= Gazimağusa - Restoran =================
        ['ad' => "Niazi's Restoran (Mağusa Şubesi)", 'kategori' => 'restoran', 'sehir' => 'Gazimağusa', 'adres' => 'Salamis Yolu, Gazimağusa', 'telefon' => '+90 392 365 30 20', 'aciklama' => 'Niazi\'s kalitesi; meze ve kebap çeşitleri.'],
        ['ad' => 'Eziç Mağusa Restoran', 'kategori' => 'restoran', 'sehir' => 'Gazimağusa', 'adres' => 'Gazimağusa', 'telefon' => '+90 392 365 33 33', 'aciklama' => 'Meze ve ızgara çeşitleri.'],
        ['ad' => 'Othello Meze Restaurant', 'kategori' => 'restoran', 'sehir' => 'Gazimağusa', 'adres' => 'Namık Kemal Meydanı, Gazimağusa', 'telefon' => '+90 392 365 44 44', 'aciklama' => 'Shakespeare\'in izinde; zengin meze sofrası.'],
        ['ad' => 'Aspava Restaurant & Bar', 'kategori' => 'restoran', 'sehir' => 'Gazimağusa', 'adres' => 'Gazimağusa', 'telefon' => '+90 392 365 55 55', 'aciklama' => 'Anadolu mutfağı ve bar.'],
        ['ad' => 'Rouge 21', 'kategori' => 'restoran', 'sehir' => 'Gazimağusa', 'adres' => 'Gazimağusa', 'telefon' => '+90 392 365 60 60', 'aciklama' => 'Modern restoran-bar; akşam etkinlikleri.'],
        ['ad' => 'The Palm House', 'kategori' => 'restoran', 'sehir' => 'Gazimağusa', 'adres' => 'Gazimağusa', 'telefon' => '+90 392 365 61 61', 'aciklama' => 'Palmiyeler altında açık hava yemekleri.'],
        ['ad' => 'Minder Restoran', 'kategori' => 'restoran', 'sehir' => 'Gazimağusa', 'adres' => 'Gazimağusa', 'telefon' => '+90 392 365 62 62', 'aciklama' => 'Yer sofrası ve geleneksel Kıbrıs yemekleri.'],
        ['ad' => 'Desdemona Meyhanesi', 'kategori' => 'restoran', 'sehir' => 'Gazimağusa', 'adres' => 'Gazimağusa', 'telefon' => '+90 392 365 63 63', 'aciklama' => 'Meze ve meyhane kültürü.'],
        ['ad' => 'Lokkum Restoran', 'kategori' => 'restoran', 'sehir' => 'Gazimağusa', 'adres' => 'Gazimağusa', 'telefon' => '+90 392 365 64 64', 'aciklama' => 'Gün boyu servis; kahvaltı ve akşam yemeği.'],

        // ================= İskele =================
        ['ad' => 'Balıkçı Limanı Restaurant', 'kategori' => 'restoran', 'sehir' => 'İskele', 'adres' => 'Balıkçı Limanı, Boğaz, İskele', 'telefon' => '+90 392 371 11 11', 'aciklama' => 'İskele Balıkçı Limanı\'nda taze balık ve deniz manzarası.'],
        ['ad' => 'Yeni İskele Sahil Cafe', 'kategori' => 'cafe', 'sehir' => 'İskele', 'adres' => 'Sahil Yolu, Yeni İskele', 'telefon' => '+90 392 371 22 22', 'aciklama' => 'Sahilde serinleten kafe; kahvaltı ve tatlı çeşitleri.'],
        ['ad' => 'Boğaz Beach Bar', 'kategori' => 'bar', 'sehir' => 'İskele', 'adres' => 'Boğaz, İskele', 'telefon' => '+90 392 371 33 33', 'aciklama' => 'Deniz kenarında akşamüstü kokteylleri.'],

        // ================= Güzelyurt =================
        ['ad' => 'Güzelyurt Çarşı Cafe', 'kategori' => 'cafe', 'sehir' => 'Güzelyurt', 'adres' => 'Ecevit Caddesi, Güzelyurt', 'telefon' => '+90 392 714 11 11', 'aciklama' => 'Narenciye diyarının çarşı kahvesi; taze sıkılmış portakal suyu.'],
        ['ad' => 'Güzelyurt Bahçe Restaurant', 'kategori' => 'restoran', 'sehir' => 'Güzelyurt', 'adres' => 'Güzelyurt', 'telefon' => '+90 392 714 22 22', 'aciklama' => 'Bahçe içinde ev yemekleri ve ızgara çeşitleri.'],
        ['ad' => 'Narenciye Bahçesi Bar', 'kategori' => 'bar', 'sehir' => 'Güzelyurt', 'adres' => 'Güzelyurt', 'telefon' => '+90 392 714 33 33', 'aciklama' => 'Narenciye bahçeleri arasında sakin bir bar.'],

        // ================= Lefke =================
        ['ad' => 'Lefke Sahil Restoran', 'kategori' => 'restoran', 'sehir' => 'Lefke', 'adres' => 'Gemikonağı Sahili, Lefke', 'telefon' => '+90 392 727 11 11', 'aciklama' => 'Deniz kenarında balık ve ızgara çeşitleri.'],
        ['ad' => 'Lefke Merkez Cafe', 'kategori' => 'cafe', 'sehir' => 'Lefke', 'adres' => 'Cengiz Topel Caddesi, Lefke', 'telefon' => '+90 392 727 22 22', 'aciklama' => 'Lefke merkezde sıcak bir mola; kahve ve tatlı.'],
        ['ad' => 'Gemikonağı Sunset Bar', 'kategori' => 'bar', 'sehir' => 'Lefke', 'adres' => 'Gemikonağı, Lefke', 'telefon' => '+90 392 727 33 33', 'aciklama' => 'Gün batımı manzaralı sahil barı.'],

        // ================= Lefkoşa - Ek kayıtlar =================
        ['ad' => 'Kaffa Coffee', 'kategori' => 'cafe', 'sehir' => 'Lefkoşa', 'adres' => 'Dereboyu Caddesi, Lefkoşa', 'telefon' => '+90 392 228 45 45', 'aciklama' => 'Sade espresso ve filtre kahveye odaklanan küçük butik kahveci.'],
        ['ad' => 'Café Mitre', 'kategori' => 'cafe', 'sehir' => 'Lefkoşa', 'adres' => 'Arasta Sokak, Suriçi, Lefkoşa', 'telefon' => '+90 392 227 21 21', 'aciklama' => 'Surlariçi\'nin tarihi meydanında, gölgeli avlusunda kahve molası.'],
        ['ad' => 'Nicosia Coffee Roasters', 'kategori' => 'cafe', 'sehir' => 'Lefkoşa', 'adres' => 'Osmanpaşa Caddesi, Lefkoşa', 'telefon' => '+90 392 227 76 76', 'aciklama' => 'Kendi çekirdeğini kavuran, demleme yöntemlerine odaklanan kahveci.'],
        ['ad' => 'Sugar & Bean', 'kategori' => 'cafe', 'sehir' => 'Lefkoşa', 'adres' => 'Köşklüçiftlik, Lefkoşa', 'telefon' => '+90 392 228 34 34', 'aciklama' => 'Hafta sonu serpme kahvaltı ve ev yapımı pastalarıyla bilinen kafe.'],
        ['ad' => 'Viyana Kafe', 'kategori' => 'cafe', 'sehir' => 'Lefkoşa', 'adres' => 'Atatürk Caddesi, Lefkoşa', 'telefon' => '+90 392 228 63 63', 'aciklama' => 'Kahvaltı, poğaça ve sıcak içeceklerle sabahın vazgeçilmezi.'],
        ['ad' => 'Corner Coffee', 'kategori' => 'cafe', 'sehir' => 'Lefkoşa', 'adres' => 'Suriçi, Lefkoşa', 'telefon' => '+90 392 227 47 47', 'aciklama' => 'Hızlı servis, paket kahve ve atıştırmalık seçenekleri.'],
        ['ad' => 'Barrel Pub', 'kategori' => 'bar', 'sehir' => 'Lefkoşa', 'adres' => 'Dereboyu Caddesi, Lefkoşa', 'telefon' => '+90 392 228 82 82', 'aciklama' => 'Geniş bira menüsü ve pub mutfağıyla gece buluşmaları.'],
        ['ad' => 'Havana Bar', 'kategori' => 'bar', 'sehir' => 'Lefkoşa', 'adres' => 'Kumsal, Lefkoşa', 'telefon' => '+90 392 228 91 91', 'aciklama' => 'Kokteyller, plak müziği ve sıcak atmosfer.'],
        ['ad' => 'Route Lounge', 'kategori' => 'bar', 'sehir' => 'Lefkoşa', 'adres' => 'Atatürk Caddesi, Lefkoşa', 'telefon' => '+90 392 228 74 74', 'aciklama' => 'Hafta sonu canlı performanslar ve sahne etkinlikleri.'],
        ['ad' => 'The Basement', 'kategori' => 'bar', 'sehir' => 'Lefkoşa', 'adres' => 'Suriçi, Lefkoşa', 'telefon' => '+90 392 227 39 39', 'aciklama' => 'Sokak altında, samimi ve düşük ışıklı küçük bar.'],
        ['ad' => 'Kitchen Kitchen', 'kategori' => 'restoran', 'sehir' => 'Lefkoşa', 'adres' => 'Dereboyu Caddesi, Lefkoşa', 'telefon' => '+90 392 229 62 62', 'aciklama' => 'Günlük değişen menüsüyle modern kitchen ve meyhane.'],
        ['ad' => 'Zeki Ocakbaşı', 'kategori' => 'restoran', 'sehir' => 'Lefkoşa', 'adres' => 'Atatürk Caddesi, Lefkoşa', 'telefon' => '+90 392 228 57 57', 'aciklama' => 'Mangal ve ocakbaşı; şiş, köfte ve pide çeşitleri.'],
        ['ad' => 'Meydan Kebap', 'kategori' => 'restoran', 'sehir' => 'Lefkoşa', 'adres' => 'Girne Kapısı, Lefkoşa', 'telefon' => '+90 392 227 84 84', 'aciklama' => 'Klasik kebaplar, dürüm ve lavaş çeşitleri.'],
        ['ad' => 'Golden Phoenix', 'kategori' => 'restoran', 'sehir' => 'Lefkoşa', 'adres' => 'Köşklüçiftlik, Lefkoşa', 'telefon' => '+90 392 227 55 11', 'aciklama' => 'Çin ve Uzak Doğu mutfağı; wok, noodle ve tavuk yemekleri.'],
        ['ad' => 'Alora Restaurant', 'kategori' => 'restoran', 'sehir' => 'Lefkoşa', 'adres' => 'Kumsal, Lefkoşa', 'telefon' => '+90 392 229 71 71', 'aciklama' => 'Akdeniz mutfağı, salatalar ve akşam özel menüsü.'],
        ['ad' => 'Saray Lokantası', 'kategori' => 'restoran', 'sehir' => 'Lefkoşa', 'adres' => 'Osmanpaşa Caddesi, Lefkoşa', 'telefon' => '+90 392 227 65 65', 'aciklama' => 'Uygun fiyatlı ev yemekleri ve günlük çorba.'],
        ['ad' => 'Sofra Ev Yemekleri', 'kategori' => 'restoran', 'sehir' => 'Lefkoşa', 'adres' => 'Şehit Mustafa Ruso Caddesi, Lefkoşa', 'telefon' => '+90 392 228 36 36', 'aciklama' => 'Geleneksel Kıbrıs mutfağı; şeftali kebabı ve mırra çorbası.'],

        // ================= Girne - Ek kayıtlar =================
        ['ad' => 'Coffee Island Girne', 'kategori' => 'cafe', 'sehir' => 'Girne', 'adres' => 'Eski Liman, Girne', 'telefon' => '+90 392 815 92 92', 'aciklama' => 'Liman manzarası eşliğinde kahve ve tatlı.'],
        ['ad' => 'Liman Kahvesi', 'kategori' => 'cafe', 'sehir' => 'Girne', 'adres' => 'Girne Limanı, Girne', 'telefon' => '+90 392 815 64 64', 'aciklama' => 'Kale ve tekne manzaralı, sahil kenarında kahve.'],
        ['ad' => 'Bellapais Kafe', 'kategori' => 'cafe', 'sehir' => 'Girne', 'adres' => 'Bellapais, Girne', 'telefon' => '+90 392 815 68 68', 'aciklama' => 'Manastırın gölgesinde, bahçe içinde sakin kafe.'],
        ['ad' => 'Kervansaray Cafe', 'kategori' => 'cafe', 'sehir' => 'Girne', 'adres' => 'Ziya Rızkı Caddesi, Girne', 'telefon' => '+90 392 815 59 59', 'aciklama' => 'Klasik Kıbrıs kahvesi ve ev tatlıları.'],
        ['ad' => 'Deniz Kafe', 'kategori' => 'cafe', 'sehir' => 'Girne', 'adres' => 'Karaoğlanoğlu, Girne', 'telefon' => '+90 392 815 47 47', 'aciklama' => 'Deniz kenarında kahvaltı ve soğuk içecek.'],
        ['ad' => 'Pivo Bar', 'kategori' => 'bar', 'sehir' => 'Girne', 'adres' => 'Ziya Rızkı Caddesi, Girne', 'telefon' => '+90 392 815 52 52', 'aciklama' => 'Yerel ve ithal biraların yanında pub atıştırmalıkları.'],
        ['ad' => 'Sketch Bar', 'kategori' => 'bar', 'sehir' => 'Girne', 'adres' => 'Eski Liman, Girne', 'telefon' => '+90 392 815 35 35', 'aciklama' => 'Liman üzerinde, gece boyunca DJ setleri.'],
        ['ad' => 'Anchor Bar', 'kategori' => 'bar', 'sehir' => 'Girne', 'adres' => 'Girne Limanı, Girne', 'telefon' => '+90 392 815 26 26', 'aciklama' => 'Denizcilerin uğrak noktası; bira ve müzik.'],
        ['ad' => 'Jellyfish Beach Bar', 'kategori' => 'bar', 'sehir' => 'Girne', 'adres' => 'Alsancak, Girne', 'telefon' => '+90 392 815 79 79', 'aciklama' => 'Plaj üstünde gündüz güneş, akşam kokteyl.'],
        ['ad' => 'Ephesus Restaurant', 'kategori' => 'restoran', 'sehir' => 'Girne', 'adres' => 'Girne', 'telefon' => '+90 392 815 48 48', 'aciklama' => 'Kebap, meze ve Anadolu sofrası.'],
        ['ad' => 'Olive Tree', 'kategori' => 'restoran', 'sehir' => 'Girne', 'adres' => 'Çatalköy, Girne', 'telefon' => '+90 392 815 73 73', 'aciklama' => 'Bahçe içinde Akdeniz mutfağı ve zeytinyağlılar.'],
        ['ad' => 'Denizci Balık', 'kategori' => 'restoran', 'sehir' => 'Girne', 'adres' => 'Karaoğlanoğlu, Girne', 'telefon' => '+90 392 815 45 45', 'aciklama' => 'Günlük tutulan balık ve ızgara çeşitleri.'],
        ['ad' => 'Kebapçı Halil Usta', 'kategori' => 'restoran', 'sehir' => 'Girne', 'adres' => 'Girne', 'telefon' => '+90 392 815 56 56', 'aciklama' => 'Adana, urfa ve şiş kebapta iddialı.'],
        ['ad' => 'Bella Vista', 'kategori' => 'restoran', 'sehir' => 'Girne', 'adres' => 'Alsancak, Girne', 'telefon' => '+90 392 815 61 61', 'aciklama' => 'Deniz manzaralı teras; akşam yemeği için ideal.'],
        ['ad' => 'Picasso Restaurant', 'kategori' => 'restoran', 'sehir' => 'Girne', 'adres' => 'Girne', 'telefon' => '+90 392 815 43 43', 'aciklama' => 'Sanat temalı dekor ve dünya mutfağı.'],

        // ================= Gazimağusa - Ek kayıtlar =================
        ['ad' => 'Otto Café', 'kategori' => 'cafe', 'sehir' => 'Gazimağusa', 'adres' => 'Eski Şehir, Gazimağusa', 'telefon' => '+90 392 365 78 78', 'aciklama' => 'Surların içinde, avlusu ferah olan sakin kafe.'],
        ['ad' => 'Papatya Cafe', 'kategori' => 'cafe', 'sehir' => 'Gazimağusa', 'adres' => 'Namık Kemal Meydanı, Gazimağusa', 'telefon' => '+90 392 365 66 66', 'aciklama' => 'Meydan manzaralı; kahve ve dondurma.'],
        ['ad' => 'Mavi Kupa Cafe', 'kategori' => 'cafe', 'sehir' => 'Gazimağusa', 'adres' => 'Salamis Yolu, Gazimağusa', 'telefon' => '+90 392 365 71 71', 'aciklama' => 'Öğrencilerin uğrak noktası; uygun fiyatlı menü ve Wi-Fi.'],
        ['ad' => 'Spring Garden Cafe', 'kategori' => 'cafe', 'sehir' => 'Gazimağusa', 'adres' => 'Gazimağusa', 'telefon' => '+90 392 365 74 74', 'aciklama' => 'Bahçeli, doğallığı korunmuş ferah kafe.'],
        ['ad' => 'Stone Bar', 'kategori' => 'bar', 'sehir' => 'Gazimağusa', 'adres' => 'Eski Şehir, Gazimağusa', 'telefon' => '+90 392 365 81 81', 'aciklama' => 'Taş dokulu surlar içinde kokteyl barı.'],
        ['ad' => 'Casablanca Bar', 'kategori' => 'bar', 'sehir' => 'Gazimağusa', 'adres' => 'Gazimağusa', 'telefon' => '+90 392 365 84 84', 'aciklama' => 'Akşam canlı müzik ve keyifli içecekler.'],
        ['ad' => 'Han Yemek Evi', 'kategori' => 'restoran', 'sehir' => 'Gazimağusa', 'adres' => 'Namık Kemal, Gazimağusa', 'telefon' => '+90 392 365 87 87', 'aciklama' => 'Tarihi handa ev usulü yemekler.'],
        ['ad' => 'Mercan Balık', 'kategori' => 'restoran', 'sehir' => 'Gazimağusa', 'adres' => 'Gazimağusa', 'telefon' => '+90 392 365 89 89', 'aciklama' => 'Deniz ürünleri ve günlük taze balık.'],
        ['ad' => 'Tulip Restoran', 'kategori' => 'restoran', 'sehir' => 'Gazimağusa', 'adres' => 'Gazimağusa', 'telefon' => '+90 392 365 91 91', 'aciklama' => 'Şık salon; akşam yemeği ve özel gün organizasyonları.'],
        ['ad' => 'Kebapçı Baba', 'kategori' => 'restoran', 'sehir' => 'Gazimağusa', 'adres' => 'Salamis Yolu, Gazimağusa', 'telefon' => '+90 392 365 93 93', 'aciklama' => 'Odun ateşinde kebap ve pide.'],
        ['ad' => 'Dragon Mağusa', 'kategori' => 'restoran', 'sehir' => 'Gazimağusa', 'adres' => 'Gazimağusa', 'telefon' => '+90 392 365 95 95', 'aciklama' => 'Uzak Doğu mutfağı; sushi, wok ve noodle.'],

        // ================= İskele - Ek kayıtlar =================
        ['ad' => 'İskele Kahvecisi', 'kategori' => 'cafe', 'sehir' => 'İskele', 'adres' => 'Yeni İskele, İskele', 'telefon' => '+90 392 371 44 44', 'aciklama' => 'Merkezde sade kahve ve tatlı molası.'],
        ['ad' => 'Sahil Kafe', 'kategori' => 'cafe', 'sehir' => 'İskele', 'adres' => 'Boğaz, İskele', 'telefon' => '+90 392 371 47 47', 'aciklama' => 'Deniz kenarında kahvaltı ve soğuk içecek.'],
        ['ad' => 'Liman Bar', 'kategori' => 'bar', 'sehir' => 'İskele', 'adres' => 'Balıkçı Limanı, İskele', 'telefon' => '+90 392 371 61 61', 'aciklama' => 'Liman üzerinde gün batımı içecekleri.'],
        ['ad' => 'İskele Meydan Bar', 'kategori' => 'bar', 'sehir' => 'İskele', 'adres' => 'Yeni İskele, İskele', 'telefon' => '+90 392 371 64 64', 'aciklama' => 'Meydanda, hareketli gece atmosferi.'],
        ['ad' => 'Deniz Feneri Restoran', 'kategori' => 'restoran', 'sehir' => 'İskele', 'adres' => 'Boğaz, İskele', 'telefon' => '+90 392 371 51 51', 'aciklama' => 'Manzaralı terasta balık ve ızgara.'],
        ['ad' => 'İskele Ocakbaşı', 'kategori' => 'restoran', 'sehir' => 'İskele', 'adres' => 'Yeni İskele, İskele', 'telefon' => '+90 392 371 54 54', 'aciklama' => 'Ocakbaşı keyfi; şiş, köfte ve pide.'],
        ['ad' => 'Boğaz Balık', 'kategori' => 'restoran', 'sehir' => 'İskele', 'adres' => 'Boğaz, İskele', 'telefon' => '+90 392 371 57 57', 'aciklama' => 'Günlük balık ve zengin meze tabağı.'],

        // ================= Güzelyurt - Ek kayıtlar =================
        ['ad' => 'Güzelyurt Pastanesi', 'kategori' => 'cafe', 'sehir' => 'Güzelyurt', 'adres' => 'Ecevit Caddesi, Güzelyurt', 'telefon' => '+90 392 714 44 44', 'aciklama' => 'Börek, poğaça ve taze sıkılmış meyve suyu.'],
        ['ad' => 'Meydan Kahve', 'kategori' => 'cafe', 'sehir' => 'Güzelyurt', 'adres' => 'Güzelyurt Meydanı, Güzelyurt', 'telefon' => '+90 392 714 47 47', 'aciklama' => 'Meydanda çay ve kahve molası.'],
        ['ad' => 'Üniversite Kafe', 'kategori' => 'cafe', 'sehir' => 'Güzelyurt', 'adres' => 'Güzelyurt', 'telefon' => '+90 392 714 51 51', 'aciklama' => 'Öğrencilere uygun fiyatlı menü ve ücretsiz Wi-Fi.'],
        ['ad' => 'Meydan Bar', 'kategori' => 'bar', 'sehir' => 'Güzelyurt', 'adres' => 'Güzelyurt Meydanı, Güzelyurt', 'telefon' => '+90 392 714 54 54', 'aciklama' => 'Akşamları canlı müzik ve sohbet.'],
        ['ad' => 'Güzelyurt Ocakbaşı', 'kategori' => 'restoran', 'sehir' => 'Güzelyurt', 'adres' => 'Güzelyurt', 'telefon' => '+90 392 714 57 57', 'aciklama' => 'Izgara ve kebap çeşitleri.'],
        ['ad' => 'Çarşı Kebap', 'kategori' => 'restoran', 'sehir' => 'Güzelyurt', 'adres' => 'Ecevit Caddesi, Güzelyurt', 'telefon' => '+90 392 714 61 61', 'aciklama' => 'Çarşı içinde hızlı ve lezzetli kebap.'],
        ['ad' => 'Narenciye Sofra', 'kategori' => 'restoran', 'sehir' => 'Güzelyurt', 'adres' => 'Güzelyurt', 'telefon' => '+90 392 714 64 64', 'aciklama' => 'Bölge ürünlerinden hazırlanan ev yemekleri.'],

        // ================= Lefke - Ek kayıtlar =================
        ['ad' => 'Lefke Pastanesi', 'kategori' => 'cafe', 'sehir' => 'Lefke', 'adres' => 'Cengiz Topel Caddesi, Lefke', 'telefon' => '+90 392 727 44 44', 'aciklama' => 'Taze poğaça, börek ve sütlü tatlılar.'],
        ['ad' => 'Zeybek Kafe', 'kategori' => 'cafe', 'sehir' => 'Lefke', 'adres' => 'Lefke', 'telefon' => '+90 392 727 47 47', 'aciklama' => 'Sakin atmosferde kahve ve kitap okuma köşesi.'],
        ['ad' => 'Zeybek Bar', 'kategori' => 'bar', 'sehir' => 'Lefke', 'adres' => 'Lefke', 'telefon' => '+90 392 727 51 51', 'aciklama' => 'Yerel müzik eşliğinde akşam içecekleri.'],
        ['ad' => 'Gemikonağı Balık Evi', 'kategori' => 'restoran', 'sehir' => 'Lefke', 'adres' => 'Gemikonağı, Lefke', 'telefon' => '+90 392 727 54 54', 'aciklama' => 'Sahilde taze balık ve meze tabağı.'],
        ['ad' => 'Lefke Ev Yemekleri', 'kategori' => 'restoran', 'sehir' => 'Lefke', 'adres' => 'Lefke', 'telefon' => '+90 392 727 57 57', 'aciklama' => 'Ev usulü yemekler ve her gün değişen menü.'],
        ['ad' => 'Ocakbaşı Lefke', 'kategori' => 'restoran', 'sehir' => 'Lefke', 'adres' => 'Cengiz Topel Caddesi, Lefke', 'telefon' => '+90 392 727 61 61', 'aciklama' => 'Izgara, şiş ve pide çeşitleri.'],

        // ================= Popüler Mekanlar =================
        // Lefkoşa
        ['ad' => 'Kolonaki', 'kategori' => 'restoran', 'sehir' => 'Lefkoşa', 'adres' => 'Kızılbaş Sokak, Suriçi, Lefkoşa', 'telefon' => '+90 392 228 66 77', 'aciklama' => 'Şehir merkezinin sevilen Yunan-Kıbrıs meze ve ızgara lokantası.'],
        ['ad' => 'Hüsrev Kebap Salonu', 'kategori' => 'restoran', 'sehir' => 'Lefkoşa', 'adres' => 'Osmanpaşa Caddesi, Lefkoşa', 'telefon' => '+90 392 227 18 18', 'aciklama' => 'Uzun yıllardır aynı yerde, odun ateşinde kebap.'],
        ['ad' => 'Panagiouda', 'kategori' => 'restoran', 'sehir' => 'Lefkoşa', 'adres' => 'Arasta Sokak, Suriçi, Lefkoşa', 'telefon' => '+90 392 227 19 19', 'aciklama' => 'Tarihi handa geleneksel Kıbrıs mutfağı.'],
        ['ad' => 'Çarşı Kahvesi', 'kategori' => 'cafe', 'sehir' => 'Lefkoşa', 'adres' => 'Suriçi, Lefkoşa', 'telefon' => '+90 392 227 23 23', 'aciklama' => 'Surlariçi çarısında geleneksel Kıbrıs kahvesi.'],
        ['ad' => 'Bayraktar Ocakbaşı', 'kategori' => 'restoran', 'sehir' => 'Lefkoşa', 'adres' => 'Atatürk Caddesi, Lefkoşa', 'telefon' => '+90 392 228 58 58', 'aciklama' => 'Kuzu şiş ve köfteyle ünlü ocakbaşı.'],
        ['ad' => 'İnci Pastanesi', 'kategori' => 'cafe', 'sehir' => 'Lefkoşa', 'adres' => 'Dereboyu Caddesi, Lefkoşa', 'telefon' => '+90 392 228 16 16', 'aciklama' => 'Pasta, poğaça ve kahvaltı seçenekleri.'],
        ['ad' => 'Ledra Books & Coffee', 'kategori' => 'cafe', 'sehir' => 'Lefkoşa', 'adres' => 'Ledra Caddesi, Lefkoşa', 'telefon' => '+90 392 228 68 68', 'aciklama' => 'Sınır kapısı yakınında kitap ve kahve.'],
        ['ad' => 'Maxim Bar', 'kategori' => 'bar', 'sehir' => 'Lefkoşa', 'adres' => 'Dereboyu Caddesi, Lefkoşa', 'telefon' => '+90 392 228 69 69', 'aciklama' => 'Gece geç saatlere kadar açık klasik bar.'],
        ['ad' => 'Kahve Dünyası', 'kategori' => 'cafe', 'sehir' => 'Lefkoşa', 'adres' => 'Dereboyu Caddesi, Lefkoşa', 'telefon' => '+90 392 228 14 14', 'aciklama' => 'Kahve, sıcak çikolata ve tatlı zincirinin Lefkoşa şubesi.'],

        // Girne
        ['ad' => 'Bedesten Restoran', 'kategori' => 'restoran', 'sehir' => 'Girne', 'adres' => 'Bedesten Sokak, Girne', 'telefon' => '+90 392 815 31 31', 'aciklama' => 'Eski Bedesten içinde meze ve ızgara.'],
        ['ad' => 'The House Cafe', 'kategori' => 'cafe', 'sehir' => 'Girne', 'adres' => 'Ziya Rızkı Caddesi, Girne', 'telefon' => '+90 392 815 53 53', 'aciklama' => 'Şık dekoru, kahve ve tatlılarıyla uğrak nokta.'],
        ['ad' => 'Girne Kale Kahvesi', 'kategori' => 'cafe', 'sehir' => 'Girne', 'adres' => 'Girne Kalesi, Girne', 'telefon' => '+90 392 815 12 12', 'aciklama' => 'Kale manzarasında kahve ve dondurma.'],
        ['ad' => 'La Bodega', 'kategori' => 'bar', 'sehir' => 'Girne', 'adres' => 'Eski Liman, Girne', 'telefon' => '+90 392 815 76 76', 'aciklama' => 'Şarap ve tapas odaklı Akdeniz barı.'],
        ['ad' => 'Casa Siciliana', 'kategori' => 'restoran', 'sehir' => 'Girne', 'adres' => 'Alsancak, Girne', 'telefon' => '+90 392 815 83 83', 'aciklama' => 'Sicilya ve İtalyan mutfağı.'],
        ['ad' => 'Armağan Balık', 'kategori' => 'restoran', 'sehir' => 'Girne', 'adres' => 'Karakum, Girne', 'telefon' => '+90 392 815 66 77', 'aciklama' => 'Taze balık ve mevsim mezeleri.'],
        ['ad' => 'Sunset Terrace', 'kategori' => 'bar', 'sehir' => 'Girne', 'adres' => 'Alsancak, Girne', 'telefon' => '+90 392 815 75 75', 'aciklama' => 'Gün batımı kokteylleri, deniz manzarası.'],
        ['ad' => 'Liman Coffee Republic', 'kategori' => 'cafe', 'sehir' => 'Girne', 'adres' => 'Girne Limanı, Girne', 'telefon' => '+90 392 815 33 11', 'aciklama' => 'Liman kenarında kahve ve atıştırmalık.'],
        ['ad' => 'Merit Park Garden', 'kategori' => 'restoran', 'sehir' => 'Girne', 'adres' => 'Karakum, Girne', 'telefon' => '+90 392 815 38 38', 'aciklama' => 'Otel bahçesinde açık büfe ve ızgara.'],

        // Gazimağusa
        ['ad' => 'Othello Garden', 'kategori' => 'restoran', 'sehir' => 'Gazimağusa', 'adres' => 'Namık Kemal Meydanı, Gazimağusa', 'telefon' => '+90 392 365 45 45', 'aciklama' => 'Surların içinde bahçe alanı, meze ve ızgara.'],
        ['ad' => 'Salamis Teras Restoran', 'kategori' => 'restoran', 'sehir' => 'Gazimağusa', 'adres' => 'Salamis Yolu, Gazimağusa', 'telefon' => '+90 392 365 34 34', 'aciklama' => 'Geniş terasta deniz manzaralı akşam yemeği.'],
        ['ad' => 'Mağusa Balıkçı', 'kategori' => 'restoran', 'sehir' => 'Gazimağusa', 'adres' => 'Balıkçı Pazarı, Gazimağusa', 'telefon' => '+90 392 365 36 36', 'aciklama' => 'Pazarda taze balık ve hızlı servis.'],
        ['ad' => 'Surlariçi Kafe', 'kategori' => 'cafe', 'sehir' => 'Gazimağusa', 'adres' => 'Eski Şehir, Gazimağusa', 'telefon' => '+90 392 365 72 72', 'aciklama' => 'Surlar içinde sessiz çalışma köşesi olan kafe.'],
        ['ad' => 'Kıbrıs Kebap Evi', 'kategori' => 'restoran', 'sehir' => 'Gazimağusa', 'adres' => 'Salamis Yolu, Gazimağusa', 'telefon' => '+90 392 365 96 96', 'aciklama' => 'Şiş, köfte ve lavaş.'],
        ['ad' => 'Old Town Bar', 'kategori' => 'bar', 'sehir' => 'Gazimağusa', 'adres' => 'Eski Şehir, Gazimağusa', 'telefon' => '+90 392 365 82 82', 'aciklama' => 'Surlar üstünde teras barı.'],
        ['ad' => 'Gazi Bahçe', 'kategori' => 'cafe', 'sehir' => 'Gazimağusa', 'adres' => 'Namık Kemal Meydanı, Gazimağusa', 'telefon' => '+90 392 365 68 68', 'aciklama' => 'Bahçede kahve ve limonata.'],

        // İskele
        ['ad' => 'Bafra Balık Evi', 'kategori' => 'restoran', 'sehir' => 'İskele', 'adres' => 'Bafra, İskele', 'telefon' => '+90 392 371 66 66', 'aciklama' => 'Bafra sahilinde balık ve ızgara.'],
        ['ad' => 'İskele Liman Kahve', 'kategori' => 'cafe', 'sehir' => 'İskele', 'adres' => 'Balıkçı Limanı, İskele', 'telefon' => '+90 392 371 68 68', 'aciklama' => 'Liman manzarasında kahve.'],
        ['ad' => 'Turna Meyhane', 'kategori' => 'bar', 'sehir' => 'İskele', 'adres' => 'Yeni İskele, İskele', 'telefon' => '+90 392 371 69 69', 'aciklama' => 'Meze ve rakı eşliğinde akşam.'],

        // Güzelyurt
        ['ad' => 'Cengiz Topel Kafe', 'kategori' => 'cafe', 'sehir' => 'Güzelyurt', 'adres' => 'Cengiz Topel Caddesi, Güzelyurt', 'telefon' => '+90 392 714 66 66', 'aciklama' => 'Merkezde klasik kahvehane.'],
        ['ad' => 'Portakal Bahçe Restoran', 'kategori' => 'restoran', 'sehir' => 'Güzelyurt', 'adres' => 'Güzelyurt', 'telefon' => '+90 392 714 68 68', 'aciklama' => 'Narenciye bahçeleri içinde, yöresel ürünler.'],
        ['ad' => 'Güzelyurt Meyhane', 'kategori' => 'bar', 'sehir' => 'Güzelyurt', 'adres' => 'Güzelyurt Meydanı, Güzelyurt', 'telefon' => '+90 392 714 69 69', 'aciklama' => 'Akşam meze ve canlı müzik.'],

        // Lefke
        ['ad' => 'Gemikonağı Marina Cafe', 'kategori' => 'cafe', 'sehir' => 'Lefke', 'adres' => 'Gemikonağı, Lefke', 'telefon' => '+90 392 727 64 64', 'aciklama' => 'Marina manzarasında kahve.'],
        ['ad' => 'Lefke Otel Restoran', 'kategori' => 'restoran', 'sehir' => 'Lefke', 'adres' => 'Lefke', 'telefon' => '+90 392 727 66 66', 'aciklama' => 'Otel restoranında Kıbrıs mutfağı.'],
        ['ad' => 'Zeytinlik Bar', 'kategori' => 'bar', 'sehir' => 'Lefke', 'adres' => 'Gemikonağı, Lefke', 'telefon' => '+90 392 727 68 68', 'aciklama' => 'Zeytinlikler arasında sakin bar.'],
    ];

    $mekanEkle = $pdo->prepare(
        'INSERT INTO mekanlar (ad, kategori, sehir, adres, telefon, aciklama, aktif)
         VALUES (:ad, :kategori, :sehir, :adres, :telefon, :aciklama, 1)'
    );

    foreach ($mekanlar as $mekan) {
        $mekanEkle->execute([
            ':ad' => $mekan['ad'],
            ':kategori' => $mekan['kategori'],
            ':sehir' => $mekan['sehir'],
            ':adres' => $mekan['adres'] ?? '',
            ':telefon' => $mekan['telefon'] ?? '',
            ':aciklama' => $mekan['aciklama'] ?? '',
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

    $bugunKampanyalari = [
        [
            'mekan' => 'Petek Pastanesi',
            'baslik' => 'Bugüne Özel Tatlı İndirimi',
            'aciklama' => 'Seçili tatlılarda gün boyu %15 indirim.',
            'tarih' => date('Y-m-d'),
        ],
        [
            'mekan' => 'BarDuck Gastro Bar',
            'baslik' => 'Sürekli Happy Hour',
            'aciklama' => 'Hafta içi her gün 17:00 - 19:00 arası özel menü.',
            'tarih' => '',
        ],
        [
            'mekan' => "Niazi's Restaurant",
            'baslik' => 'İki Kişilik Meze Menüsü',
            'aciklama' => 'Hafta sonu çift kişilik meze tabağında %10 indirim.',
            'tarih' => '',
        ],
    ];

    foreach ($bugunKampanyalari as $kampanya) {
        $mekanIdBul->execute([':ad' => $kampanya['mekan']]);
        $mekanId = $mekanIdBul->fetchColumn();

        if ($mekanId) {
            $kampanyaEkle->execute([
                ':mekan_id' => $mekanId,
                ':baslik' => $kampanya['baslik'],
                ':aciklama' => $kampanya['aciklama'],
                ':tarih' => $kampanya['tarih'],
            ]);
        }
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

function kategoriEtiketi(string $kategori): string
{
    return KATEGORI_ETIKETLERI[$kategori] ?? ucfirst($kategori);
}

/**
 * Mekanın popüler listesinde olup olmadığını kontrol eder.
 */
function populerMi(?string $ad): bool
{
    return $ad !== null && in_array($ad, POPULER_MEKANLAR, true);
}

/**
 * Seçili şehre göre kategori ve toplam mekan sayılarını döndürür.
 */
function mekanSayilari(?string $sehir = null): array
{
    $sayilar = ['tum' => 0, 'cafe' => 0, 'bar' => 0, 'restoran' => 0];

    if ($sehir !== null && !in_array($sehir, SEHIRLER, true)) {
        return $sayilar;
    }

    $sql = 'SELECT kategori, COUNT(*) AS adet FROM mekanlar WHERE aktif = 1';
    if ($sehir !== null) {
        $sql .= ' AND sehir = :sehir';
    }
    $sql .= ' GROUP BY kategori';

    $stmt = pdoBaglantisi()->prepare($sql);
    if ($sehir !== null) {
        $stmt->execute([':sehir' => $sehir]);
    } else {
        $stmt->execute();
    }

    foreach ($stmt->fetchAll() as $satir) {
        $kategori = $satir['kategori'];
        if (array_key_exists($kategori, $sayilar)) {
            $sayilar[$kategori] = (int) $satir['adet'];
            $sayilar['tum'] += (int) $satir['adet'];
        }
    }

    return $sayilar;
}

/**
 * Google Haritalar arama bağlantısı üretir.
 */
function haritaBaglantisi(array $mekan): string
{
    $sorgu = trim(implode(' ', array_filter([
        $mekan['ad'] ?? '',
        $mekan['adres'] ?? '',
        $mekan['sehir'] ?? '',
        'KKTC',
    ])));

    return 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode($sorgu);
}
