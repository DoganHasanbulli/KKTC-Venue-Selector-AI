<?php
declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/_auth.php';

adminGirisGerekli();
$pdo = pdoBaglantisi();

$mesaj = '';
$mesajTip = '';

function aktifDegeri(mixed $deger): int
{
    return ($deger === '1' || $deger === 1 || $deger === 'on') ? 1 : 0;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $islem = $_POST['islem'] ?? '';

    try {
        if ($islem === 'mekan_ekle') {
            $ad = trim($_POST['ad'] ?? '');
            $kategori = trim($_POST['kategori'] ?? '');
            $sehir = trim($_POST['sehir'] ?? '');
            $adres = trim($_POST['adres'] ?? '');
            $telefon = trim($_POST['telefon'] ?? '');
            $aciklama = trim($_POST['aciklama'] ?? '');
            $aktif = aktifDegeri($_POST['aktif'] ?? 0);

            if ($ad === '' || !gecerliKategoriMi($kategori) || !gecerliSehirMi($sehir)) {
                throw new RuntimeException('Mekan ekleme sırasında zorunlu alanları doğru doldurun.');
            }

            $stmt = $pdo->prepare('INSERT INTO mekanlar (ad, kategori, sehir, adres, telefon, aciklama, aktif) VALUES (:ad, :kategori, :sehir, :adres, :telefon, :aciklama, :aktif)');
            $stmt->execute([
                ':ad' => $ad,
                ':kategori' => $kategori,
                ':sehir' => $sehir,
                ':adres' => $adres,
                ':telefon' => $telefon,
                ':aciklama' => $aciklama,
                ':aktif' => $aktif,
            ]);

            $mesaj = 'Mekan eklendi.';
            $mesajTip = 'basari';
        }

        if ($islem === 'mekan_guncelle') {
            $id = (int) ($_POST['id'] ?? 0);
            $ad = trim($_POST['ad'] ?? '');
            $kategori = trim($_POST['kategori'] ?? '');
            $sehir = trim($_POST['sehir'] ?? '');
            $adres = trim($_POST['adres'] ?? '');
            $telefon = trim($_POST['telefon'] ?? '');
            $aciklama = trim($_POST['aciklama'] ?? '');
            $aktif = aktifDegeri($_POST['aktif'] ?? 0);

            if ($id < 1 || $ad === '' || !gecerliKategoriMi($kategori) || !gecerliSehirMi($sehir)) {
                throw new RuntimeException('Mekan güncelleme için geçerli veri girin.');
            }

            $stmt = $pdo->prepare('UPDATE mekanlar SET ad=:ad, kategori=:kategori, sehir=:sehir, adres=:adres, telefon=:telefon, aciklama=:aciklama, aktif=:aktif WHERE id=:id');
            $stmt->execute([
                ':id' => $id,
                ':ad' => $ad,
                ':kategori' => $kategori,
                ':sehir' => $sehir,
                ':adres' => $adres,
                ':telefon' => $telefon,
                ':aciklama' => $aciklama,
                ':aktif' => $aktif,
            ]);

            $mesaj = 'Mekan güncellendi.';
            $mesajTip = 'basari';
        }

        if ($islem === 'mekan_sil') {
            $id = (int) ($_POST['id'] ?? 0);
            if ($id < 1) {
                throw new RuntimeException('Silinecek mekan bulunamadı.');
            }

            $stmt = $pdo->prepare('DELETE FROM mekanlar WHERE id = :id');
            $stmt->execute([':id' => $id]);
            $mesaj = 'Mekan silindi.';
            $mesajTip = 'uyari';
        }

        if ($islem === 'kampanya_ekle') {
            $mekanId = (int) ($_POST['mekan_id'] ?? 0);
            $baslik = trim($_POST['baslik'] ?? '');
            $aciklama = trim($_POST['aciklama'] ?? '');
            $tarih = trim($_POST['tarih'] ?? '');
            $aktif = aktifDegeri($_POST['aktif'] ?? 0);

            if ($mekanId < 1 || $baslik === '') {
                throw new RuntimeException('Kampanya eklemek için mekan ve başlık zorunludur.');
            }

            if ($tarih !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $tarih)) {
                throw new RuntimeException('Kampanya tarihi YYYY-MM-DD formatında olmalı.');
            }

            $stmt = $pdo->prepare('INSERT INTO kampanyalar (mekan_id, baslik, aciklama, tarih, aktif) VALUES (:mekan_id, :baslik, :aciklama, :tarih, :aktif)');
            $stmt->execute([
                ':mekan_id' => $mekanId,
                ':baslik' => $baslik,
                ':aciklama' => $aciklama,
                ':tarih' => $tarih,
                ':aktif' => $aktif,
            ]);

            $mesaj = 'Kampanya eklendi.';
            $mesajTip = 'basari';
        }

        if ($islem === 'kampanya_guncelle') {
            $id = (int) ($_POST['id'] ?? 0);
            $mekanId = (int) ($_POST['mekan_id'] ?? 0);
            $baslik = trim($_POST['baslik'] ?? '');
            $aciklama = trim($_POST['aciklama'] ?? '');
            $tarih = trim($_POST['tarih'] ?? '');
            $aktif = aktifDegeri($_POST['aktif'] ?? 0);

            if ($id < 1 || $mekanId < 1 || $baslik === '') {
                throw new RuntimeException('Kampanya güncellemek için geçerli veri girin.');
            }

            if ($tarih !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $tarih)) {
                throw new RuntimeException('Kampanya tarihi YYYY-MM-DD formatında olmalı.');
            }

            $stmt = $pdo->prepare('UPDATE kampanyalar SET mekan_id=:mekan_id, baslik=:baslik, aciklama=:aciklama, tarih=:tarih, aktif=:aktif WHERE id=:id');
            $stmt->execute([
                ':id' => $id,
                ':mekan_id' => $mekanId,
                ':baslik' => $baslik,
                ':aciklama' => $aciklama,
                ':tarih' => $tarih,
                ':aktif' => $aktif,
            ]);

            $mesaj = 'Kampanya güncellendi.';
            $mesajTip = 'basari';
        }

        if ($islem === 'kampanya_sil') {
            $id = (int) ($_POST['id'] ?? 0);
            if ($id < 1) {
                throw new RuntimeException('Silinecek kampanya bulunamadı.');
            }

            $stmt = $pdo->prepare('DELETE FROM kampanyalar WHERE id = :id');
            $stmt->execute([':id' => $id]);
            $mesaj = 'Kampanya silindi.';
            $mesajTip = 'uyari';
        }
    } catch (Throwable $e) {
        $mesaj = $e->getMessage();
        $mesajTip = 'hata';
    }
}

