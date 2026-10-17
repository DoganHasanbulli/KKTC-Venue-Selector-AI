<?php
require_once __DIR__ . '/config.php';
?>
<!doctype html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KKTC Rastgele Mekan Seçici</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body data-page="index">
    <header class="ust-menu">
        <h1>KKTC Rastgele Mekan Seçici</h1>
        <nav>
            <a href="index.php" class="aktif">Ana Sayfa</a>
            <a href="mekanlar.php">Mekanlar</a>
            <a href="admin/login.php">Admin</a>
        </nav>
    </header>

    <main class="kapsayici">
        <section class="hero">
            <h2>Bugün Nereye Gidelim?</h2>
            <p>Kategori ve şehir seç, dev butona bas; KKTC'den rastgele bir mekan gelsin!</p>
        </section>

        <section class="filtre-kart">
            <h3>Kategori Seç</h3>
            <div class="kategori-grid" id="kategoriGrid">
                <button type="button" class="kategori-btn secili" data-kategori="tum">✨<span>Tümü</span></button>
                <button type="button" class="kategori-btn" data-kategori="cafe">☕<span>Cafe</span></button>
                <button type="button" class="kategori-btn" data-kategori="bar">🍸<span>Bar</span></button>
                <button type="button" class="kategori-btn" data-kategori="restoran">🍽️<span>Restoran</span></button>
            </div>

            <label for="sehirSecimi">Şehir Seç</label>
            <select id="sehirSecimi">
                <option value="Tümü">Tümü</option>
                <?php foreach (SEHIRLER as $sehir): ?>
                    <option value="<?= guvenliMetin($sehir) ?>"><?= guvenliMetin($sehir) ?></option>
                <?php endforeach; ?>
            </select>

            <button id="rastgeleSecBtn" class="rastgele-btn" type="button">Rastgele Seç</button>
            <p id="durumMesaji" class="durum-mesaji" aria-live="polite"></p>
        </section>

        <section class="sonuc-kart" id="sonucKart">
            <h3>Seçilen Mekan</h3>
            <div class="sonuc-icerik">
                <p>Henüz seçim yapılmadı.</p>
            </div>
        </section>

        <section class="kampanya-kart">
            <h3>Bugünün Kampanyaları</h3>
            <div id="kampanyaListesi" class="kampanya-listesi">
                <p>Kampanyalar yükleniyor...</p>
            </div>
        </section>
    </main>

    <script src="js/main.js"></script>
</body>
</html>
