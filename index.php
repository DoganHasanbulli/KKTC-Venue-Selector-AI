<?php
require_once __DIR__ . '/config.php';

$pdo = pdoBaglantisi();

$toplamMekan = (int) $pdo->query('SELECT COUNT(*) FROM mekanlar WHERE aktif = 1')->fetchColumn();
$sehirSayisi = (int) $pdo->query('SELECT COUNT(DISTINCT sehir) FROM mekanlar WHERE aktif = 1')->fetchColumn();

$kampanyaSorgu = $pdo->prepare(
    "SELECT COUNT(*)
     FROM kampanyalar k
     INNER JOIN mekanlar m ON m.id = k.mekan_id
     WHERE k.aktif = 1 AND m.aktif = 1 AND (k.tarih = '' OR k.tarih = :bugun)"
);
$kampanyaSorgu->execute([':bugun' => date('Y-m-d')]);
$kampanyaSayisi = (int) $kampanyaSorgu->fetchColumn();

$sehirSayilari = $pdo->query(
    'SELECT sehir, COUNT(*) AS adet FROM mekanlar WHERE aktif = 1 GROUP BY sehir'
)->fetchAll(PDO::FETCH_KEY_PAIR);

$baslikSayilari = mekanSayilari(null);

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
    <meta name="description" content="KKTC'de şehir ve kategoriye göre rastgele mekan seç, kampanyaları keşfet.">
    <title>KKTC Rastgele Mekan Seçici</title>
    <script>try{if(localStorage.getItem('kktc_tema')==='koyu')document.documentElement.dataset.theme='koyu';}catch(e){}</script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css?v=20261019">