$mekanDuzenleId = (int) ($_GET['mekan_duzenle'] ?? 0);
$kampanyaDuzenleId = (int) ($_GET['kampanya_duzenle'] ?? 0);

$mekanDuzenlenecek = null;
if ($mekanDuzenleId > 0) {
    $stmt = $pdo->prepare('SELECT * FROM mekanlar WHERE id = :id');
    $stmt->execute([':id' => $mekanDuzenleId]);
    $mekanDuzenlenecek = $stmt->fetch();
}

$kampanyaDuzenlenecek = null;
if ($kampanyaDuzenleId > 0) {
    $stmt = $pdo->prepare('SELECT * FROM kampanyalar WHERE id = :id');
    $stmt->execute([':id' => $kampanyaDuzenleId]);
    $kampanyaDuzenlenecek = $stmt->fetch();
}

$toplamAktifMekan = (int) $pdo->query('SELECT COUNT(*) FROM mekanlar WHERE aktif = 1')->fetchColumn();
$kategoriSayilari = $pdo->query('SELECT kategori, COUNT(*) AS adet FROM mekanlar WHERE aktif = 1 GROUP BY kategori ORDER BY kategori')->fetchAll();
$sehirSayilari = $pdo->query('SELECT sehir, COUNT(*) AS adet FROM mekanlar WHERE aktif = 1 GROUP BY sehir ORDER BY sehir')->fetchAll();

