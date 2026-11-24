{{--
    Dialog seragam untuk seluruh aplikasi (admin, login manage, portal & auth pelamar).
    Menggantikan toast notifikasi, SweetAlert, modal error per halaman, alert() & confirm() bawaan.

    Tampilan netral: kartu putih, ikon dalam lingkaran berwarna lembut sesuai jenis:
      success (hijau) · error (merah) · warning (kuning) · info / confirm / loading (biru utama)

    API JavaScript (window.AppDialog):
      AppDialog.success(title, message)         -> Promise
      AppDialog.error(title, message|array)     -> Promise
      AppDialog.warning(title, message)         -> Promise
      AppDialog.info(title, message)            -> Promise
      AppDialog.confirm({ title, message, confirmText, cancelText, danger }) -> Promise<boolean>
      AppDialog.loading(text) / AppDialog.hideLoading()
    Alias lama: window.PageLoading.show/hide, window.alert(msg) -> dialog peringatan.

    HTML:
      <form data-confirm="Hapus data ini?" data-confirm-danger>  -> konfirmasi sebelum submit
      <a|button data-confirm="...">                              -> konfirmasi sebelum klik
      <form data-no-loading>                                     -> tanpa loading saat submit
      <form data-loading-text="Menyimpan...">                    -> teks loading khusus

    Pesan flash server (session success/error/warning/info & $errors) otomatis tampil.
--}}
@php
    $flash = null;
    if (isset($errors) && $errors->any()) {
        $flash = ['type' => 'error', 'title' => 'Periksa Kembali Data Anda', 'message' => $errors->all()];
    } elseif (session('error')) {
        $flash = ['type' => 'error', 'title' => 'Terjadi Kesalahan', 'message' => session('error')];
    } elseif (session('warning')) {
        $flash = ['type' => 'warning', 'title' => 'Perhatian', 'message' => session('warning')];
    } elseif (session('success')) {
        $flash = ['type' => 'success', 'title' => 'Berhasil', 'message' => session('success')];
    } elseif (session('info') || session('status')) {
        $flash = ['type' => 'info', 'title' => 'Informasi', 'message' => session('info') ?? session('status')];
    }
@endphp

<style>
    #appDialog .modal-dialog { max-width: 420px; }
    #appDialog .modal-content { border: 0; border-radius: 16px; box-shadow: 0 20px 45px rgba(16, 24, 40, .18); }
    #appDialog .app-dialog-icon {
        width: 60px; height: 60px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center;
        font-size: 30px; margin-bottom: 14px;
    }
    #appDialog .app-dialog-title { font-size: 17px; font-weight: 600; color: #101828; margin-bottom: 6px; }
    #appDialog .app-dialog-message { font-size: 14px; color: #667085; line-height: 1.55; word-break: break-word; }
    #appDialog .app-dialog-message ul { text-align: left; margin: 6px 0 0; padding-left: 18px; }
    #appDialog .app-dialog-actions { display: flex; gap: 8px; }
    #appDialog .app-dialog-actions .btn { flex: 1; padding-top: .55rem; padding-bottom: .55rem; font-weight: 500; }
    #appDialog[data-type="success"] .app-dialog-icon { background: #ECFDF3; color: #12B76A; }
    #appDialog[data-type="error"]   .app-dialog-icon { background: #FEF3F2; color: #F04438; }
    #appDialog[data-type="warning"] .app-dialog-icon { background: #FFFAEB; color: #F79009; }
    #appDialog[data-type="info"]    .app-dialog-icon,
    #appDialog[data-type="confirm"] .app-dialog-icon,
    #appDialog[data-type="loading"] .app-dialog-icon { background: #EEF2FF; color: #3E6CFF; }
    #appDialog[data-type="danger"]  .app-dialog-icon { background: #FEF3F2; color: #F04438; }
    #appDialog .spinner-border { width: 28px; height: 28px; border-width: 3px; }
</style>

<div class="modal fade" id="appDialog" tabindex="-1" aria-hidden="true" data-type="info">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center px-4 pt-4 pb-3">
                <div class="app-dialog-icon" id="appDialogIcon"></div>
                <div class="app-dialog-title" id="appDialogTitle"></div>
                <div class="app-dialog-message" id="appDialogMessage"></div>
            </div>
            <div class="px-4 pb-4 app-dialog-actions" id="appDialogActions"></div>
        </div>
    </div>
</div>

