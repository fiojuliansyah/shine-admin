{{--
    Modal loading untuk setiap submit form (login, daftar, lupa password, onboarding, dll).
    Form yang tidak ingin memakai loading: tambahkan atribut data-no-loading.
    Teks bisa diganti per form lewat atribut data-loading-text="...".
--}}
<div class="modal fade" id="pageLoadingModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow">
            <div class="modal-body text-center py-4">
                <div class="spinner-border text-primary mb-3" role="status" style="width: 2.75rem; height: 2.75rem;">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <h6 class="mb-1" id="pageLoadingTitle">Sedang memproses...</h6>
                <p class="text-muted small mb-0">Mohon tunggu, jangan tutup atau muat ulang halaman.</p>
            </div>
        </div>
    </div>
</div>

<script>
    (function () {
        var modalEl = document.getElementById('pageLoadingModal');
        if (!modalEl || !window.bootstrap) return;
        var modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        var titleEl = document.getElementById('pageLoadingTitle');

        var opening = false, hideRequested = false;
        modalEl.addEventListener('shown.bs.modal', function () {
            opening = false;
            // hide() dipanggil saat animasi buka belum selesai -> tutup sekarang
            if (hideRequested) { hideRequested = false; modal.hide(); }
        });

        window.PageLoading = {
            show: function (text) {
                titleEl.textContent = text || 'Sedang memproses...';
                hideRequested = false;
                // Tutup modal lain (mis. alert) agar tidak bertumpuk
                document.querySelectorAll('.modal.show').forEach(function (el) {
                    if (el !== modalEl) bootstrap.Modal.getOrCreateInstance(el).hide();
                });
                if (!modalEl.classList.contains('show')) opening = true;
                modal.show();
            },
            hide: function () {
                if (opening) { hideRequested = true; return; }
                modal.hide();
            }
        };

        // Bubbling di document: berjalan setelah handler form milik halaman,
        // sehingga submit yang dibatalkan (validasi JS) tidak memunculkan loading.
        document.addEventListener('submit', function (e) {
            var form = e.target;
            if (e.defaultPrevented || form.hasAttribute('data-no-loading')) return;

            window.PageLoading.show(form.getAttribute('data-loading-text'));

            // Cegah submit ganda (dilakukan setelah submit agar value tombol tetap terkirim)
            setTimeout(function () {
                form.querySelectorAll('button[type="submit"], input[type="submit"]').forEach(function (btn) {
                    btn.disabled = true;
                });
            }, 0);
        });

        // Kembali lewat tombol Back (bfcache): tutup loading & aktifkan tombol lagi
        window.addEventListener('pageshow', function (e) {
            if (!e.persisted) return;
            modal.hide();
            document.querySelectorAll('button[type="submit"][disabled], input[type="submit"][disabled]').forEach(function (btn) {
                btn.disabled = false;
            });
        });
    })();
</script>
