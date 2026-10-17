(function () {
    const body = document.body;
    const page = body.dataset.page;

    const kategoriEtiketleri = {
        cafe: 'Cafe',
        bar: 'Bar',
        restoran: 'Restoran'
    };

    const kategoriIkonlari = {
        cafe: '☕',
        bar: '🍸',
        restoran: '🍽️'
    };

    const kacis = (metin) => {
        if (metin === null || metin === undefined) return '';
        return String(metin)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    };

    function indexSayfasiCalistir() {
        const kategoriGrid = document.getElementById('kategoriGrid');
        const sehirSecimi = document.getElementById('sehirSecimi');
        const rastgeleSecBtn = document.getElementById('rastgeleSecBtn');
        const btnYazi = rastgeleSecBtn.querySelector('.btn-yazi');
        const sonucKart = document.getElementById('sonucKart');
        const sonucIcerik = sonucKart.querySelector('.sonuc-icerik');
        const durumMesaji = document.getElementById('durumMesaji');
        const kampanyaListesi = document.getElementById('kampanyaListesi');

        let seciliKategori = 'tum';

        kategoriGrid.addEventListener('click', (event) => {
            const btn = event.target.closest('.kategori-btn');
            if (!btn) return;

            seciliKategori = btn.dataset.kategori;
            kategoriGrid.querySelectorAll('.kategori-btn').forEach((el) => el.classList.remove('secili'));
            btn.classList.add('secili');
        });

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
                const yanit = await fetch('api/kampanyalar.php');
                const veri = await yanit.json();

                if (!veri.success || !veri.data.length) {
                    kampanyaListesi.innerHTML = '<p>Bugün için aktif kampanya bulunmuyor.</p>';
                    return;
                }

                kampanyaListesi.innerHTML = veri.data.map((kampanya) => `
                    <article class="kampanya-item">
                        <span class="rozet">Kampanya</span>
                        <h4>${kacis(kampanya.baslik)}</h4>
                        <p>${kacis(kampanya.aciklama || '')}</p>
                        <small>${kacis(kampanya.mekan_adi)} · ${kacis(kampanya.sehir)} · ${kacis(kategoriEtiketleri[kampanya.kategori] || kampanya.kategori)}</small>
                    </article>
                `).join('');
            } catch (hata) {
                kampanyaListesi.innerHTML = '<p>Kampanyalar alınırken bir hata oluştu.</p>';
            }
        }

        async function rastgeleMekanSec(event) {
            tiklamaEfektiOlustur(event);
            rastgeleSecBtn.disabled = true;
            btnYazi.textContent = 'Seçiliyor...';
            durumMesaji.textContent = 'Sana uygun mekan aranıyor...';

            try {
                const params = new URLSearchParams({
                    sehir: sehirSecimi.value,
                    kategori: seciliKategori
                });

                const yanit = await fetch(`api/rastgele.php?${params.toString()}`);
                const veri = await yanit.json();

                if (!veri.success || !veri.data) {
                    sonucIcerik.innerHTML = `<p>${kacis(veri.message || 'Mekan bulunamadı.')}</p>`;
                    durumMesaji.textContent = veri.message || 'Mekan bulunamadı.';
                    sonucKart.classList.remove('animasyon');
                    return;
                }

                const mekan = veri.data;
                const kampanyaAlani = mekan.kampanya_var
                    ? `<div class="kampanya-vurgu"><span class="rozet">Bugünün Kampanyası</span><h4>${kacis(mekan.kampanya_baslik)}</h4><p>${kacis(mekan.kampanya_aciklama || '')}</p></div>`
                    : '<p class="soluk">Bu mekan için bugün aktif kampanya yok.</p>';

                sonucIcerik.innerHTML = `
                    <article>
                        <h4>${kacis(mekan.ad)}</h4>
                        <p><strong>Kategori:</strong> ${kacis(kategoriIkonlari[mekan.kategori] || '📍')} ${kacis(kategoriEtiketleri[mekan.kategori] || mekan.kategori)}</p>
                        <p><strong>Şehir:</strong> ${kacis(mekan.sehir)}</p>
                        <p><strong>Adres:</strong> ${kacis(mekan.adres || 'Bilgi yok')}</p>
                        <p><strong>Telefon:</strong> ${kacis(mekan.telefon || 'Bilgi yok')}</p>
                        <p><strong>Açıklama:</strong> ${kacis(mekan.aciklama || 'Açıklama eklenmemiş')}</p>
                        ${kampanyaAlani}
                    </article>
                `;

                sonucKart.classList.remove('animasyon');
                void sonucKart.offsetWidth;
                sonucKart.classList.add('animasyon');
                durumMesaji.textContent = 'Yeni mekan seçildi!';
            } catch (hata) {
                sonucIcerik.innerHTML = '<p>Bağlantı hatası oluştu. Lütfen tekrar deneyin.</p>';
                durumMesaji.textContent = 'Sunucuya bağlanılamadı.';
            } finally {
                rastgeleSecBtn.disabled = false;
                btnYazi.textContent = 'Rastgele Seç';
            }
        }

        rastgeleSecBtn.addEventListener('click', rastgeleMekanSec);
        kampanyalariGetir();
    }

    function mekanlarSayfasiCalistir() {
        const listeSehir = document.getElementById('listeSehir');
        const listeKategori = document.getElementById('listeKategori');
        const listeArama = document.getElementById('listeArama');
        const mekanListe = document.getElementById('mekanListe');
        const listeBaslik = document.getElementById('listeBaslik');

        async function mekanlariGetir() {
            mekanListe.innerHTML = '<p>Yükleniyor...</p>';

            try {
                const params = new URLSearchParams({
                    sehir: listeSehir.value,
                    kategori: listeKategori.value,
                    arama: listeArama.value.trim()
                });

                const yanit = await fetch(`api/mekanlar.php?${params.toString()}`);
                const veri = await yanit.json();

                if (!veri.success) {
                    mekanListe.innerHTML = `<p>${kacis(veri.message || 'Mekanlar getirilemedi.')}</p>`;
                    return;
                }

                listeBaslik.textContent = `Mekan Listesi (${veri.count})`;

                if (!veri.data.length) {
                    mekanListe.innerHTML = '<p>Seçtiğiniz filtrelere uygun mekan bulunamadı.</p>';
                    return;
                }

                mekanListe.innerHTML = veri.data.map((mekan) => `
                    <article class="liste-item">
                        <h4>${kacis(mekan.ad)} ${Number(mekan.kampanya_var) ? '<span class="rozet">Kampanyalı</span>' : ''}</h4>
                        <p><strong>Kategori:</strong> ${kacis(kategoriEtiketleri[mekan.kategori] || mekan.kategori)}</p>
                        <p><strong>Şehir:</strong> ${kacis(mekan.sehir)}</p>
                        <p><strong>Adres:</strong> ${kacis(mekan.adres || 'Bilgi yok')}</p>
                        <p><strong>Telefon:</strong> ${kacis(mekan.telefon || 'Bilgi yok')}</p>
                        <p><strong>Açıklama:</strong> ${kacis(mekan.aciklama || 'Açıklama eklenmemiş')}</p>
                    </article>
                `).join('');
            } catch (hata) {
                mekanListe.innerHTML = '<p>Mekanlar alınırken bir hata oluştu.</p>';
            }
        }

        let aramaZamanlayici;
        listeArama.addEventListener('input', () => {
            clearTimeout(aramaZamanlayici);
            aramaZamanlayici = setTimeout(mekanlariGetir, 250);
        });

        listeSehir.addEventListener('change', mekanlariGetir);
        listeKategori.addEventListener('change', mekanlariGetir);

        mekanlariGetir();
    }

    if (page === 'index') {
        indexSayfasiCalistir();
    }

    if (page === 'mekanlar') {
        mekanlarSayfasiCalistir();
    }
})();
