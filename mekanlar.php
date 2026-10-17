<?php
require_once __DIR__ . '/config.php';
?>
<!doctype html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mekanlar - KKTC Rastgele Mekan Seçici</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body data-page="mekanlar">
    <header class="ust-menu">
        <h1>KKTC Rastgele Mekan Seçici</h1>
        <nav>
            <a href="index.php">Ana Sayfa</a>
            <a href="mekanlar.php" class="aktif">Mekanlar</a>
            <a href="admin/login.php">Admin</a>
        </nav>
    </header>

    <main class="kapsayici">
        <section class="hero">
            <h2>Tüm Mekanlar</h2>
            <p>Şehir ve kategoriye göre filtrele, arama yap ve kampanyalı mekanları hemen gör.</p>
        </section>

        <section class="filtre-kart">
            <div class="filtre-cift">
                <div>
                    <label for="listeSehir">Şehir</label>
                    <select id="listeSehir">
                        <option value="Tümü">Tümü</option>
                        <?php foreach (SEHIRLER as $sehir): ?>
                            <option value="<?= guvenliMetin($sehir) ?>"><?= guvenliMetin($sehir) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="listeKategori">Kategori</label>
                    <select id="listeKategori">
                        <option value="tum">Tümü</option>
                        <option value="cafe">Cafe</option>
                        <option value="bar">Bar</option>
                        <option value="restoran">Restoran</option>
                    </select>
                </div>
            </div>

            <label for="listeArama">Arama</label>
            <input id="listeArama" type="text" placeholder="Mekan adı, adres veya açıklama ile ara...">
        </section>

        <section class="liste-kart">
            <h3 id="listeBaslik">Mekan Listesi</h3>
            <div id="mekanListe" class="mekan-liste-grid">
                <p>Yükleniyor...</p>
            </div>
        </section>
    </main>

    <script src="js/main.js"></script>
</body>
</html>
