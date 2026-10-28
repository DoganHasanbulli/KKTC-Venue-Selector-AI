<?php
require_once __DIR__ . '/config.php';

$varsayilanSehir = trim((string) ($_GET['sehir'] ?? ''));
$varsayilanKategori = trim((string) ($_GET['kategori'] ?? ''));

$seciliSehir = gecerliSehirMi($varsayilanSehir) ? $varsayilanSehir : 'Tümü';
$seciliKategori = gecerliKategoriMi($varsayilanKategori) ? $varsayilanKategori : 'tum';

$pdo = pdoBaglantisi();

$aktifSehir = $seciliSehir === 'Tümü' ? null : $seciliSehir;
$baslikSayilari = mekanSayilari($aktifSehir);
$sehirSayilari = $pdo->query(
    'SELECT sehir, COUNT(*) AS adet FROM mekanlar WHERE aktif = 1 GROUP BY sehir'
)->fetchAll(PDO::FETCH_KEY_PAIR);
$toplamMekan = (int) $pdo->query('SELECT COUNT(*) FROM mekanlar WHERE aktif = 1')->fetchColumn();

$kategoriDugmeleri = [
    'tum' => ['ikon' => '✨', 'ad' => 'Tümü', 'sayi' => $baslikSayilari['tum']],
    'cafe' => ['ikon' => '☕', 'ad' => 'Cafe', 'sayi' => $baslikSayilari['cafe']],
    'bar' => ['ikon' => '🍸', 'ad' => 'Bar', 'sayi' => $baslikSayilari['bar']],
    'restoran' => ['ikon' => '🍽️', 'ad' => 'Restoran', 'sayi' => $baslikSayilari['restoran']],
];
?>
<!doctype html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="KKTC mekanlarını şehir ve kategoriye göre filtrele, ara ve keşfet.">
    <title>Mekanlar - KKTC Rastgele Mekan Seçici</title>
    <script>try{if(localStorage.getItem('kktc_tema')==='koyu')document.documentElement.dataset.theme='koyu';}catch(e){}</script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css?v=20261019">
</head>
<body data-page="mekanlar">
    <header class="ust-menu">
        <a class="marka" href="index.php">
            <span class="marka-logo">🧭</span>
            <span class="marka-yazi">KKTC<strong>Mekan Seçici</strong></span>
        </a>
        <div class="ust-sag">
            <nav>
                <a href="index.php">Ana Sayfa</a>
                <a href="mekanlar.php" class="aktif">Mekanlar</a>
                <a href="admin/login.php">Admin</a>
            </nav>
            <button type="button" class="tema-dugme" aria-label="Temayı değiştir">🌙</button>
        </div>
    </header>

    <main class="kapsayici">
        <section class="hero kisa">
            <div class="hero-dekor" aria-hidden="true">
                <span class="blob blob-2"></span>
            </div>
            <span class="hero-etiket">Keşfet</span>
            <h2>Tüm Mekanlar</h2>
            <p>Şehir ve kategoriye göre filtrele, arama yap, favorilerine ekle ve kampanyalı mekanları hemen gör.</p>
        </section>

        <section class="panel arac-cubugu">
            <div class="kategori-grid kategori-pilleri" id="listeKategoriPilleri">
                <?php foreach ($kategoriDugmeleri as $anahtar => $bilgi): ?>
                    <button type="button" class="kategori-btn<?= $anahtar === $seciliKategori ? ' secili' : '' ?>" data-kategori="<?= $anahtar ?>">
                        <span class="kategori-ikon"><?= $bilgi['ikon'] ?></span>
                        <span class="kategori-ad"><?= guvenliMetin($bilgi['ad']) ?></span>
                        <span class="kategori-sayi"><?= $bilgi['sayi'] ?></span>
                    </button>
                <?php endforeach; ?>
            </div>

            <div class="arac-satir">
                <div class="alan">
                    <label for="listeSehir">Şehir</label>
                    <select id="listeSehir">
                        <option value="Tümü" <?= $seciliSehir === 'Tümü' ? 'selected' : '' ?>>Tümü (<?= $toplamMekan ?>)</option>
                        <?php foreach (SEHIRLER as $sehir): ?>
                            <option value="<?= guvenliMetin($sehir) ?>" <?= $sehir === $seciliSehir ? 'selected' : '' ?>><?= guvenliMetin($sehir) ?> (<?= (int) ($sehirSayilari[$sehir] ?? 0) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="alan">
                    <label for="listeSiralama">Sıralama</label>
                    <select id="listeSiralama">
                        <option value="sehir">Şehir (A-Z)</option>
                        <option value="ad">Mekan Adı (A-Z)</option>
                        <option value="kategori">Kategori</option>
                        <option value="populer">Popüler olanlar önce</option>
                        <option value="kampanya">Kampanyalılar önce</option>
                    </select>
                </div>
                <div class="alan genis">
                    <label for="listeArama">Arama</label>
                    <input id="listeArama" type="search" placeholder="Mekan adı, adres veya açıklama ile ara...">
                </div>
            </div>

            <div class="arac-ek">
                <button type="button" id="favoriFiltre" class="buton favori-dugme">
                    ⭐ Favoriler <span class="favori-sayac" id="favoriSayi">0</span>
                </button>
                <button type="button" id="filtreTemizle" class="buton">↺ Filtreleri Temizle</button>
            </div>

            <p class="filtre-ozet" id="filtreOzet" aria-live="polite"></p>
        </section>

        <section class="panel liste-kart">
            <div class="panel-baslik liste-ozet">
                <h3 id="listeBaslik">Mekan Listesi</h3>
                <div id="ozetRozetler" class="ozet-rozetler"></div>
            </div>
            <div id="mekanListe" class="mekan-liste-grid">
                <div class="iskelet iskelet-kart"></div>
                <div class="iskelet iskelet-kart"></div>
                <div class="iskelet iskelet-kart"></div>
            </div>
        </section>
    </main>

    <footer class="alt-bilgi">
        <div class="alt-ust">
            <span class="alt-marka">🧭 <strong>KKTC Mekan Seçici</strong></span>
            <nav class="alt-linkler">
                <a href="index.php">Ana Sayfa</a>
                <a href="mekanlar.php">Mekanlar</a>
                <a href="admin/login.php">Admin</a>
            </nav>
        </div>
        <p class="soluk">© <?= date('Y') ?> · Okul Projesi · İletişim ve adres bilgileri gösterim amaçlıdır.</p>
    </footer>

    <script src="js/main.js?v=20261019"></script>
</body>
</html>
