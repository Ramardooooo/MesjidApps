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
</body>
</html>