</head>
<body data-page="index">
    <header class="ust-menu">
        <a class="marka" href="index.php">
            <span class="marka-logo">🧭</span>
            <span class="marka-yazi">KKTC<strong>Mekan Seçici</strong></span>
        </a>
        <div class="ust-sag">
            <nav>
                <a href="index.php" class="aktif">Ana Sayfa</a>
                <a href="mekanlar.php">Mekanlar</a>
                <a href="admin/login.php">Admin</a>
            </nav>
            <button type="button" class="tema-dugme" aria-label="Temayı değiştir">🌙</button>
        </div>
    </header>

    <main class="kapsayici">
        <section class="hero">
            <div class="hero-dekor" aria-hidden="true">
                <span class="blob blob-1"></span>
                <span class="blob blob-2"></span>
                <span class="blob blob-3"></span>
            </div>
            <span class="hero-etiket">Rastgele Keşif</span>
            <h2>Bugün Nereye Gidelim?</h2>
            <p>Kategori ve şehir seç, tek dokunuşla sana uygun bir mekan keşfet. Kampanyaları da anında gör.</p>

            <div class="stat-grid">
                <article class="stat-kart">
                    <span class="stat-ikon">📍</span>
                    <span class="stat-deger" data-sayac data-hedef="<?= $toplamMekan ?>">0</span>
                    <span class="stat-etiket">Aktif Mekan</span>
                </article>
                <article class="stat-kart">
                    <span class="stat-ikon">🏙️</span>
                    <span class="stat-deger" data-sayac data-hedef="<?= $sehirSayisi ?>">0</span>
                    <span class="stat-etiket">Şehir</span>
                </article>
                <article class="stat-kart">
                    <span class="stat-ikon">🎁</span>
                    <span class="stat-deger" data-sayac data-hedef="<?= $kampanyaSayisi ?>">0</span>
                    <span class="stat-etiket">Aktif Kampanya</span>
                </article>
            </div>

            <div class="kesif-cubugu" id="kesifCubugu" data-toplam="<?= $toplamMekan ?>" hidden>
                <div class="kesif-ust">
                    <span class="kesif-etiket">🧭 Keşif İlerlemesi</span>
                    <span class="kesif-deger" id="kesifDeger">0 / <?= $toplamMekan ?></span>
                </div>
                <span class="kesif-cizgi"><span class="kesif-dolgu" id="kesifDolgu"></span></span>
                <span class="kesif-alt" id="kesifAlt">İlk mekanını seç, koleksiyonun başlasın!</span>
            </div>
        </section>

        <section class="mood-bolum" data-acilir>
            <div class="bolum-baslik">
                <h3>Ne Havadasın?</h3>
                <p>Ruh halini seç, gerisini bize bırak — filtreyi biz ayarlarız.</p>
            </div>
            <div class="mood-cipler" id="moodCipleri">
                <button type="button" class="mood-cip" data-kategori="cafe" data-sehir="Tümü"
                        data-mesaj="Sakin bir kahve molası ayarlıyoruz ☕">
                    <span class="mood-ikon">😌</span>
                    <span class="mood-ad">Sakin</span>
                    <span class="mood-alt">cafe · her şehir</span>
                </button>
                <button type="button" class="mood-cip" data-kategori="bar" data-sehir="Tümü"
                        data-mesaj="Enerjini atacak bir yer arıyoruz 🍸">
                    <span class="mood-ikon">🎉</span>
                    <span class="mood-ad">Enerjik</span>
                    <span class="mood-alt">bar · her şehir</span>
                </button>
                <button type="button" class="mood-cip" data-kategori="restoran" data-sehir="Girne"
                        data-mesaj="Girne'de romantik bir akşam yemeği seçiyoruz 💑">
                    <span class="mood-ikon">💑</span>
                    <span class="mood-ad">Romantik</span>
                    <span class="mood-alt">restoran · Girne</span>
                </button>
                <button type="button" class="mood-cip" data-kategori="restoran" data-sehir="Lefkoşa"
                        data-mesaj="Karnını doyuracak bir yer buluyoruz 🍽️">
                    <span class="mood-ikon">🍽️</span>
                    <span class="mood-ad">Açım</span>
                    <span class="mood-alt">restoran · Lefkoşa</span>
                </button>
            </div>
        </section>

        <section class="adim-bolum" data-acilir>
            <div class="bolum-baslik">
                <h3>Nasıl Çalışır?</h3>
                <p>Üç kısa adımda sana uygun mekanı bul.</p>
            </div>
            <div class="adim-grid">
                <article class="adim-kart">
                    <span class="adim-no">1</span>
                    <span class="adim-ikon">🎯</span>
                    <h4>Filtrele</h4>
                    <p>Kategori ve şehrini seç; istersen arama yap.</p>
                </article>
                <article class="adim-kart">
                    <span class="adim-no">2</span>
                    <span class="adim-ikon">🎲</span>
                    <h4>Rastgele Seç</h4>
                    <p>Büyük butona bas, sistem sana bir mekan seçsin.</p>
                </article>
                <article class="adim-kart">
                    <span class="adim-no">3</span>
                    <span class="adim-ikon">📍</span>
                    <h4>Keşfet</h4>
                    <p>Adres, telefon ve haritayı gör; yola çık.</p>
                </article>
            </div>
        </section>

        <section class="sehir-bolum" data-acilir>
            <div class="bolum-baslik">
                <h3>Şehre Göre Keşfet</h3>
                <p>KKTC'nin <?= $sehirSayisi ?> şehrindeki mekanları tek tıkla incele.</p>
            </div>
            <div class="sehir-grid">
                <?php foreach (SEHIRLER as $sehir): ?>
                    <a class="sehir-kart sehir--<?= strtolower($sehir) ?>" href="mekanlar.php?sehir=<?= rawurlencode($sehir) ?>">
                        <span class="sehir-ghost"><?= mb_substr($sehir, 0, 2) ?></span>
                        <span class="sehir-ikon">🏙️</span>
                        <span class="sehir-ad"><?= guvenliMetin($sehir) ?></span>
                        <span class="sehir-sayi"><?= (int) ($sehirSayilari[$sehir] ?? 0) ?> mekan</span>
                        <span class="sehir-ok">İncele →</span>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="panel">
            <div class="panel-baslik">
                <h3>Filtreler</h3>
                <p class="soluk">Önce seçimini yap, sonra büyük butona bas.</p>
            </div>

            <div class="kategori-grid" id="kategoriGrid">
                <?php foreach ($kategoriDugmeleri as $anahtar => $bilgi): ?>
                    <button type="button" class="kategori-btn<?= $anahtar === 'tum' ? ' secili' : '' ?>" data-kategori="<?= $anahtar ?>">
                        <span class="kategori-ikon"><?= $bilgi['ikon'] ?></span>
                        <span class="kategori-ad"><?= $bilgi['ad'] ?></span>
                        <span class="kategori-sayi"><?= $bilgi['sayi'] ?></span>
                    </button>
                <?php endforeach; ?>
            </div>

            <div class="alan dar">
                <label for="sehirSecimi">Şehir</label>
                <select id="sehirSecimi">
                    <option value="Tümü">Tümü (<?= $toplamMekan ?>)</option>
                    <?php foreach (SEHIRLER as $sehir): ?>
                        <option value="<?= guvenliMetin($sehir) ?>"><?= guvenliMetin($sehir) ?> (<?= (int) ($sehirSayilari[$sehir] ?? 0) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>

            <p class="filtre-ozet" id="filtreOzet" aria-live="polite"></p>
        </section>

        <section class="secim-sahnesi" aria-label="Rastgele seçim sahnesi">
            <div class="surpriz-satir">
                <button type="button" class="buton surpriz-dugme" id="surprizDugme" aria-pressed="false">
                    🎲 Sürpriz: gitmediklerimden
                </button>
            </div>
            <button id="rastgeleSecBtn" class="rastgele-btn daire-btn" type="button">
                <span class="btn-dokusu" aria-hidden="true"></span>
                <span class="btn-yazi">Rastgele<br>Seç</span>
                <span class="cevir-bant" aria-hidden="true">
                    <span class="cevir-pencere"><span class="cevir-ray"></span></span>
                </span>
            </button>
            <p id="durumMesaji" class="durum-mesaji" aria-live="polite"></p>
            <p class="klavye-ipucu"><kbd>Boşluk</kbd> tuşuyla da çevirebilirsin</p>
            <div class="gecmis-cipler" id="gecmisListesi" hidden></div>
        </section>

        <section class="panel sonuc-kart" id="sonucKart" data-acilir>
            <div class="panel-baslik">
                <h3>Seçilen Mekan</h3>
            </div>
            <div class="sonuc-icerik">
                <div class="bos-durum">
                    <span class="bos-ikon">🎯</span>
                    <p>Henüz seçim yapılmadı. Yukarıdaki butona basarak başla.</p>
                </div>
            </div>
        </section>

        <section class="panel kampanya-kart" data-acilir>
            <div class="panel-baslik">
                <h3>Bugünün Kampanyaları</h3>
                <p class="soluk">Seçili mekanlarda geçerli güncel fırsatlar.</p>
            </div>
            <div id="kampanyaListesi" class="kampanya-listesi">
                <div class="iskelet iskelet-kampanya"></div>
                <div class="iskelet iskelet-kampanya"></div>
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
