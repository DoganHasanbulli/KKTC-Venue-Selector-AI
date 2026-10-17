# KKTC Rastgele Mekan Seçici

KKTC'de şehir ve kategoriye göre rastgele mekan seçimi yapan, kampanyaları gösteren ve admin panelinden içerik yönetimi sağlayan okul projesidir.

## Özellikler

- Ana sayfada **büyük "Rastgele Seç" butonu** ile anlık mekan seçimi
- Kategori seçimi: **Tümü / Cafe / Bar / Restoran**
- Şehir seçimi: **Tümü / Lefkoşa / Girne / Gazimağusa / İskele / Güzelyurt / Lefke**
- Sonuç kartında mekan bilgileri + varsa **Bugünün Kampanyası** rozeti
- Ayrı **Mekanlar** sayfasında filtreleme + arama
- Session tabanlı güvenli **Admin paneli**
  - Özet istatistikler
  - Mekan CRUD
  - Kampanya CRUD
- SQLite veritabanı ilk çalıştırmada otomatik oluşturulur ve seed verileri yüklenir

## Teknolojiler

- Saf HTML, CSS, JavaScript (Vanilla)
- PHP (PDO)
- SQLite

## XAMPP ile Kurulum ve Çalıştırma (Windows)

1. Bu proje klasörünü `kktc_mekan_secici` adıyla XAMPP `htdocs` içine kopyalayın.
   - Örnek: `C:\xampp\htdocs\kktc_mekan_secici`
2. XAMPP Control Panel üzerinden **Apache** servisini başlatın.
3. Tarayıcıda şu adresi açın:
   - `http://localhost/kktc_mekan_secici`
4. İlk açılışta `database/kktc.db` dosyası otomatik oluşturulur.

## Admin Giriş Bilgisi

- Kullanıcı adı: `admin`
- Şifre: `admin123`

> Güvenlik için teslimden önce admin şifresini değiştirmeniz önerilir.

## Dosya Yapısı

```text
kktc_mekan_secici/
├── index.php
├── mekanlar.php
├── config.php
├── README.md
├── css/
│   └── style.css
├── js/
│   └── main.js
├── api/
│   ├── rastgele.php
│   ├── mekanlar.php
│   └── kampanyalar.php
├── admin/
│   ├── _auth.php
│   ├── index.php
│   ├── login.php
│   ├── panel.php
│   └── logout.php
└── database/
    └── kktc.db (ilk çalıştırmada otomatik oluşur)
```

## Veritabanını Sıfırlama

1. `database/kktc.db` dosyasını silin.
2. Siteyi tekrar açın.
3. Veritabanı otomatik yeniden oluşturulur ve seed verisi tekrar yüklenir.

## 1 Aylık Okul Projesi Takvimi (Öneri)

### 1. Hafta
- Gereksinim analizi ve sayfa akışlarının netleştirilmesi
- Basit wireframe hazırlanması
- Veritabanı yapısının kesinleşmesi

### 2. Hafta
- Ana sayfa + rastgele seçim API geliştirmesi
- Mekan listesi ve filtreleme altyapısı
- Responsive temel tasarımın oturtulması

### 3. Hafta
- Admin giriş sistemi
- Mekan CRUD + Kampanya CRUD
- İstatistik alanı ve hata kontrolleri

### 4. Hafta
- Uçtan uca test, hata düzeltme, içerik düzenleme
- README ve teslim dokümantasyonu
- Son demo provası ve öğretmene sunuma hazırlık