<script>
(function () {
    var el = document.getElementById('appDialog');
    if (!el || !window.bootstrap) return;

    var modal = new bootstrap.Modal(el, { backdrop: 'static', keyboard: false });
    var iconEl = document.getElementById('appDialogIcon');
    var titleEl = document.getElementById('appDialogTitle');
    var msgEl = document.getElementById('appDialogMessage');
    var actionsEl = document.getElementById('appDialogActions');

    var ICONS = {
        success: 'ti ti-circle-check', error: 'ti ti-alert-circle', warning: 'ti ti-alert-triangle',
        info: 'ti ti-info-circle', confirm: 'ti ti-help-circle', danger: 'ti ti-trash'
    };

    var current = null;      // { resolve, type }
    var autoTimer = null;
    // Status modal: hidden -> showing -> shown -> hiding -> hidden
    var phase = 'hidden', pendingHide = false, reopen = false;

    function esc(v) { var d = document.createElement('div'); d.textContent = v == null ? '' : String(v); return d.innerHTML; }

    function renderMessage(message) {
        if (Array.isArray(message)) {
            if (message.length === 1) return esc(message[0]);
            return '<ul>' + message.map(function (m) { return '<li>' + esc(m) + '</li>'; }).join('') + '</ul>';
        }
        return esc(message).replace(/\n/g, '<br>');
    }

    function finish(value) {
        clearTimeout(autoTimer);
        var c = current;
        current = null;
        if (c) c.resolve(value);
    }

    function closeOtherModals() {
        document.querySelectorAll('.modal.show').forEach(function (m) {
            if (m !== el) bootstrap.Modal.getOrCreateInstance(m).hide();
        });
    }

    function show() {
        if (phase === 'hidden') { closeOtherModals(); phase = 'showing'; modal.show(); }
        else if (phase === 'hiding') { reopen = true; }  // tampil lagi setelah selesai menutup
        pendingHide = false;
    }

    function hide() {
        reopen = false;
        if (phase === 'showing') { pendingHide = true; }
        else if (phase === 'shown') { phase = 'hiding'; modal.hide(); }
    }

    el.addEventListener('shown.bs.modal', function () {
        phase = 'shown';
        if (pendingHide) { pendingHide = false; phase = 'hiding'; modal.hide(); }
    });
    el.addEventListener('hidden.bs.modal', function () {
        phase = 'hidden';
        if (reopen) { reopen = false; phase = 'showing'; modal.show(); return; }
        finish(false);
        if (document.querySelector('.modal.show')) document.body.classList.add('modal-open');
    });

    /**
     * @param {Object} o { type, title, message, buttons: [{text, value, cls}], autoClose(ms) }
     */
    function open(o) {
        finish(false); // dialog sebelumnya dianggap ditutup

        return new Promise(function (resolve) {
            current = { resolve: resolve, type: o.type };
            el.setAttribute('data-type', o.type);

            iconEl.innerHTML = o.type === 'loading'
                ? '<div class="spinner-border" role="status"></div>'
                : '<i class="' + (ICONS[o.type] || ICONS.info) + '"></i>';
            titleEl.textContent = o.title || '';
            msgEl.innerHTML = o.message ? renderMessage(o.message) : '';
            msgEl.style.display = o.message ? '' : 'none';

            actionsEl.innerHTML = '';
            actionsEl.style.display = (o.buttons || []).length ? '' : 'none';
            (o.buttons || []).forEach(function (b) {
                var btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'btn ' + (b.cls || 'btn-primary');
                btn.textContent = b.text;
                btn.addEventListener('click', function () {
                    var c = current;
                    current = null;
                    clearTimeout(autoTimer);
                    hide();
                    if (c) c.resolve(b.value);
                });
                actionsEl.appendChild(btn);
            });

            if (o.autoClose) autoTimer = setTimeout(hide, o.autoClose);
            show();

            var primary = actionsEl.querySelector('.btn:last-child');
            if (primary) setTimeout(function () { primary.focus(); }, 300);
        });
    }

    function notice(type, title, message, autoClose) {
        return open({ type: type, title: title, message: message, autoClose: autoClose,
            buttons: [{ text: 'OK', value: true, cls: 'btn-primary' }] });
    }

    var AppDialog = {
        success: function (title, message) { return notice('success', title || 'Berhasil', message, 2800); },
        error: function (title, message) { return notice('error', title || 'Terjadi Kesalahan', message); },
        warning: function (title, message) { return notice('warning', title || 'Perhatian', message); },
        info: function (title, message) { return notice('info', title || 'Informasi', message); },
        confirm: function (opts) {
            opts = typeof opts === 'string' ? { message: opts } : (opts || {});
            return open({
                type: opts.danger ? 'danger' : 'confirm',
                title: opts.title || 'Konfirmasi',
                message: opts.message,
                buttons: [
                    { text: opts.cancelText || 'Batal', value: false, cls: 'btn-light' },
                    { text: opts.confirmText || 'Ya, Lanjutkan', value: true, cls: opts.danger ? 'btn-danger' : 'btn-primary' }
                ]
            });
        },
        loading: function (text) {
            open({ type: 'loading', title: text || 'Sedang memproses...', message: 'Mohon tunggu, jangan tutup atau muat ulang halaman.' });
        },
        hideLoading: function () {
            if (el.getAttribute('data-type') !== 'loading') return;
            finish(false);
            hide();
        }
    };
    window.AppDialog = AppDialog;

    // Alias lama
    window.PageLoading = { show: AppDialog.loading, hide: AppDialog.hideLoading };
    window.alert = function (message) { AppDialog.warning('Perhatian', message); };

    // ---------- Konfirmasi lewat atribut data-confirm ----------
    function confirmOptions(node) {
        return {
            title: node.getAttribute('data-confirm-title') || 'Konfirmasi',
            message: node.getAttribute('data-confirm'),
            confirmText: node.getAttribute('data-confirm-text') || (node.hasAttribute('data-confirm-danger') ? 'Ya, Hapus' : 'Ya, Lanjutkan'),
            danger: node.hasAttribute('data-confirm-danger')
        };
    }

    document.addEventListener('click', function (e) {
        var node = e.target.closest && e.target.closest('a[data-confirm], button[data-confirm]');
        if (!node || node._confirmed) { if (node) node._confirmed = false; return; }
        e.preventDefault();
        e.stopImmediatePropagation();
        AppDialog.confirm(confirmOptions(node)).then(function (ok) {
            if (!ok) return;
            node._confirmed = true;
            node.click();
        });
    }, true);

    document.addEventListener('submit', function (e) {
        var form = e.target;
        if (!form.hasAttribute('data-confirm')) return;
        if (form._confirmed) { form._confirmed = false; return; }
        e.preventDefault();
        e.stopImmediatePropagation();
        AppDialog.confirm(confirmOptions(form)).then(function (ok) {
            if (!ok) return;
            form._confirmed = true;
            form.requestSubmit ? form.requestSubmit() : form.submit();
        });
    }, true);

    // ---------- Loading saat form dikirim ----------
    // Dicek setelah semua handler halaman jalan (setTimeout 0): submit yang dibatalkan
    // (validasi JS, modal konfirmasi) tidak memunculkan loading.
    function readDownloadCookie() {
        var m = document.cookie.match(/(?:^|;\s*)download_token=([^;]*)/);
        return m ? decodeURIComponent(m[1]) : null;
    }
    function clearDownloadCookie() { document.cookie = 'download_token=; Max-Age=0; path=/'; }

    document.addEventListener('submit', function (e) {
        var form = e.target;
        if (form.hasAttribute('data-no-loading')) return;

        // Token download: server membalas cookie ini bila responsnya file (MarkDownloadComplete),
        // sehingga loading ditutup walau halaman tidak berpindah.
        var token = Date.now().toString(36) + Math.random().toString(36).slice(2, 10);
        var old = form.querySelector('input[name="download_token"]');
        if (old) old.remove();
        var input = document.createElement('input');
        input.type = 'hidden'; input.name = 'download_token'; input.value = token;
        form.appendChild(input);
        clearDownloadCookie();

        setTimeout(function () {
            if (e.defaultPrevented) return;
            AppDialog.loading(form.getAttribute('data-loading-text'));
            setTimeout(function () {
                form.querySelectorAll('button[type="submit"], input[type="submit"]').forEach(function (b) { b.disabled = true; });
            }, 0);

            var poll = setInterval(function () {
                if (readDownloadCookie() === token) {
                    clearInterval(poll);
                    clearDownloadCookie();
                    AppDialog.hideLoading();
                    form.querySelectorAll('button[type="submit"][disabled], input[type="submit"][disabled]').forEach(function (b) { b.disabled = false; });
                }
            }, 500);
        }, 0);
    });

    // Kembali lewat tombol Back (bfcache): tutup loading & aktifkan tombol
    window.addEventListener('pageshow', function (e) {
        if (!e.persisted) return;
        AppDialog.hideLoading();
        document.querySelectorAll('button[type="submit"][disabled], input[type="submit"][disabled]').forEach(function (b) { b.disabled = false; });
    });

    // ---------- Pesan flash dari server ----------
    @if ($flash)
        document.addEventListener('DOMContentLoaded', function () {
            AppDialog[@json($flash['type'])](@json($flash['title']), {!! json_encode($flash['message'], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!});
        });
    @endif
})();
</script>
