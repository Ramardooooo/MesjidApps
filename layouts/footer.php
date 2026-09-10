    </main>

    <!-- Footer Bersama -->
    <footer class="bg-white border-t border-antique-300/30 py-4 mt-8 no-print">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-xs text-warm-800/60">
            <p>© <?= date('Y') ?> Masjid Nurul Iman · Menjaga Amanah, Memakmurkan Masjid</p>
        </div>
    </footer>

</div>

<!-- Script Drawer Sidebar Mobile -->
<script>
    const sidebar = document.getElementById('mainSidebar');
    const overlay = document.getElementById('sidebarOverlay');
    const toggleBtn = document.getElementById('sidebarToggle');
    const closeBtn = document.getElementById('sidebarClose');

    function bukaSidebar() {
        if (!sidebar || !overlay) return;
        sidebar.classList.remove('-translate-x-full');
        overlay.classList.remove('hidden');
        document.body.classList.add('overflow-hidden', 'lg:overflow-auto');
    }

    function tutupSidebar() {
        if (!sidebar || !overlay) return;
        sidebar.classList.add('-translate-x-full');
        overlay.classList.add('hidden');
        document.body.classList.remove('overflow-hidden', 'lg:overflow-auto');
    }

    if (toggleBtn) toggleBtn.addEventListener('click', bukaSidebar);
    if (closeBtn) closeBtn.addEventListener('click', tutupSidebar);
    if (overlay) overlay.addEventListener('click', tutupSidebar);
</script>

<!-- Modal Konfirmasi Modern (Tema Masjid) -->
<div id="modalKonfirmasi" class="fixed inset-0 z-[70] hidden flex items-center justify-center p-4">
    <div id="konfirmasiLatar" class="absolute inset-0 bg-cypress-950/50 veil-blur"></div>
    <div class="relative bg-white rounded-3xl max-w-sm w-full p-6 sm:p-7 text-center shadow-2xl border border-antique-300 scale-95 transition-transform origin-center">
        <div class="absolute inset-x-0 top-0 h-1.5 bg-gradient-to-r from-cypress-700 via-antique-500 to-cypress-700 rounded-t-3xl"></div>
        <div id="konfirmasiIkon" class="mx-auto w-16 h-16 rounded-2xl bg-red-50 border border-red-200 flex items-center justify-center mb-3">
            <i class="fa-solid fa-trash-can text-red-600 text-2xl"></i>
        </div>

        <h3 id="konfirmasiJudul" class="font-classic text-lg font-bold text-warm-900">Konfirmasi Hapus</h3>
        <p id="konfirmasiPesan" class="text-xs text-warm-800/70 mt-1.5 leading-relaxed">Tindakan ini tidak dapat dibatalkan.</p>

        <div class="flex items-center gap-2 pt-5">
            <button type="button" id="konfirmasiTidak" class="flex-1 px-4 py-2.5 rounded-xl bg-warm-100 hover:bg-warm-200 text-warm-800 font-bold text-xs transition">
                Batal
            </button>
            <button type="button" id="konfirmasiYa" class="flex-1 px-4 py-2.5 rounded-xl bg-red-600 hover:bg-red-700 text-white font-bold text-xs transition shadow-xs flex items-center justify-center gap-1.5">
                <i class="fa-solid fa-trash-can text-[10px]"></i>
                <span>Ya, Hapus</span>
            </button>
        </div>

        <p class="text-[10px] text-warm-800/40 font-semibold mt-4">
            <i class="fa-solid fa-shield-halved text-antique-600 mr-1"></i>
            Masjid Nurul Iman · Amanah Dalam Setiap Tindakan
        </p>
    </div>
</div>

<script>
// Konfirmasi Modern Pengganti confirm() Native — Tema Masjid
(function () {
    var modal = document.getElementById('modalKonfirmasi');
    if (!modal) return;
    var latar = document.getElementById('konfirmasiLatar');
    var judul = document.getElementById('konfirmasiJudul');
    var pesan = document.getElementById('konfirmasiPesan');
    var ikonBox = document.getElementById('konfirmasiIkon');
    var btnYa = document.getElementById('konfirmasiYa');
    var btnTidak = document.getElementById('konfirmasiTidak');

    var targetUrl = null;
    var targetForm = null;

    function tutup() {
        modal.classList.add('hidden');
        targetUrl = null;
        targetForm = null;
    }

    function buka(elemen) {
        var variant = elemen.getAttribute('data-variant') || 'danger';
        judul.textContent = elemen.getAttribute('data-judul') || (variant === 'success' ? 'Konfirmasi Verifikasi' : 'Konfirmasi Hapus');
        pesan.textContent = elemen.getAttribute('data-pesan') || (variant === 'success' ? 'Lanjutkan proses verifikasi ini?' : 'Apakah Anda yakin ingin menghapus data ini?');

        if (variant === 'success') {
            ikonBox.className = 'mx-auto w-16 h-16 rounded-2xl bg-emerald-50 border border-emerald-200 flex items-center justify-center mb-3';
            ikonBox.innerHTML = '<i class="fa-solid fa-circle-check text-emerald-600 text-2xl"></i>';
            btnYa.className = 'flex-1 px-4 py-2.5 rounded-xl bg-cypress-700 hover:bg-cypress-800 text-white font-bold text-xs transition shadow-xs flex items-center justify-center gap-1.5';
            btnYa.innerHTML = '<i class="fa-solid fa-check text-[10px]"></i><span>Ya, Verifikasi</span>';
        } else {
            ikonBox.className = 'mx-auto w-16 h-16 rounded-2xl bg-red-50 border border-red-200 flex items-center justify-center mb-3';
            ikonBox.innerHTML = '<i class="fa-solid fa-trash-can text-red-600 text-2xl"></i>';
            btnYa.className = 'flex-1 px-4 py-2.5 rounded-xl bg-red-600 hover:bg-red-700 text-white font-bold text-xs transition shadow-xs flex items-center justify-center gap-1.5';
            btnYa.innerHTML = '<i class="fa-solid fa-trash-can text-[10px]"></i><span>Ya, Hapus</span>';
        }

        modal.classList.remove('hidden');
    }

    document.addEventListener('click', function (e) {
        var link = e.target.closest('[data-hapus]');
        if (link && link.tagName === 'A') {
            e.preventDefault();
            targetUrl = link.getAttribute('href');
            buka(link);
        }
    });

    document.addEventListener('submit', function (e) {
        var form = e.target.closest('[data-hapus]');
        if (form && form.tagName === 'FORM') {
            e.preventDefault();
            targetForm = form;
            buka(form);
        }
    });

    btnYa.addEventListener('click', function () {
        if (targetUrl !== null) {
            window.location.href = targetUrl;
        } else if (targetForm !== null) {
            targetForm.submit();
        }
    });

    btnTidak.addEventListener('click', tutup);
    latar.addEventListener('click', tutup);
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') tutup();
    });
})();
</script>
</body>
</html>
