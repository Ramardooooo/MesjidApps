/*!
 * assets/js/app.js
 * Mesjid Nurul Iman — UI behaviours terpusat (tanpa inline onclick)
 * Menggantikan confirm() browser ("localhost says") dengan modal bertema masjid.
 */
(function () {
    'use strict';

    var CONFIRM_ID = 'mjnConfirm';

    /* =========================================================
     * Helper kecil
     * ========================================================= */
    function closest(el, sel) {
        return el.closest ? el.closest(sel) : null;
    }

    function injectStyles() {
        var css = document.createElement('style');
        css.id = 'mjnStyles';
        css.textContent =
            '@keyframes mjn-fade{from{opacity:0}to{opacity:1}}' +
            '@keyframes mjn-pop{from{opacity:0;transform:scale(.92) translateY(10px)}to{opacity:1;transform:scale(1) translateY(0)}}' +
            '.mjn-overlay{animation:mjn-fade .18s ease}' +
            '.mjn-card{animation:mjn-pop .22s cubic-bezier(.22,1,.36,1)}' +
            '.mjn-toast{position:fixed;left:50%;bottom:24px;transform:translateX(-50%);z-index:9999;' +
            'background:#0d1d15;color:#f8f4ec;border:1px solid rgba(197,160,89,.5);' +
            'padding:10px 18px;border-radius:999px;font-size:13px;font-weight:700;' +
            'box-shadow:0 10px 30px rgba(0,0,0,.35);animation:mjn-pop .2s ease;display:flex;align-items:center;gap:8px}' +
            '.mjn-copied{outline:2px solid rgba(5,150,105,.75)!important;outline-offset:2px!important;border-radius:10px!important}';
        document.head.appendChild(css);
    }

    /* =========================================================
     * Toast
     * ========================================================= */
    var toastTimer = null;
    function showToast(msg) {
        var old = document.querySelector('.mjn-toast');
        if (old) old.remove();
        var t = document.createElement('div');
        t.className = 'mjn-toast';
        t.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#c5a059" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>' + msg;
        document.body.appendChild(t);
        clearTimeout(toastTimer);
        toastTimer = setTimeout(function () { t.remove(); }, 2200);
    }

    /* =========================================================
     * MODAL KONFIRMASI — pengganti confirm() browser
     * ========================================================= */
    var okAction = null;

    function buildConfirm() {
        if (document.getElementById(CONFIRM_ID)) return;
        var el = document.createElement('div');
        el.id = CONFIRM_ID;
        el.className = 'mjn-overlay fixed inset-0 z-[999] hidden items-center justify-center p-4';
        el.style.background = 'rgba(6,16,11,.72)';
        el.innerHTML =
            '<div class="mjn-card" style="max-width:400px;width:100%;background:#fff;border-radius:22px;' +
            'border:1px solid #e3d5b4;box-shadow:0 24px 70px rgba(0,0,0,.4);overflow:hidden;' +
            'font-family:Segoe UI,Helvetica,Arial,sans-serif">' +
            '<div style="background:linear-gradient(135deg,#07100b,#1b3a2b);padding:26px 22px 20px;text-align:center;position:relative;overflow:hidden">' +
            '<div style="margin:0 auto 12px;width:58px;height:58px;border-radius:50%;border:2px solid rgba(197,160,89,.6);' +
            'background:rgba(197,160,89,.12);display:flex;align-items:center;justify-content:center" id="mjnConfirmIconWrap">' +
            '<svg id="mjnConfirmIcon" width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#dfc896" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">' +
            '<path d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>' +
            '</div>' +
            '<div id="mjnConfirmTitle" style="font-size:17px;font-weight:800;color:#fff;letter-spacing:.2px"></div>' +
            '</div>' +
            '<div style="padding:22px 24px 8px;text-align:center">' +
            '<p id="mjnConfirmText" style="margin:0;font-size:13px;line-height:1.7;color:#4b5a51"></p>' +
            '</div>' +
            '<div style="padding:16px 24px 24px;display:flex;align-items:center;justify-content:center;gap:10px">' +
            '<button type="button" id="mjnConfirmCancel" style="padding:11px 20px;border-radius:12px;background:#f5f1e8;color:#4b5563;font-weight:700;font-size:12px;cursor:pointer;border:1px solid #ebe4d3">Batal</button>' +
            '<button type="button" id="mjnConfirmOk" style="padding:11px 24px;border-radius:12px;background:#b91c1c;color:#fff;font-weight:800;font-size:12px;cursor:pointer;border:none;box-shadow:0 8px 20px rgba(185,28,28,.3)">Ya, Lanjutkan</button>' +
            '</div></div>';

        el.addEventListener('click', function (e) {
            if (e.target === el) hideConfirm();
        });
        document.getElementById('mjnConfirmCancel').addEventListener('click', hideConfirm);
        document.getElementById('mjnConfirmOk').addEventListener('click', function () {
            var fn = okAction;
            hideConfirm();
            if (typeof fn === 'function') fn();
        });
        document.body.appendChild(el);
    }

    function hideConfirm() {
        var el = document.getElementById(CONFIRM_ID);
        if (el) {
            el.classList.add('hidden');
            el.classList.remove('flex');
        }
        okAction = null;
    }

    function showConfirm(opts) {
        buildConfirm();
        opts = opts || {};
        document.getElementById('mjnConfirmTitle').textContent = opts.title || 'Konfirmasi';
        document.getElementById('mjnConfirmText').textContent = opts.text || 'Anda yakin ingin melanjutkan aksi ini?';
        document.getElementById('mjnConfirmOk').textContent = opts.ok || 'Ya, Lanjutkan';

        var ok = document.getElementById('mjnConfirmOk');
        if (opts.style === 'success') {
            ok.style.background = 'linear-gradient(135deg,#065f46,#047857)';
            ok.style.boxShadow = '0 8px 20px rgba(4,120,87,.3)';
        } else {
            ok.style.background = 'linear-gradient(135deg,#991b1b,#b91c1c)';
            ok.style.boxShadow = '0 8px 20px rgba(185,28,28,.3)';
        }

        okAction = opts.onOk || null;
        var el = document.getElementById(CONFIRM_ID);
        el.classList.remove('hidden');
        el.classList.add('flex');
    }

    /* =========================================================
     * Clipboard — data-copy="id" data-copy-text="nomor"
     * ========================================================= */
    function doCopy(btn, text) {
        navigator.clipboard.writeText(text).then(function () {
            showToast('Nomor rekening tersalin');
            var label = btn.querySelector('[data-copy-state]');
            if (label) {
                label.dataset.mjnOrig = label.innerHTML;
                label.innerHTML = '<i class="fa-solid fa-check"></i> Tercopy!';
                btn.classList.add('mjn-copied');
                setTimeout(function () {
                    if (label.dataset.mjnOrig) label.innerHTML = label.dataset.mjnOrig;
                    btn.classList.remove('mjn-copied');
                }, 2000);
            } else {
                btn.classList.add('mjn-copied');
                setTimeout(function () { btn.classList.remove('mjn-copied'); }, 2000);
            }
        }).catch(function () {
            showToast('Gagal menyalin. Salin manual: ' + text);
        });
    }

    /* =========================================================
     * Delegation: CLICK
     * ========================================================= */
    document.addEventListener('click', function (e) {
        var t = e.target;

        /* --- data-confirm : link/button konfirmasi custom --- */
        var confirmLink = closest(t, '[data-confirm]');
        if (confirmLink) {
            e.preventDefault();
            showConfirm({
                title: confirmLink.getAttribute('data-confirm-title') || 'Konfirmasi Aksi',
                text: confirmLink.getAttribute('data-confirm'),
                ok: confirmLink.getAttribute('data-confirm-ok') || 'Ya, Lanjutkan',
                style: confirmLink.getAttribute('data-confirm-style') || 'danger',
                onOk: function () {
                    var href = confirmLink.getAttribute('href');
                    if (href) window.location.href = href;
                }
            });
            return;
        }

        /* --- data-modal-open : buka modal --- */
        var open = closest(t, '[data-modal-open]');
        if (open) {
            var mo = document.getElementById(open.getAttribute('data-modal-open'));
            if (mo) {
                mo.classList.remove('hidden');
                mo.classList.add('flex');
                document.body.classList.add('overflow-hidden');
            }
            return;
        }

        /* --- data-modal-close : tutup modal --- */
        var close = closest(t, '[data-modal-close]');
        if (close) {
            var mcId = close.getAttribute('data-modal-close') || '';
            var target = mcId ? document.getElementById(mcId) : closest(close, '[data-modal]');
            if (target) {
                target.classList.add('hidden');
                target.classList.remove('flex');
                document.body.classList.remove('overflow-hidden');
            }
            reenableModalCleanup();
            return;
        }

        /* --- data-copy : salin teks --- */
        var copyBtn = closest(t, '[data-copy]');
        if (copyBtn) {
            e.preventDefault();
            doCopy(copyBtn, copyBtn.getAttribute('data-copy-text') || copyBtn.getAttribute('data-copy'));
            return;
        }

        /* --- data-nominal : preset nominal donasi --- */
        var nomBtn = closest(t, '[data-nominal]');
        if (nomBtn) {
            e.preventDefault();
            var input = document.getElementById(nomBtn.getAttribute('data-nominal-target') || 'nominalInput');
            if (input) input.value = nomBtn.getAttribute('data-nominal');
            document.querySelectorAll('[data-nominal]').forEach(function (b) { b.classList.remove('active'); });
            nomBtn.classList.add('active');
            return;
        }

        /* --- data-action : print / back --- */
        var act = closest(t, '[data-action]');
        if (act) {
            var a = act.getAttribute('data-action');
            if (a === 'print') window.print();
            else if (a === 'back') window.history.back();
            return;
        }

        /* --- data-login-user : panel akses cepat login --- */
        var loginBtn = closest(t, '[data-login-user]');
        if (loginBtn) {
            var u = document.getElementById('loginUsername');
            var p = document.getElementById('password');
            if (u) u.value = loginBtn.getAttribute('data-login-user');
            if (p) p.value = loginBtn.getAttribute('data-login-pass') || '';
            return;
        }
    });

    /* =========================================================
     * Delegation: SUBMIT — data-confirm-form
     * ========================================================= */
    document.addEventListener('submit', function (e) {
        var form = closest(e.target, '[data-confirm-form]');
        if (!form) return;
        if (form.getAttribute('data-confirm-approved') === '1') {
            form.setAttribute('data-confirm-approved', '0');
            return;
        }
        e.preventDefault();
        showConfirm({
            title: form.getAttribute('data-confirm-title') || 'Konfirmasi',
            text: form.getAttribute('data-confirm-form'),
            ok: form.getAttribute('data-confirm-ok') || 'Ya, Lanjutkan',
            style: form.getAttribute('data-confirm-style') || 'success',
            onOk: function () {
                form.setAttribute('data-confirm-approved', '1');
                form.submit();
            }
        });
    });

    /* =========================================================
     * Delegation: CHANGE — radio/checkbox toggle
     * ========================================================= */
    document.addEventListener('change', function (e) {
        var r = e.target;
        if (!r.matches) return;

        if (r.matches('[data-toggle-metode]') && r.checked) {
            if (typeof window.toggleMetode === 'function') {
                window.toggleMetode(r.getAttribute('data-toggle-metode'));
            }
        }
        if (r.matches('[data-toggle-anonim]')) {
            if (typeof window.toggleAnonim === 'function') {
                window.toggleAnonim(r.checked);
            }
        }
    });

    /* =========================================================
     * UMUM: hapus highlight preset bila nominal diketik manual
     * ========================================================= */
    document.addEventListener('input', function (e) {
        var t = e.target;
        if (t.id === 'nominalInput') {
            document.querySelectorAll('[data-nominal].active').forEach(function (b) {
                b.classList.remove('active');
            });
        }
    });

    function reenableModalCleanup() {
        /* placeholder untuk membership sesudah tutup modal tertentu */
    }

    /* =========================================================
     * Keyboard: ESC menutup modal konfirmasi
     * ========================================================= */
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') hideConfirm();
    });

    injectStyles();
})();