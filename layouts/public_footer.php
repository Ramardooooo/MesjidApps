<?php
// layouts/public_footer.php - Footer Publik & WhatsApp Floating Button
$profil = get_profil_masjid();
$base = base_url();
$rekeningFooter = $pdo->query("SELECT * FROM rekening_donasi WHERE is_active = 1 ORDER BY urutan ASC LIMIT 3")->fetchAll();
?>
    </main>

    <!-- Footer Publik Islami Elegan -->
    <footer class="bg-cypress-950 text-white border-t-2 border-antique-500/40 relative overflow-hidden mt-16 pattern-arabesque-dark">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-16 pb-12 relative z-10">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-10">
                
                <!-- Kolom 1: Profil & Identitas -->
                <div class="space-y-4">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-2xl bg-cypress-900 border border-antique-500/50 flex items-center justify-center text-antique-400 shadow-inner">
                            <i class="fa-solid fa-mosque text-xl"></i>
                        </div>
                        <div>
                            <span class="font-classic text-base font-bold tracking-wider block text-white leading-tight">
                                <?= strtoupper(e($profil['nama_masjid'])) ?>
                            </span>
                            <span class="text-[10px] text-antique-300 uppercase tracking-widest block font-medium">
                                <?= e($profil['sebutan']) ?>
                            </span>
                        </div>
                    </div>
                    
                    <p class="text-xs text-stone-300/80 leading-relaxed">
                        <?= e($profil['slogan']) ?>. Pusat kegiatan ibadah, pembinaan keilmuan Islam, dan penyaluran amanah sosial kemasyarakatan yang transparan dan akuntabel.
                    </p>

                    <!-- Sosmed -->
                    <div class="flex items-center gap-2.5 pt-2">
                        <?php if (!empty($profil['instagram'])): ?>
                            <a href="https://instagram.com/<?= e($profil['instagram']) ?>" target="_blank" class="w-9 h-9 rounded-xl bg-white/5 hover:bg-pink-500 hover:text-white border border-white/10 flex items-center justify-center text-stone-300 transition-luxury" title="Instagram">
                                <i class="fa-brands fa-instagram text-sm"></i>
                            </a>
                        <?php endif; ?>
                        <?php if (!empty($profil['youtube'])): ?>
                            <a href="https://youtube.com/@<?= e($profil['youtube']) ?>" target="_blank" class="w-9 h-9 rounded-xl bg-white/5 hover:bg-red-600 hover:text-white border border-white/10 flex items-center justify-center text-stone-300 transition-luxury" title="YouTube">
                                <i class="fa-brands fa-youtube text-sm"></i>
                            </a>
                        <?php endif; ?>
                        <?php if (!empty($profil['facebook'])): ?>
                            <a href="https://facebook.com/<?= e($profil['facebook']) ?>" target="_blank" class="w-9 h-9 rounded-xl bg-white/5 hover:bg-blue-600 hover:text-white border border-white/10 flex items-center justify-center text-stone-300 transition-luxury" title="Facebook">
                                <i class="fa-brands fa-facebook-f text-sm"></i>
                            </a>
                        <?php endif; ?>
                        <?php if (!empty($profil['tiktok'] ?? null)): ?>
                            <a href="https://tiktok.com/@<?= e($profil['tiktok']) ?>" target="_blank" class="w-9 h-9 rounded-xl bg-white/5 hover:bg-slate-800 hover:text-white border border-white/10 flex items-center justify-center text-stone-300 transition-luxury" title="TikTok">
                                <i class="fa-brands fa-tiktok text-sm"></i>
                            </a>
                        <?php endif; ?>
                        <?php if (!empty($profil['twitter'] ?? null)): ?>
                            <a href="https://twitter.com/<?= e($profil['twitter']) ?>" target="_blank" class="w-9 h-9 rounded-xl bg-white/5 hover:bg-blue-400 hover:text-white border border-white/10 flex items-center justify-center text-stone-300 transition-luxury" title="X (Twitter)">
                                <i class="fa-brands fa-x-twitter text-sm"></i>
                            </a>
                        <?php endif; ?>
                        <?php if (!empty($profil['telegram'] ?? null)): ?>
                            <a href="https://t.me/<?= e($profil['telegram']) ?>" target="_blank" class="w-9 h-9 rounded-xl bg-white/5 hover:bg-blue-500 hover:text-white border border-white/10 flex items-center justify-center text-stone-300 transition-luxury" title="Telegram">
                                <i class="fa-brands fa-telegram text-sm"></i>
                            </a>
                        <?php endif; ?>
                        <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $profil['whatsapp']) ?>" target="_blank" class="w-9 h-9 rounded-xl bg-emerald-950 hover:bg-emerald-600 hover:text-white border border-emerald-500/30 flex items-center justify-center text-emerald-400 transition-luxury" title="WhatsApp">
                            <i class="fa-brands fa-whatsapp text-sm"></i>
                        </a>
                    </div>
                </div>

                <!-- Kolom 2: Navigasi Tautan Cepat -->
                <div>
                    <h4 class="font-classic text-sm font-bold tracking-wider text-antique-300 uppercase mb-4 pb-2 border-b border-white/10">
                        Tautan Utama
                    </h4>
                    <ul class="space-y-2.5 text-xs text-stone-300">
                        <li>
                            <a href="<?= $base ?>/index.php" class="hover:text-antique-300 transition flex items-center gap-2">
                                <i class="fa-solid fa-chevron-right text-[10px] text-antique-500"></i> Beranda Masjid
                            </a>
                        </li>
                        <li>
                            <a href="<?= $base ?>/home/profil.php" class="hover:text-antique-300 transition flex items-center gap-2">
                                <i class="fa-solid fa-chevron-right text-[10px] text-antique-500"></i> Profil &amp; Struktur DKM
                            </a>
                        </li>
                        <li>
                            <a href="<?= $base ?>/home/program.php" class="hover:text-antique-300 transition flex items-center gap-2">
                                <i class="fa-solid fa-chevron-right text-[10px] text-antique-500"></i> Katalog Program &amp; Wakaf
                            </a>
                        </li>
                        <li>
                            <a href="<?= $base ?>/home/transparansi.php" class="hover:text-antique-300 transition flex items-center gap-2">
                                <i class="fa-solid fa-chevron-right text-[10px] text-antique-500"></i> Transparansi Kas Terbuka
                            </a>
                        </li>
                        <li>
                            <a href="<?= $base ?>/home/berita.php" class="hover:text-antique-300 transition flex items-center gap-2">
                                <i class="fa-solid fa-chevron-right text-[10px] text-antique-500"></i> Kabar &amp; Warta Kegiatan
                            </a>
                        </li>
                        <li>
                            <a href="<?= $base ?>/home/kajian.php" class="hover:text-antique-300 transition flex items-center gap-2">
                                <i class="fa-solid fa-chevron-right text-[10px] text-antique-500"></i> Video Kajian &amp; Live Streaming
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Kolom 3: Rekening Resmi Masjid -->
                <div>
                    <h4 class="font-classic text-sm font-bold tracking-wider text-antique-300 uppercase mb-4 pb-2 border-b border-white/10">
                        Rekening Infaq Resmi
                    </h4>
                    <div class="space-y-3">
                        <?php foreach ($rekeningFooter as $rek): ?>
                            <div class="p-2.5 rounded-xl bg-white/5 border border-white/10 text-xs">
                                <div class="flex items-center justify-between">
                                    <span class="font-bold text-white"><?= e($rek['nama_bank']) ?></span>
                                    <span class="text-[10px] text-antique-400 bg-antique-500/10 px-1.5 py-0.5 rounded"><?= e($rek['kategori_donasi']) ?></span>
                                </div>
                                <div class="flex items-center justify-between mt-1">
                                    <span class="font-mono text-antique-200 tracking-wider font-semibold text-xs"><?= e($rek['nomor_rekening']) ?></span>
                                    <button type="button" id="copyBtnFooter-<?= htmlspecialchars(md5($rek['id'])) ?>" onclick="copyToClipboardBtn('<?= e($rek['nomor_rekening']) ?>', '<?= htmlspecialchars(md5($rek['id'])) ?>', 'footer');" class="text-[10px] text-antique-400 hover:text-antique-200 px-1.5 py-0.5 rounded bg-cypress-900 border border-antique-500/30 transition-luxury">
                                        <i class="fa-regular fa-copy"></i> Salin
                                    </button>
                                </div>
                                <span class="text-[10px] text-stone-400 block mt-0.5">a.n <?= e($rek['atas_nama']) ?></span>
                            </div>
                        <?php endforeach; ?>
                        <a href="<?= $base ?>/home/donasi-online.php" class="inline-flex items-center gap-1.5 text-xs text-antique-400 hover:text-white font-semibold mt-1">
                            <span>Lihat metode QRIS &amp; transfer lengkap</span>
                            <i class="fa-solid fa-arrow-right text-[10px]"></i>
                        </a>
                    </div>
                </div>

                <!-- Kolom 4: Kontak & Sekretariat -->
                <div class="space-y-3">
                    <h4 class="font-classic text-sm font-bold tracking-wider text-antique-300 uppercase mb-4 pb-2 border-b border-white/10">
                        Sekretariat DKM
                    </h4>
                    <p class="text-xs text-stone-300/80 flex items-start gap-2.5 leading-relaxed">
                        <i class="fa-solid fa-location-dot text-antique-500 mt-1 shrink-0"></i>
                        <span><?= e($profil['alamat']) ?></span>
                    </p>
                    <p class="text-xs text-stone-300/80 flex items-center gap-2.5">
                        <i class="fa-solid fa-phone text-antique-500 shrink-0"></i>
                        <span><?= e($profil['whatsapp']) ?></span>
                    </p>
                    <p class="text-xs text-stone-300/80 flex items-center gap-2.5">
                        <i class="fa-solid fa-envelope text-antique-500 shrink-0"></i>
                        <span><?= e($profil['email']) ?></span>
                    </p>

                    <div class="pt-2">
                        <a href="<?= $base ?>/home/kontak.php" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-cypress-900 hover:bg-cypress-800 border border-antique-500/40 text-antique-300 text-xs font-semibold transition">
                            <i class="fa-solid fa-paper-plane"></i>
                            <span>Hubungi Pengurus</span>
                        </a>
                    </div>
                </div>

            </div>

            <!-- Bottom Bar Copyright -->
            <div class="mt-12 pt-6 border-t border-white/10 flex flex-col sm:flex-row items-center justify-between text-xs text-stone-400 gap-4">
                <p>&copy; <?= date('Y') ?> <?= e($profil['nama_masjid']) ?>. Seluruh hak cipta dilindungi.</p>
                <div class="flex items-center gap-4 text-xs">
                    <a href="<?= $base ?>/home/transparansi.php" class="hover:text-antique-300">Laporan Publik</a>
                    <span>•</span>
                    <a href="<?= $base ?>/auth/login.php" class="hover:text-antique-300">Portal Pengurus &amp; Donatur</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Floating WhatsApp Button -->
    <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $profil['whatsapp']) ?>?text=Assalamu'alaikum%20Pengurus%20<?= urlencode($profil['nama_masjid']) ?>..." 
       target="_blank" 
       rel="noopener"
       aria-label="Chat WhatsApp Pengurus"
       class="fixed bottom-6 right-6 z-50 flex items-center gap-2.5 px-4 py-3 rounded-full bg-gradient-to-r from-emerald-600 to-emerald-700 text-white shadow-xl shadow-emerald-950/40 hover:scale-105 active:scale-95 border-2 border-white/20 transition-all group">
        <i class="fa-brands fa-whatsapp text-2xl group-hover:rotate-12 transition-transform"></i>
        <span class="text-xs font-bold hidden sm:inline-block">Layanan Jamaah</span>
    </a>

    <!-- Copy Button State Change Function - Generic -->
    <script>
    function copyToClipboardBtn(text, btnId, prefix = 'footer') {
        navigator.clipboard.writeText(text).then(() => {
           const btn = document.getElementById(`copyBtn${prefix.charAt(0).toUpperCase() + prefix.slice(1)}-${btnId}`);
           if (btn) {
               // Ganti text & icon
               btn.innerHTML = '<i class="fa-solid fa-check mr-1"></i> Tercopy!';
                
               // Adjust color based on context
               if (prefix === 'footer') {
                   btn.classList.add('bg-emerald-600', 'text-white');
                   btn.classList.remove('bg-cypress-900', 'text-antique-400');
               } else if (prefix === 'index') {
                   btn.classList.add('bg-emerald-600', 'hover:bg-emerald-700', 'text-white');
                   btn.classList.remove('bg-antique-500/10', 'hover:bg-antique-500', 'text-antique-300', 'hover:text-cypress-950');
               } else if (prefix === 'kontak') {
                   btn.classList.add('bg-emerald-600', 'text-white');
                   btn.classList.remove('bg-cypress-950', 'text-antique-300');
               }
                
               // Kembali ke semula setelah 2 detik
               setTimeout(() => {
                   btn.innerHTML = '<i class="fa-regular fa-copy mr-1"></i> Salin';
                    
                   if (prefix === 'footer') {
                       btn.classList.remove('bg-emerald-600', 'text-white');
                       btn.classList.add('bg-cypress-900', 'text-antique-400');
                   } else if (prefix === 'index') {
                       btn.classList.remove('bg-emerald-600', 'hover:bg-emerald-700', 'text-white');
                       btn.classList.add('bg-antique-500/10', 'hover:bg-antique-500', 'text-antique-300', 'hover:text-cypress-950');
                   } else if (prefix === 'kontak') {
                       btn.classList.remove('bg-emerald-600', 'text-white');
                       btn.classList.add('bg-cypress-950', 'text-antique-300');
                   }
               }, 2000);
           }
        }).catch(() => {
           alert('Gagal menyalin nomor rekening');
        });
    }
    </script>

</body>
</html>