$mekanlar = $pdo->query('SELECT * FROM mekanlar ORDER BY sehir, kategori, ad')->fetchAll();
$kampanyalar = $pdo->query("SELECT k.*, m.ad AS mekan_adi FROM kampanyalar k INNER JOIN mekanlar m ON m.id = k.mekan_id ORDER BY k.id DESC")->fetchAll();
$aktifMekanlar = $pdo->query('SELECT id, ad, sehir, kategori FROM mekanlar WHERE aktif = 1 ORDER BY ad')->fetchAll();
?>
<!doctype html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Paneli</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <header class="ust-menu">
        <h1>KKTC Rastgele Mekan Seçici - Admin</h1>
        <nav>
            <a href="../index.php">Ana Sayfa</a>
            <a href="../mekanlar.php">Mekanlar</a>
            <a href="panel.php" class="aktif">Admin Panel</a>
        </nav>
    </header>

    <main class="kapsayici">
        <section class="admin-kart admin-ust">
            <div>
                <h2>Hoş geldiniz, <?= guvenliMetin((string) ($_SESSION['yonetici_adi'] ?? 'Yönetici')) ?></h2>
                <p class="soluk">Mekan ve kampanya kayıtlarını buradan yönetebilirsiniz.</p>
            </div>
            <a class="admin-cikis" href="logout.php">Çıkış Yap</a>
        </section>

        <?php if ($mesaj !== ''): ?>
            <section class="<?= guvenliMetin($mesajTip ?: 'uyari') ?>"><?= guvenliMetin($mesaj) ?></section>
        <?php endif; ?>

        <section class="admin-kart">
            <h3>Özet İstatistikler</h3>
            <div class="admin-grid">
                <article class="istatistik-kutu">
                    <h4>Toplam Aktif Mekan</h4>
                    <p><strong><?= $toplamAktifMekan ?></strong></p>
                </article>
                <article class="istatistik-kutu">
                    <h4>Kategori Bazlı</h4>
                    <ul>
                        <?php foreach ($kategoriSayilari as $satir): ?>
                            <li><?= guvenliMetin($satir['kategori']) ?>: <?= (int) $satir['adet'] ?></li>
                        <?php endforeach; ?>
                    </ul>
                </article>
                <article class="istatistik-kutu">
                    <h4>Şehir Bazlı</h4>
                    <ul>
                        <?php foreach ($sehirSayilari as $satir): ?>
                            <li><?= guvenliMetin($satir['sehir']) ?>: <?= (int) $satir['adet'] ?></li>
                        <?php endforeach; ?>
                    </ul>
                </article>
            </div>
        </section>

        <section class="admin-kart">
            <h3><?= $mekanDuzenlenecek ? 'Mekan Güncelle' : 'Yeni Mekan Ekle' ?></h3>
            <form method="post">
                <input type="hidden" name="islem" value="<?= $mekanDuzenlenecek ? 'mekan_guncelle' : 'mekan_ekle' ?>">
                <?php if ($mekanDuzenlenecek): ?>
                    <input type="hidden" name="id" value="<?= (int) $mekanDuzenlenecek['id'] ?>">
                <?php endif; ?>

                <div class="grid-2">
                    <div>
                        <label for="mekan_ad">Mekan Adı</label>
                        <input id="mekan_ad" name="ad" required value="<?= guvenliMetin($mekanDuzenlenecek['ad'] ?? '') ?>">
                    </div>
                    <div>
                        <label for="mekan_kategori">Kategori</label>
                        <select id="mekan_kategori" name="kategori" required>
                            <?php foreach (KATEGORILER as $kategori): ?>
                                <option value="<?= guvenliMetin($kategori) ?>" <?= (($mekanDuzenlenecek['kategori'] ?? '') === $kategori) ? 'selected' : '' ?>><?= guvenliMetin($kategori) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="grid-2">
                    <div>
                        <label for="mekan_sehir">Şehir</label>
                        <select id="mekan_sehir" name="sehir" required>
                            <?php foreach (SEHIRLER as $sehir): ?>
                                <option value="<?= guvenliMetin($sehir) ?>" <?= (($mekanDuzenlenecek['sehir'] ?? '') === $sehir) ? 'selected' : '' ?>><?= guvenliMetin($sehir) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label for="mekan_telefon">Telefon</label>
                        <input id="mekan_telefon" name="telefon" value="<?= guvenliMetin($mekanDuzenlenecek['telefon'] ?? '') ?>">
                    </div>
                </div>

                <label for="mekan_adres">Adres</label>
                <input id="mekan_adres" name="adres" value="<?= guvenliMetin($mekanDuzenlenecek['adres'] ?? '') ?>">

                <label for="mekan_aciklama">Açıklama</label>
                <textarea id="mekan_aciklama" name="aciklama"><?= guvenliMetin($mekanDuzenlenecek['aciklama'] ?? '') ?></textarea>

                <label><input type="checkbox" name="aktif" value="1" <?= (!isset($mekanDuzenlenecek['aktif']) || (int) $mekanDuzenlenecek['aktif'] === 1) ? 'checked' : '' ?>> Aktif</label>

                <button class="dugme-yesil" type="submit"><?= $mekanDuzenlenecek ? 'Güncelle' : 'Ekle' ?></button>
                <?php if ($mekanDuzenlenecek): ?>
                    <a class="kucuk-btn" href="panel.php">İptal</a>
                <?php endif; ?>
            </form>
        </section>

        <section class="admin-kart">
            <h3>Mekan Listesi</h3>
            <div class="tablo-kapsayici">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Ad</th>
                            <th>Kategori</th>
                            <th>Şehir</th>
                            <th>Adres</th>
                            <th>Telefon</th>
                            <th>Aktif</th>
                            <th>İşlem</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($mekanlar as $mekan): ?>
                            <tr>
                                <td><?= (int) $mekan['id'] ?></td>
                                <td><?= guvenliMetin($mekan['ad']) ?></td>
                                <td><?= guvenliMetin($mekan['kategori']) ?></td>
                                <td><?= guvenliMetin($mekan['sehir']) ?></td>
                                <td><?= guvenliMetin($mekan['adres']) ?></td>
                                <td><?= guvenliMetin($mekan['telefon']) ?></td>
                                <td><?= ((int) $mekan['aktif'] === 1) ? 'Evet' : 'Hayır' ?></td>
                                <td>
                                    <div class="inline-form">
                                        <a class="kucuk-btn dugme-mavi" href="panel.php?mekan_duzenle=<?= (int) $mekan['id'] ?>">Düzenle</a>
                                        <form method="post" onsubmit="return confirm('Bu mekanı silmek istediğinizden emin misiniz?');">
                                            <input type="hidden" name="islem" value="mekan_sil">
                                            <input type="hidden" name="id" value="<?= (int) $mekan['id'] ?>">
                                            <button class="dugme-kirmizi" type="submit">Sil</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="admin-kart">
            <h3><?= $kampanyaDuzenlenecek ? 'Kampanya Güncelle' : 'Yeni Kampanya Ekle' ?></h3>
            <form method="post">
                <input type="hidden" name="islem" value="<?= $kampanyaDuzenlenecek ? 'kampanya_guncelle' : 'kampanya_ekle' ?>">
                <?php if ($kampanyaDuzenlenecek): ?>
                    <input type="hidden" name="id" value="<?= (int) $kampanyaDuzenlenecek['id'] ?>">
                <?php endif; ?>

                <label for="kampanya_mekan">Mekan</label>
                <select id="kampanya_mekan" name="mekan_id" required>
                    <?php foreach ($aktifMekanlar as $mekan): ?>
                        <option value="<?= (int) $mekan['id'] ?>" <?= ((int) ($kampanyaDuzenlenecek['mekan_id'] ?? 0) === (int) $mekan['id']) ? 'selected' : '' ?>>
                            <?= guvenliMetin($mekan['ad']) ?> - <?= guvenliMetin($mekan['sehir']) ?> (<?= guvenliMetin($mekan['kategori']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>

                <label for="kampanya_baslik">Başlık</label>
                <input id="kampanya_baslik" name="baslik" required value="<?= guvenliMetin($kampanyaDuzenlenecek['baslik'] ?? '') ?>">

                <label for="kampanya_aciklama">Açıklama</label>
                <textarea id="kampanya_aciklama" name="aciklama"><?= guvenliMetin($kampanyaDuzenlenecek['aciklama'] ?? '') ?></textarea>

                <label for="kampanya_tarih">Tarih (YYYY-MM-DD) - Boş bırakılırsa sürekli kampanya</label>
                <input id="kampanya_tarih" name="tarih" value="<?= guvenliMetin($kampanyaDuzenlenecek['tarih'] ?? '') ?>" placeholder="YYYY-MM-DD">

                <label><input type="checkbox" name="aktif" value="1" <?= (!isset($kampanyaDuzenlenecek['aktif']) || (int) $kampanyaDuzenlenecek['aktif'] === 1) ? 'checked' : '' ?>> Aktif</label>

                <button class="dugme-yesil" type="submit"><?= $kampanyaDuzenlenecek ? 'Güncelle' : 'Ekle' ?></button>
                <?php if ($kampanyaDuzenlenecek): ?>
                    <a class="kucuk-btn" href="panel.php">İptal</a>
                <?php endif; ?>
            </form>
        </section>

        <section class="admin-kart">
            <h3>Kampanya Listesi</h3>
            <div class="tablo-kapsayici">
                <table>
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>Mekan</th>
                        <th>Başlık</th>
                        <th>Açıklama</th>
                        <th>Tarih</th>
                        <th>Aktif</th>
                        <th>İşlem</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($kampanyalar as $kampanya): ?>
                        <tr>
                            <td><?= (int) $kampanya['id'] ?></td>
                            <td><?= guvenliMetin($kampanya['mekan_adi']) ?></td>
                            <td><?= guvenliMetin($kampanya['baslik']) ?></td>
                            <td><?= guvenliMetin($kampanya['aciklama']) ?></td>
                            <td><?= guvenliMetin($kampanya['tarih'] !== '' ? $kampanya['tarih'] : 'Sürekli') ?></td>
                            <td><?= ((int) $kampanya['aktif'] === 1) ? 'Evet' : 'Hayır' ?></td>
                            <td>
                                <div class="inline-form">
                                    <a class="kucuk-btn dugme-mavi" href="panel.php?kampanya_duzenle=<?= (int) $kampanya['id'] ?>">Düzenle</a>
                                    <form method="post" onsubmit="return confirm('Bu kampanyayı silmek istediğinizden emin misiniz?');">
                                        <input type="hidden" name="islem" value="kampanya_sil">
                                        <input type="hidden" name="id" value="<?= (int) $kampanya['id'] ?>">
                                        <button class="dugme-kirmizi" type="submit">Sil</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</body>
</html>
