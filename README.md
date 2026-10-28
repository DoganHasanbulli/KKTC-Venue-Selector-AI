# KKTC Rastgele Mekan Seçici

![PHP](https://img.shields.io/badge/PHP-8.0%2B-777BB4?logo=php&logoColor=white)
![SQLite](https://img.shields.io/badge/SQLite-3-003B57?logo=sqlite&logoColor=white)
![Bağımlılık](https://img.shields.io/badge/ba%C4%9F%C4%B1ml%C4%B1l%C4%B1k-yok-success)
![Lisans](https://img.shields.io/badge/lisans-MIT-green)

KKTC'deki kafe, bar ve restoranları şehir/kategoriye göre filtreleyip **rastgele mekan seçen**, günün kampanyalarını gösteren ve session tabanlı bir **admin paneli** üzerinden içerik yönetimi sağlayan web uygulaması.

Saf PHP + SQLite ile yazıldığı için harici bağımlılık, paket yöneticisi veya derleme adımı gerektirmez.

## İçindekiler

- [Özellikler](#özellikler)
- [Teknolojiler](#teknolojiler)
- [Kurulum](#kurulum)
- [Admin Paneli](#admin-paneli)
- [Dosya Yapısı](#dosya-yapısı)
- [Veritabanı](#veritabanı)
- [Güvenlik Notları](#güvenlik-notları)
- [Lisans](#lisans)

## Özellikler

**Kullanıcı tarafı**

- **162 mekan** (6 şehir, 3 kategori), 23'ü popüler olarak işaretli; her mekanda adres, telefon ve açıklama
- Ana sayfada tek tuşla **rastgele mekan seçimi**
- Kategori filtresi: Tümü / Cafe / Bar / Restoran — canlı sayaçlı
- Şehir filtresi: Tümü / Lefkoşa / Girne / Gazimağusa / İskele / Güzelyurt / Lefke
- Seçilen şehre göre kategori sayaçlarının anında güncellenmesi
- Sonuç kartında mekan bilgileri, **⭐ Popüler** rozeti ve varsa **Bugünün Kampanyası** rozeti
- Her mekan için **Google Haritalar** bağlantısı ("Haritada Gör")
- `mekanlar.php` sayfasında gelişmiş listeleme: metin arama, kategori filtreleri, sıralama, favoriler ve URL ile paylaşılabilir filtreler (`?sehir=Girne&kategori=bar`)
- Açık/koyu tema, mobil uyumlu arayüz, kaydırma animasyonları ve erişilebilir klavye odakları

**Admin tarafı**

- Session tabanlı giriş (`password_hash` / `password_verify`)
- Özet istatistikler
- Mekan ve kampanya CRUD işlemleri
- Kayıtları silmeden **aktif/pasif** yapabilme (pasif kayıtlar listelerde görünmez)

**Teknik**

- İlk çalıştırmada SQLite veritabanı ve seed verileri otomatik oluşturulur
- JSON API uç noktaları (`api/`), PDO ile hazırlanmış (prepared) sorgular

> Not: Seed verilerdeki adres ve telefonlar gösterim amaçlıdır; yayına almadan önce admin panelinden güncellenmesi önerilir.

## Teknolojiler

| Katman | Teknoloji |
| --- | --- |
| Backend | PHP 8+ (PDO) |
| Veritabanı | SQLite 3 |
| Frontend | HTML5, CSS3, Vanilla JavaScript |
| Kimlik doğrulama | PHP Session + `password_hash()` |

## Kurulum

### Gereksinimler

- PHP **8.0+** (`pdo_sqlite` eklentisi açık olmalı)
- Herhangi bir web sunucusu (Apache, Nginx) veya PHP'nin yerleşik sunucusu

### 1. Projeyi klonlayın

```bash
git clone https://github.com/DoganHasanbulli/KKTC-Venue-Selector-AI.git
cd KKTC-Venue-Selector-AI
```

### 2. PHP yerleşik sunucusu (en hızlı yol)

```bash
php -S localhost:8000
```

Ardından <http://localhost:8000> adresini açın.

### 3. Windows için tek tıkla kurulum

Proje kökündeki `KUR.bat` dosyasına çift tıklayın. Betik:

1. PHP kurulumunu otomatik arar (PATH, XAMPP, winget),
2. XAMPP varsa projeyi `htdocs` altına kopyalayıp Apache'i başlatır,
3. XAMPP yoksa PHP yerleşik sunucusunu (`localhost:8000`) çalıştırır,
4. Tarayıcıyı otomatik açar.

### 4. XAMPP ile klasik yöntem

1. **Apache** > Start
2. Proje klasörünü `C:\xampp\htdocs\kktc_mekan_secici` olarak kopyalayın
3. <http://localhost/kktc_mekan_secici> adresini açın

Veritabanı dosyası ilk istekte `database/kktc.db` olarak otomatik oluşturulur ve seed verileri yüklenir.

## Admin Paneli

- URL: `/admin/login.php`
- Demo hesap: `admin` / `admin123`

> ⚠️ Varsayılan şifre yalnızca yerel geliştirme içindir. Projeyi internete açmadan veya teslim etmeden önce `admin/login.php` ekranındaki demo hesap bilgisini kaldırın ve şifreyi değiştirin (seed şifresi `config.php` içindeki `veritabaniTohumla()` fonksiyonunda tanımlıdır).

## Dosya Yapısı

```text
kktc_mekan_secici/
├── index.php            # Ana sayfa: rastgele seçim + özet
├── mekanlar.php         # Filtrelenebilir mekan listesi
├── config.php           # Sabitler, PDO bağlantısı, şema ve seed verileri
├── KUR.bat              # Windows tek tıkla kurulum betiği
├── api/
│   ├── rastgele.php     # Rastgele mekan uç noktası
│   ├── mekanlar.php     # Filtreli mekan listesi
│   ├── kampanyalar.php  # Kampanyalar
│   └── istatistik.php   # Özet sayılar
├── admin/
│   ├── _auth.php        # Oturum kontrol yardımcıları
│   ├── index.php        # Panele yönlendirme
│   ├── login.php        # Giriş formu
│   ├── panel.php        # CRUD paneli
│   └── logout.php
├── css/style.css
├── js/main.js
├── database/
│   ├── .gitkeep         # Klasörü repoda tutar
│   ├── .htaccess        # Veritabanına web erişimini engeller
│   └── kktc.db          # İlk çalıştırmada oluşur (repoya dahil değil)
├── .editorconfig
├── .gitattributes
├── .gitignore
├── LICENSE
└── README.md
```

## Veritabanı

Üç tablo kullanılır: `mekanlar`, `kampanyalar` ve `yonetici`.
Şema ve örnek veriler `config.php` içindeki `veritabaniKur()` ve `veritabaniTohumla()` fonksiyonlarında tanımlıdır.

**Sıfırlama:** `database/kktc.db` dosyasını silip siteyi yeniden açın; veritabanı ve seed verileri tekrar oluşturulur.

`database/*.db` dosyaları `.gitignore` ile hariç tutulur; böylece yerel veriniz ve her çalıştırmada oluşan değişiklikler repoya karışmaz.

## Güvenlik Notları

Bu proje **eğitim amaçlı** geliştirilmiştir. Canlı ortama taşımadan önce:

- Varsayılan admin şifresini değiştirin ve demo hesap bilgisini arayüzden kaldırın.
- `display_errors` kapalı, `error_reporting` üretim ayarında olsun.
- Uygulamayı HTTPS arkasında çalıştırın ve `session.cookie_secure` / `session.cookie_httponly` ayarlarını açın.
- `database/` klasörüne web üzerinden erişimi engelleyin (Apache için `database/.htaccess` hazır; Nginx kullanıyorsanız `location ^~ /database/ { deny all; }` ekleyin).

## Lisans

[MIT](LICENSE) lisansı ile yayınlanmıştır.

<details>
<summary>1 aylık geliştirme takvimi (okul projesi önerisi)</summary>

| Hafta | Kapsam |
| --- | --- |
| 1 | Gereksinim analizi, sayfa akışları, wireframe ve veritabanı şeması |
| 2 | Ana sayfa + rastgele seçim API'si, mekan listesi ve filtreleme, responsive tasarım |
| 3 | Admin giriş sistemi, mekan/kampanya CRUD, istatistikler ve hata kontrolleri |
| 4 | Uçtan uca test, hata düzeltme, içerik düzenleme ve dokümantasyon |

</details>
