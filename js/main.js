(function () {
    'use strict';

    const body = document.body;
    const page = body.dataset.page;

    const kategoriMeta = {
        cafe: { etiket: 'Cafe', ikon: '☕' },
        bar: { etiket: 'Bar', ikon: '🍸' },
        restoran: { etiket: 'Restoran', ikon: '🍽️' }
    };

    const FAVORI_ANAHTAR = 'kktc_favoriler';
    const TEMA_ANAHTAR = 'kktc_tema';
    const KESIF_ANAHTAR = 'kktc_kesfedilen';
    const GECMIS_ANAHTAR = 'kktc_gecmis';

    function kacis(metin) {
        if (metin === null || metin === undefined) return '';
        return String(metin)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function ucfirst(metin) {
        if (!metin) return '';
        return metin.charAt(0).toUpperCase() + metin.slice(1);
    }

    function kategoriEtiketi(kategori) {
        return (kategoriMeta[kategori] && kategoriMeta[kategori].etiket) || ucfirst(kategori);
    }

    function kategoriIkonu(kategori) {
        return (kategoriMeta[kategori] && kategoriMeta[kategori].ikon) || '📍';
    }

    function haritaBaglantisi(mekan) {
        const sorgu = [mekan.ad, mekan.adres, mekan.sehir, 'KKTC'].filter(Boolean).join(' ');
        return 'https://www.google.com/maps/search/?api=1&query=' + encodeURIComponent(sorgu);
    }

    function bilgiSatiri(ikon, etiket, deger, genis) {
        return `
            <div class="bilgi-satir${genis ? ' genis' : ''}">
                <span class="bilgi-ikon">${ikon}</span>
                <div class="bilgi-metin">
                    <span class="bilgi-etiket">${kacis(etiket)}</span>
                    <span class="bilgi-deger">${kacis(deger)}</span>
                </div>
            </div>
        `;
    }

    /* ---- API adresi ----
     * Adres sayfa URL'ine göre değil, main.js'in kendisiyle göre çözülür.
     * Böylece sayfa hangi yolda açılırsa açılsın api/ her zaman dogru yerde bulunur.
     */
    const API_KOK = (function () {
        const betik = document.currentScript || document.querySelector('script[src*="main.js"]');
        if (betik && betik.src) {
            try {
                return new URL('../', betik.src).href;
            } catch (hata) {
                /* yoksay */
            }
        }
        return location.href;
    })();

    function apiAdresi(yol) {
        return new URL('api/' + yol, API_KOK).href;
    }

    async function apiIste(yol) {
        let yanit;

        try {
            yanit = await fetch(apiAdresi(yol), { cache: 'no-store' });
        } catch (hata) {
            throw new Error('AgHatasi');
        }

        if (!yanit.ok) {
            throw new Error('HTTP ' + yanit.status);
        }

        const metin = await yanit.text();

        try {
            return JSON.parse(metin);
        } catch (hata) {
            throw new Error('JSONOkunamadi');
        }
    }

    function hataGoster(hata) {
        const detay = hata && hata.message ? hata.message : '';
        const httpDurum = detay.match(/^HTTP (\d+)$/);

        if (httpDurum) {
            return 'Sunucu yanıt vermedi (HTTP ' + httpDurum[1] + ').';
        }
        if (detay === 'JSONOkunamadi') {
            return 'Sunucu bozuk yanıt verdi. Sayfayı yenileyin.';
        }
        if (detay === 'AgHatasi') {
            return 'Sunucuya ulaşılamadı. İnternet bağlantınızı kontrol edin.';
        }
        return 'Beklenmeyen bir hata oluştu. Sayfayı yenileyin.';
    }

    /* ---- Şehre göre canlı sayılar ---- */
    async function istatistikleriGetir(sehir) {
        try {
            const veri = await apiIste('istatistik.php?sehir=' + encodeURIComponent(sehir));
            return veri.success ? veri : null;
        } catch (hata) {
            return null;
        }
    }

    function sayilariUygula(pillerId, veri) {
        const piller = document.getElementById(pillerId);
        if (!piller || !veri) return;

        piller.querySelectorAll('.kategori-btn').forEach((btn) => {
            const anahtar = btn.dataset.kategori;
            const alan = btn.querySelector('.kategori-sayi');
            if (alan && veri.kategori[anahtar] !== undefined) {
                alan.textContent = veri.kategori[anahtar];
            }
        });
    }

    function filtreOzetiniGuncelle(veri) {
        const alan = document.getElementById('filtreOzet');
        if (!alan || !veri) return;

        const k = veri.kategori;
        alan.innerHTML = `
            <span class="ozet-rozet">🏙️ <strong>${kacis(veri.sehir)}</strong></span>
            <span class="ozet-rozet ozet-vurgulu">📍 ${veri.toplam} mekan</span>
            <span class="ozet-rozet">☕ ${k.cafe} cafe</span>
            <span class="ozet-rozet">🍸 ${k.bar} bar</span>
            <span class="ozet-rozet">🍽️ ${k.restoran} restoran</span>
        `;
    }

    /* ---- Favoriler ---- */
    function favorileriOku() {
        try {
            const kayit = JSON.parse(localStorage.getItem(FAVORI_ANAHTAR));
            return Array.isArray(kayit) ? kayit.map(String) : [];
        } catch (hata) {
            return [];
        }
    }

    function favoriDegistir(id) {
        const idMetin = String(id);
        const liste = favorileriOku();
        const indeks = liste.indexOf(idMetin);

        if (indeks === -1) {
            liste.push(idMetin);
        } else {
            liste.splice(indeks, 1);
        }

        try {
            localStorage.setItem(FAVORI_ANAHTAR, JSON.stringify(liste));
        } catch (hata) {
            /* localStorage kapalıysa sessiz geç */
        }

        return indeks === -1;
    }

    /* ---- Keşif & son seçimler (localStorage) ---- */
    function kesifleriOku() {
        try {
            const kayit = JSON.parse(localStorage.getItem(KESIF_ANAHTAR));
            return Array.isArray(kayit) ? kayit.map(String) : [];
        } catch (hata) {
            return [];
        }
    }

    function kesifEkle(id) {
        const idMetin = String(id);
        const liste = kesifleriOku();

        if (liste.indexOf(idMetin) !== -1) return false;

        liste.push(idMetin);

        try {
            localStorage.setItem(KESIF_ANAHTAR, JSON.stringify(liste));
        } catch (hata) {
            /* yoksay */
        }

        return true;
    }

    function gecmisOku() {
        try {
            const kayit = JSON.parse(localStorage.getItem(GECMIS_ANAHTAR));
            return Array.isArray(kayit) ? kayit : [];
        } catch (hata) {
            return [];
        }
    }

    function gecmisEkle(mekan) {
        const liste = gecmisOku().filter((kayit) => String(kayit.id) !== String(mekan.id));

        liste.unshift({
            id: mekan.id,
            ad: mekan.ad,
            kategori: mekan.kategori,
            sehir: mekan.sehir
        });

        try {
            localStorage.setItem(GECMIS_ANAHTAR, JSON.stringify(liste.slice(0, 6)));
        } catch (hata) {
            /* yoksay */
        }
    }

    /* ---- Hareket tercihi ---- */
    function azHareket() {
        return !!(window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches);
    }

    /* ---- Tema ---- */
    function temaGuncelle() {
        const koyu = document.documentElement.dataset.theme === 'koyu';
        document.querySelectorAll('.tema-dugme').forEach((dugme) => {
            dugme.textContent = koyu ? '☀️' : '🌙';
            dugme.title = koyu ? 'Açık temaya geç' : 'Koyu temaya geç';
            dugme.setAttribute('aria-label', dugme.title);
        });
    }

    document.querySelectorAll('.tema-dugme').forEach((dugme) => {
        dugme.addEventListener('click', () => {
            const koyu = document.documentElement.dataset.theme === 'koyu';

            if (koyu) {
                delete document.documentElement.dataset.theme;
            } else {
                document.documentElement.dataset.theme = 'koyu';
            }

            try {
                localStorage.setItem(TEMA_ANAHTAR, koyu ? 'acik' : 'koyu');
            } catch (hata) {
                /* yoksay */
            }

            temaGuncelle();
        });
    });

    temaGuncelle();

    /* ---- Header gölgesi ---- */
    const ustMenu = document.querySelector('.ust-menu');
    if (ustMenu) {
        window.addEventListener('scroll', () => {
            ustMenu.classList.toggle('golgenli', window.scrollY > 6);
        }, { passive: true });
    }

    /* ---- Kaydırınca belirme ---- */
    const acilacaklar = document.querySelectorAll('[data-acilir]');
    if (acilacaklar.length && 'IntersectionObserver' in window) {
        acilacaklar.forEach((el) => el.classList.add('acilir'));

        const gozlemci = new IntersectionObserver((girisler) => {
            girisler.forEach((giris) => {
                if (giris.isIntersecting) {
                    giris.target.classList.add('gorunur');
                    gozlemci.unobserve(giris.target);
                }
            });
        }, { threshold: 0.08, rootMargin: '0px 0px -40px 0px' });

        acilacaklar.forEach((el) => gozlemci.observe(el));
    }

    function sayacAnimasyonuBaslat() {
        const sayaclar = document.querySelectorAll('[data-sayac]');
        if (!sayaclar.length) return;

        sayaclar.forEach((el) => {
            const hedef = Number(el.dataset.hedef) || 0;
            if (hedef <= 0) {
                el.textContent = '0';
                return;
            }
            let say = 0;
            const adim = Math.max(1, Math.ceil(hedef / 40));
            const zamanlayici = setInterval(() => {
                say += adim;
                if (say >= hedef) {
                    say = hedef;
                    clearInterval(zamanlayici);
                }
                el.textContent = say;
            }, 28);
        });
    }

    function konfetiYarat(say = 26) {
        const alan = document.querySelector('.secim-sahnesi');
        if (!alan) return;
        for (let i = 0; i < say; i++) {
            const p = document.createElement('span');
            p.className = 'konfeti';
            p.style.setProperty('--x', (Math.random() * 360) + 'deg');
            p.style.left = Math.random() * 100 + '%';
            p.style.animationDelay = (Math.random() * 0.6) + 's';
            p.style.background = ['#7c8cff', '#ff9f1c', '#22d3ee', '#34d399', '#f472b6', '#fbbf24'][i % 6];
            alan.appendChild(p);
            setTimeout(() => p.remove(), 900);
        }
    }

    /* ================= Ana Sayfa ================= */
    function indexSayfasiCalistir() {
        const kategoriGrid = document.getElementById('kategoriGrid');
        const sehirSecimi = document.getElementById('sehirSecimi');
        const rastgeleSecBtn = document.getElementById('rastgeleSecBtn');
        const btnYazi = rastgeleSecBtn.querySelector('.btn-yazi');
        const sonucKart = document.getElementById('sonucKart');
        const sonucIcerik = sonucKart.querySelector('.sonuc-icerik');
        const durumMesaji = document.getElementById('durumMesaji');
        const kampanyaListesi = document.getElementById('kampanyaListesi');
        const kesifCubugu = document.getElementById('kesifCubugu');
        const gecmisListesi = document.getElementById('gecmisListesi');
        const surprizDugme = document.getElementById('surprizDugme');
        const moodCipleri = document.getElementById('moodCipleri');

        let seciliKategori = 'tum';
        let surprizAktif = false;
        let ceviriyor = false;

        /* ---- Keşif ilerleme çubuğu ---- */
        function kesifCubuguGuncelle() {
            if (!kesifCubugu) return;

            const toplam = Number(kesifCubugu.dataset.toplam) || 0;
            const kesfedilen = kesifleriOku().length;
            const oran = toplam > 0 ? Math.min(100, Math.round((kesfedilen / toplam) * 100)) : 0;

            kesifCubugu.hidden = false;
            document.getElementById('kesifDeger').textContent = kesfedilen + ' / ' + toplam;
            document.getElementById('kesifDolgu').style.width = oran + '%';

            const alt = document.getElementById('kesifAlt');
            if (kesfedilen === 0) {
                alt.textContent = 'İlk mekanını seç, koleksiyonun başlasın!';
            } else if (toplam > 0 && kesfedilen >= toplam) {
                alt.textContent = '🏆 Hepsini keşfettin! Sen bir efsanesin.';
            } else {
                alt.textContent = 'Yolun %' + oran + ' tamam — hadi devam et!';
            }
        }

        /* ---- Son seçimler ---- */
        function gecmisCiz() {
            if (!gecmisListesi) return;

            const liste = gecmisOku();
            if (!liste.length) {
                gecmisListesi.hidden = true;
                gecmisListesi.innerHTML = '';
                return;
            }

            gecmisListesi.hidden = false;
            gecmisListesi.innerHTML = '<span class="gecmis-baslik">Son seçimler</span>' + liste.map((mekan) => `
                <button type="button" class="gecmis-cip" data-id="${kacis(mekan.id)}">
                    ${kategoriIkonu(mekan.kategori)} ${kacis(mekan.ad)}
                </button>
            `).join('');
        }

        /* ---- Paylaşım ---- */
        function paylasMetni(mekan) {
            const link = location.origin + location.pathname;
            const adres = mekan.adres ? '\n📍 ' + mekan.adres : '';

            return '"Nereye gidelim?" derdine son! Bana şunu seçti:\n' +
                mekan.ad + ' — ' + kategoriEtiketi(mekan.kategori) + ' · ' + mekan.sehir + adres +
                '\nSen de çevir: ' + link;
        }

        function paylasKur(mekan) {
            const buton = document.getElementById('paylasBtn');
            const wa = document.getElementById('waBtn');
            const metin = paylasMetni(mekan);

            if (wa) {
                wa.href = 'https://wa.me/?text=' + encodeURIComponent(metin);
            }

            if (!buton) return;

            buton.addEventListener('click', async () => {
                let tamam = false;

                try {
                    if (navigator.clipboard && navigator.clipboard.writeText) {
                        await navigator.clipboard.writeText(metin);
                        tamam = true;
                    }
                } catch (hata) {
                    /* pano izni yoksa eski yönteme düş */
                }

                if (!tamam) {
                    try {
                        const alan = document.createElement('textarea');
                        alan.value = metin;
                        alan.setAttribute('readonly', '');
                        alan.style.position = 'fixed';
                        alan.style.opacity = '0';
                        document.body.appendChild(alan);
                        alan.select();
                        tamam = document.execCommand('copy');
                        alan.remove();
                    } catch (hata) {
                        /* yoksay */
                    }
                }

                const eski = buton.textContent;
                buton.textContent = tamam ? '✓ Kopyalandı!' : '⚠️ Kopyalanamadı';
                setTimeout(() => { buton.textContent = eski; }, 1600);
            });
        }

        /* ---- Slot çevirme animasyonu ---- */
        function cevirAnimasyonu(havuz) {
            return new Promise((resolve) => {
                const ray = rastgeleSecBtn.querySelector('.cevir-ray');
                const pencere = rastgeleSecBtn.querySelector('.cevir-pencere');

                if (!ray || !pencere || havuz.length < 2 || azHareket()) {
                    resolve();
                    return;
                }

                const kazanan = havuz[havuz.length - 1];
                const isimler = havuz.slice(0, -1)
                    .sort(() => Math.random() - 0.5)
                    .slice(0, 24)
                    .map((mekan) => mekan.ad);
                isimler.push(kazanan.ad);

                rastgeleSecBtn.setAttribute('data-ceviriyor', '');
                ray.style.transition = 'none';
                ray.style.transform = 'translateY(0)';
                ray.innerHTML = isimler.map((ad) => `<span class="cevir-isim">${kacis(ad)}</span>`).join('');

                const ilk = ray.querySelector('.cevir-isim');
                const satir = ilk ? ilk.offsetHeight : 52;
                void ray.offsetHeight;

                ray.style.transition = 'transform 1.15s cubic-bezier(0.16, 0.84, 0.2, 1)';
                ray.style.transform = `translateY(-${(isimler.length - 1) * satir}px)`;

                setTimeout(() => {
                    rastgeleSecBtn.removeAttribute('data-ceviriyor');
                    resolve();
                }, 1200);
            });
        }

        /* ---- Sonuç kartı ---- */
        function sonucCiz(mekan) {
            const ilkKez = kesifEkle(mekan.id);
            kesifCubuguGuncelle();

            const kampanyaAlani = mekan.kampanya_var
                ? `<div class="kampanya-vurgu">
                        <span class="rozet rozet-kampanya">Bugünün Kampanyası</span>
                        <h4>${kacis(mekan.kampanya_baslik)}</h4>
                        <p>${kacis(mekan.kampanya_aciklama || '')}</p>
                   </div>`
                : '';

            sonucIcerik.innerHTML = `
                <div class="sonuc-ust">
                    <span class="mekan-avatar kategori-${kacis(mekan.kategori)}">${kategoriIkonu(mekan.kategori)}</span>
                    <div class="mekan-baslik-alani">
                        <h4>${kacis(mekan.ad)}</h4>
                        <span class="mekan-alt">${kacis(kategoriEtiketi(mekan.kategori))} · ${kacis(mekan.sehir)}</span>
                    </div>
                </div>

                <div class="cip-listesi">
                    <span class="cip">${kategoriIkonu(mekan.kategori)} ${kacis(kategoriEtiketi(mekan.kategori))}</span>
                    <span class="cip">🏙️ ${kacis(mekan.sehir)}</span>
                    ${ilkKez ? '<span class="rozet rozet-yeni">🎯 İlk kez keşfediyorsun!</span>' : ''}
                    ${Number(mekan.populer) ? '<span class="rozet rozet-populer">⭐ Popüler</span>' : ''}
                    ${mekan.kampanya_var ? '<span class="cip">🎁 Kampanyalı</span>' : ''}
                </div>

                <div class="bilgi-listesi">
                    ${bilgiSatiri('📍', 'Adres', mekan.adres || 'Bilgi eklenmemiş')}
                    ${bilgiSatiri('☎️', 'Telefon', mekan.telefon || 'Bilgi eklenmemiş')}
                    ${bilgiSatiri('📝', 'Açıklama', mekan.aciklama || 'Açıklama eklenmemiş', true)}
                </div>

                ${kampanyaAlani}

                <div class="sonuc-butonlar">
                    <a class="buton buton-birincil" href="${kacis(haritaBaglantisi(mekan))}" target="_blank" rel="noopener">📍 Haritada Gör</a>
                    <button type="button" class="buton" id="tekrarSecBtn">🎲 Tekrar Seç</button>
                    <button type="button" class="buton" id="paylasBtn">📤 Arkadaşına gönder</button>
                    <a class="buton" id="waBtn" target="_blank" rel="noopener" href="#">💬 WhatsApp</a>
                </div>
            `;

            const tekrarSecBtn = document.getElementById('tekrarSecBtn');
            if (tekrarSecBtn) {
                tekrarSecBtn.addEventListener('click', () => rastgeleSecBtn.click());
            }

            paylasKur(mekan);

            sonucKart.classList.add('gorunur');
            sonucKart.classList.remove('animasyon');
            void sonucKart.offsetWidth;
            sonucKart.classList.add('animasyon');

            durumMesaji.textContent = ilkKez ? 'Yeni bir mekan keşfettin! 🎉' : 'Yeni mekan seçildi!';
            konfetiYarat(24);

            gecmisEkle(mekan);
            gecmisCiz();
        }

        async function sehirSayilariniGuncelle() {
            const veri = await istatistikleriGetir(sehirSecimi.value);
            sayilariUygula('kategoriGrid', veri);
            filtreOzetiniGuncelle(veri);
        }

        kategoriGrid.addEventListener('click', (event) => {
            const btn = event.target.closest('.kategori-btn');
            if (!btn) return;

            seciliKategori = btn.dataset.kategori;
            kategoriGrid.querySelectorAll('.kategori-btn').forEach((el) => el.classList.remove('secili'));
            btn.classList.add('secili');
        });

        sehirSecimi.addEventListener('change', sehirSayilariniGuncelle);

        function tiklamaEfektiOlustur(event) {
            const rect = rastgeleSecBtn.getBoundingClientRect();
            const ripple = document.createElement('span');
            ripple.className = 'btn-ripple';

            const boyut = Math.max(rect.width, rect.height) * 0.56;
            const x = event.clientX - rect.left - boyut / 2;
            const y = event.clientY - rect.top - boyut / 2;

            ripple.style.width = `${boyut}px`;
            ripple.style.height = `${boyut}px`;
            ripple.style.left = `${x}px`;
            ripple.style.top = `${y}px`;

            rastgeleSecBtn.appendChild(ripple);
            rastgeleSecBtn.classList.add('tiklandi');

            setTimeout(() => {
                ripple.remove();
                rastgeleSecBtn.classList.remove('tiklandi');
            }, 680);
        }

        async function kampanyalariGetir() {
            try {
                const veri = await apiIste('kampanyalar.php');

                if (!veri.success || !veri.data.length) {
                    kampanyaListesi.innerHTML = `
                        <div class="bos-durum">
                            <span class="bos-ikon">🎁</span>
                            <p>Bugün için aktif kampanya bulunmuyor.</p>
                        </div>
                    `;
                    return;
                }

                kampanyaListesi.innerHTML = veri.data.map((kampanya) => `
                    <article class="kampanya-item">
                        <span class="rozet rozet-kampanya">Kampanya</span>
                        <h4>${kacis(kampanya.baslik)}</h4>
                        <p>${kacis(kampanya.aciklama || '')}</p>
                        <small>${kacis(kampanya.mekan_adi)} · ${kacis(kampanya.sehir)} · ${kacis(kategoriEtiketi(kampanya.kategori))}</small>
                    </article>
                `).join('');
            } catch (hata) {
                kampanyaListesi.innerHTML = '<div class="bos-durum"><p>' + kacis(hataGoster(hata)) + '</p></div>';
            }
        }
        async function rastgeleMekanSec(event) {
            if (ceviriyor) return;

            ceviriyor = true;
            tiklamaEfektiOlustur(event);
            rastgeleSecBtn.disabled = true;
            durumMesaji.textContent = 'Sana uygun mekan aranıyor...';

            const params = new URLSearchParams({
                sehir: sehirSecimi.value,
                kategori: seciliKategori
            });

            try {
                let havuz = [];

                try {
                    const havuzVeri = await apiIste(`mekanlar.php?${params.toString()}`);
                    if (havuzVeri && havuzVeri.success && Array.isArray(havuzVeri.data)) {
                        havuz = havuzVeri.data;
                    }
                } catch (hata) {
                    /* havuz alınamazsa tekil seçime düşülür */
                }

                if (surprizAktif && havuz.length) {
                    const kesif = new Set(kesifleriOku());
                    const yeniler = havuz.filter((mekan) => !kesif.has(String(mekan.id)));
                    if (yeniler.length) havuz = yeniler;
                }

                let mekan = null;

                if (havuz.length) {
                    const secilen = havuz[Math.floor(Math.random() * havuz.length)];
                    const digerleri = havuz
                        .filter((aday) => String(aday.id) !== String(secilen.id))
                        .slice(0, 30);
                    digerleri.push(secilen);

                    btnYazi.innerHTML = 'Çeviriyor...';
                    await cevirAnimasyonu(digerleri);

                    let detay = null;
                    try {
                        detay = await apiIste(`rastgele.php?${params.toString()}&id=${encodeURIComponent(secilen.id)}`);
                    } catch (hata) {
                        /* detay alınamazsa havuz kaydı kullanılır */
                    }

                    mekan = (detay && detay.success && detay.data) ? detay.data : secilen;
                } else {
                    const veri = await apiIste(`rastgele.php?${params.toString()}`);

                    if (!veri.success || !veri.data) {
                        sonucIcerik.innerHTML = `
                            <div class="bos-durum">
                                <span class="bos-ikon">🤔</span>
                                <p>${kacis(veri.message || 'Mekan bulunamadı.')}</p>
                            </div>
                        `;
                        durumMesaji.textContent = veri.message || 'Mekan bulunamadı.';
                        sonucKart.classList.remove('animasyon');
                        return;
                    }

                    mekan = veri.data;
                }

                sonucCiz(mekan);
            } catch (hata) {
                sonucIcerik.innerHTML = '<div class="bos-durum"><span class="bos-ikon">📡</span><p>' + kacis(hataGoster(hata)) + '</p></div>';
                durumMesaji.textContent = hataGoster(hata);
            } finally {
                ceviriyor = false;
                rastgeleSecBtn.disabled = false;
                rastgeleSecBtn.removeAttribute('data-ceviriyor');
                btnYazi.innerHTML = 'Rastgele<br>Seç';
            }
        }

        if (surprizDugme) {
            surprizDugme.addEventListener('click', () => {
                surprizAktif = !surprizAktif;
                surprizDugme.classList.toggle('aktif', surprizAktif);
                surprizDugme.setAttribute('aria-pressed', surprizAktif ? 'true' : 'false');
                surprizDugme.textContent = surprizAktif
                    ? '🎲 Sürpriz açık: gitmediklerim'
                    : '🎲 Sürpriz: gitmediklerimden';
                durumMesaji.textContent = surprizAktif
                    ? 'Sürpriz modu açık — hiç gitmediğin yerler önerilir ✨'
                    : '';
            });
        }

        if (moodCipleri) {
            moodCipleri.addEventListener('click', (event) => {
                const cip = event.target.closest('.mood-cip');
                if (!cip) return;

                const kategori = cip.dataset.kategori || 'tum';
                const sehir = cip.dataset.sehir || 'Tümü';

                seciliKategori = kategori;
                kategoriGrid.querySelectorAll('.kategori-btn').forEach((el) => {
                    el.classList.toggle('secili', el.dataset.kategori === kategori);
                });
                sehirSecimi.value = sehir;

                moodCipleri.querySelectorAll('.mood-cip').forEach((el) => {
                    el.classList.toggle('aktif', el === cip);
                });

                sehirSayilariniGuncelle();
                durumMesaji.textContent = cip.dataset.mesaj || '';

                rastgeleSecBtn.scrollIntoView({
                    behavior: azHareket() ? 'auto' : 'smooth',
                    block: 'center'
                });
            });
        }

        if (gecmisListesi) {
            gecmisListesi.addEventListener('click', async (event) => {
                const cip = event.target.closest('.gecmis-cip');
                if (!cip || ceviriyor) return;

                ceviriyor = true;
                durumMesaji.textContent = 'Seçim geri yükleniyor...';

                try {
                    const yol = 'rastgele.php?sehir=' + encodeURIComponent('Tümü') +
                        '&kategori=tum&id=' + encodeURIComponent(cip.dataset.id);
                    const veri = await apiIste(yol);

                    if (veri.success && veri.data) {
                        sonucCiz(veri.data);
                    } else {
                        durumMesaji.textContent = veri.message || 'Mekan yüklenemedi.';
                    }
                } catch (hata) {
                    durumMesaji.textContent = hataGoster(hata);
                } finally {
                    ceviriyor = false;
                }
            });
        }

        document.addEventListener('keydown', (event) => {
            if (event.code !== 'Space' || event.repeat) return;

            const hedef = event.target;
            if (hedef && (hedef.tagName === 'INPUT' || hedef.tagName === 'TEXTAREA' || hedef.tagName === 'SELECT' || hedef.isContentEditable)) {
                return;
            }

            event.preventDefault();
            rastgeleSecBtn.click();
        });

        rastgeleSecBtn.addEventListener('click', rastgeleMekanSec);
        kampanyalariGetir();
        sehirSayilariniGuncelle();
        sayacAnimasyonuBaslat();
        kesifCubuguGuncelle();
        gecmisCiz();
    }

    /* ================= Mekanlar Sayfası ================= */
    function mekanlarSayfasiCalistir() {
        const pilleri = document.getElementById('listeKategoriPilleri');
        const listeSehir = document.getElementById('listeSehir');
        const listeSiralama = document.getElementById('listeSiralama');
        const listeArama = document.getElementById('listeArama');
        const mekanListe = document.getElementById('mekanListe');
        const listeBaslik = document.getElementById('listeBaslik');
        const ozetRozetler = document.getElementById('ozetRozetler');
        const favoriFiltre = document.getElementById('favoriFiltre');
        const favoriSayi = document.getElementById('favoriSayi');
        const filtreTemizle = document.getElementById('filtreTemizle');

        let seciliKategori = pilleri.querySelector('.kategori-btn.secili')
            ? pilleri.querySelector('.kategori-btn.secili').dataset.kategori
            : 'tum';
        let sonuclar = [];
        let favoriSadece = false;
        let aktifIstatistik = null;

        async function sehirSayilariniGuncelle() {
            aktifIstatistik = await istatistikleriGetir(listeSehir.value);
            sayilariUygula('listeKategoriPilleri', aktifIstatistik);
            filtreOzetiniGuncelle(aktifIstatistik);
        }

        function kategoriSec(anahtar) {
            seciliKategori = anahtar;
            pilleri.querySelectorAll('.kategori-btn').forEach((el) => {
                el.classList.toggle('secili', el.dataset.kategori === anahtar);
            });
        }

        function urlGuncelle() {
            if (!window.history || !window.history.replaceState) return;

            const params = new URLSearchParams();
            if (listeSehir.value !== 'Tümü') params.set('sehir', listeSehir.value);
            if (seciliKategori !== 'tum') params.set('kategori', seciliKategori);

            const sorgu = params.toString();
            window.history.replaceState(null, '', sorgu ? `?${sorgu}` : location.pathname);
        }

        function favoriSayacGuncelle() {
            favoriSayi.textContent = String(favorileriOku().length);
        }

        pilleri.addEventListener('click', (event) => {
            const btn = event.target.closest('.kategori-btn');
            if (!btn) return;

            kategoriSec(btn.dataset.kategori);
            urlGuncelle();
            mekanlariGetir();
        });

        function sirala(liste) {
            const kopya = liste.slice();
            const anahtar = listeSiralama.value;

            if (anahtar === 'ad') {
                kopya.sort((a, b) => a.ad.localeCompare(b.ad, 'tr'));
            } else if (anahtar === 'kategori') {
                kopya.sort((a, b) => a.kategori.localeCompare(b.kategori, 'tr') || a.ad.localeCompare(b.ad, 'tr'));
            } else if (anahtar === 'populer') {
                kopya.sort((a, b) => (Number(b.populer) - Number(a.populer)) || a.ad.localeCompare(b.ad, 'tr'));
            } else if (anahtar === 'kampanya') {
                kopya.sort((a, b) => (Number(b.kampanya_var) - Number(a.kampanya_var)) || a.ad.localeCompare(b.ad, 'tr'));
            } else {
                kopya.sort((a, b) => a.sehir.localeCompare(b.sehir, 'tr') || a.ad.localeCompare(b.ad, 'tr'));
            }

            return kopya;
        }

        function gosterilecekler() {
            if (!favoriSadece) return sonuclar;
            const favoriSet = new Set(favorileriOku());
            return sonuclar.filter((mekan) => favoriSet.has(String(mekan.id)));
        }

        function kartOlustur(mekan, favoriSet) {
            const favori = favoriSet.has(String(mekan.id));
            const kampanyaRozet = Number(mekan.kampanya_var)
                ? '<span class="rozet rozet-kampanya">🎁 Kampanyalı</span>'
                : '';
            const kategori = kacis(mekan.kategori);

            return `
                <article class="mekan-kart kart--${kategori}" data-id="${mekan.id}">
                    <div class="mekan-kart-ust">
                        <span class="mekan-avatar kategori-${kategori}">${kategoriIkonu(mekan.kategori)}</span>
                        <div class="mekan-kart-baslik">
                            <h4>${kacis(mekan.ad)}</h4>
                            <span class="mekan-alt">${kacis(kategoriEtiketi(mekan.kategori))} · ${kacis(mekan.sehir)}</span>
                        </div>
                        <button type="button" class="favori-btn${favori ? ' aktif' : ''}" data-favori
                                title="${favori ? 'Favorilerden çıkar' : 'Favorilere ekle'}"
                                aria-label="Favorilere ekle">${favori ? '★' : '☆'}</button>
                    </div>

                    <div class="cip-listesi">
                        <span class="cip">${kategoriIkonu(mekan.kategori)} ${kacis(kategoriEtiketi(mekan.kategori))}</span>
                        <span class="cip">🏙️ ${kacis(mekan.sehir)}</span>
                        ${Number(mekan.populer) ? '<span class="rozet rozet-populer">⭐ Popüler</span>' : ''}
                        ${kampanyaRozet}
                    </div>

                    <p class="kart-aciklama">${kacis(mekan.aciklama || 'Açıklama eklenmemiş.')}</p>

                    <div class="kart-bilgi"><span class="bilgi-ikon">📍</span><span>${kacis(mekan.adres || 'Adres bilgisi eklenmemiş')}</span></div>
                    <div class="kart-bilgi"><span class="bilgi-ikon">☎️</span><span>${kacis(mekan.telefon || 'Telefon bilgisi eklenmemiş')}</span></div>

                    <div class="kart-alt">
                        <a class="buton buton-birincil" href="${kacis(haritaBaglantisi(mekan))}" target="_blank" rel="noopener">📍 Haritada Gör</a>
                    </div>
                </article>
            `;
        }

        function ciz() {
            const favoriSet = new Set(favorileriOku());
            const liste = gosterilecekler();

            listeBaslik.textContent = `Mekan Listesi (${liste.length})`;

            if (!liste.length) {
                mekanListe.innerHTML = favoriSadece
                    ? `<div class="bos-durum"><span class="bos-ikon">⭐</span><p>Henüz favori eklemediniz. Kartlardaki yıldız simgesine basarak favorilere ekleyin.</p></div>`
                    : `<div class="bos-durum"><span class="bos-ikon">🔍</span><p>Seçtiğiniz filtrelere uygun mekan bulunamadı.</p></div>`;
                return;
            }

            mekanListe.innerHTML = sirala(liste).map((mekan) => kartOlustur(mekan, favoriSet)).join('');
        }

        function ozetleriGuncelle(veri) {
            if (!veri.success) {
                ozetRozetler.innerHTML = '';
                return;
            }

            const kategori = seciliKategori === 'tum' ? 'Tümü' : kategoriEtiketi(seciliKategori);
            const sehir = listeSehir.value;
            const arama = listeArama.value.trim();

            ozetRozetler.innerHTML = `
                <span class="rozet">${kacis(kategori)}</span>
                <span class="rozet">🏙️ ${kacis(sehir)}</span>
                ${arama ? `<span class="rozet">🔍 ${kacis(arama)}</span>` : ''}
                ${favoriSadece ? '<span class="rozet rozet-kampanya">⭐ Sadece favoriler</span>' : ''}
            `;
        }

        async function mekanlariGetir() {
            mekanListe.innerHTML = `
                <div class="iskelet iskelet-kart"></div>
                <div class="iskelet iskelet-kart"></div>
                <div class="iskelet iskelet-kart"></div>
            `;

            try {
                const params = new URLSearchParams({
                    sehir: listeSehir.value,
                    kategori: seciliKategori,
                    arama: listeArama.value.trim()
                });

                const veri = await apiIste(`mekanlar.php?${params.toString()}`);

                if (!veri.success) {
                    mekanListe.innerHTML = `<div class="bos-durum"><p>${kacis(veri.message || 'Mekanlar getirilemedi.')}</p></div>`;
                    return;
                }

                sonuclar = veri.data;
                ozetleriGuncelle(veri);
                favoriSayacGuncelle();
                ciz();
            } catch (hata) {
                mekanListe.innerHTML = '<div class="bos-durum"><span class="bos-ikon">📡</span><p>' + kacis(hataGoster(hata)) + '</p><p class="soluk">Tekrar denemek için sayfayı yenileyin.</p></div>';
            }
        }

        mekanListe.addEventListener('click', (event) => {
            const favoriBtn = event.target.closest('.favori-btn');
            if (!favoriBtn) return;

            const kart = favoriBtn.closest('.mekan-kart');
            const aktif = favoriDegistir(kart.dataset.id);

            favoriBtn.classList.add('pop');
            setTimeout(() => favoriBtn.classList.remove('pop'), 380);

            favoriBtn.classList.toggle('aktif', aktif);
            favoriBtn.textContent = aktif ? '★' : '☆';
            favoriBtn.title = aktif ? 'Favorilerden çıkar' : 'Favorilere ekle';
            favoriSayacGuncelle();

            if (favoriSadece) {
                ciz();
                ozetleriGuncelle({ success: true });
            }
        });

        favoriFiltre.addEventListener('click', () => {
            favoriSadece = !favoriSadece;
            favoriFiltre.classList.toggle('aktif', favoriSadece);
            ozetleriGuncelle({ success: true });
            ciz();
        });

        filtreTemizle.addEventListener('click', () => {
            listeSehir.value = 'Tümü';
            listeArama.value = '';
            listeSiralama.value = 'sehir';
            favoriSadece = false;
            favoriFiltre.classList.remove('aktif');
            kategoriSec('tum');
            urlGuncelle();
            sehirSayilariniGuncelle();
            mekanlariGetir();
        });

        let aramaZamanlayici;
        listeArama.addEventListener('input', () => {
            clearTimeout(aramaZamanlayici);
            aramaZamanlayici = setTimeout(mekanlariGetir, 250);
        });

        listeSehir.addEventListener('change', () => {
            urlGuncelle();
            sehirSayilariniGuncelle();
            mekanlariGetir();
        });

        listeSiralama.addEventListener('change', ciz);

        favoriSayacGuncelle();
        sehirSayilariniGuncelle();
        mekanlariGetir();
    }

    if (page === 'index') {
        indexSayfasiCalistir();
    }

    if (page === 'mekanlar') {
        mekanlarSayfasiCalistir();
    }
})();
