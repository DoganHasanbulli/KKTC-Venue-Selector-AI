<?php
declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/_auth.php';

if (adminGirisliMi()) {
    header('Location: panel.php');
    exit;
}

$hata = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kullaniciAdi = trim($_POST['kullanici_adi'] ?? '');
    $sifre = $_POST['sifre'] ?? '';

    if ($kullaniciAdi === '' || $sifre === '') {
        $hata = 'Lütfen kullanıcı adı ve şifreyi doldurun.';
    } else {
        $pdo = pdoBaglantisi();
        $stmt = $pdo->prepare('SELECT id, kullanici_adi, sifre_hash FROM yonetici WHERE kullanici_adi = :kullanici_adi LIMIT 1');
        $stmt->execute([':kullanici_adi' => $kullaniciAdi]);
        $yonetici = $stmt->fetch();

        if ($yonetici && password_verify($sifre, $yonetici['sifre_hash'])) {
            session_regenerate_id(true);
            $_SESSION['yonetici_id'] = (int) $yonetici['id'];
            $_SESSION['yonetici_adi'] = $yonetici['kullanici_adi'];
            header('Location: panel.php');
            exit;
        }

        $hata = 'Giriş bilgileri hatalı.';
    }
}
?>
<!doctype html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Girişi</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <main class="login-kutu admin-kart">
        <h1>Admin Girişi</h1>
        <p class="soluk">KKTC Rastgele Mekan Seçici yönetim paneline erişmek için giriş yapın.</p>

        <?php if ($hata !== ''): ?>
            <div class="hata"><?= guvenliMetin($hata) ?></div>
        <?php endif; ?>

        <form method="post" autocomplete="off">
            <label for="kullanici_adi">Kullanıcı Adı</label>
            <input id="kullanici_adi" name="kullanici_adi" type="text" required>

            <label for="sifre">Şifre</label>
            <input id="sifre" name="sifre" type="password" required>

            <button class="dugme-mavi" type="submit">Giriş Yap</button>
        </form>

        <p class="soluk">Varsayılan hesap: <strong>admin</strong> / <strong>admin123</strong></p>
        <p><a href="../index.php">← Ana sayfaya dön</a></p>
    </main>
</body>
</html>
