{{--
    Modal pratinjau dokumen (gambar / PDF) — dokumen tidak dibuka di tab baru / link storage.

    Pemakaian: <a href="{{ $document->previewUrl() }}" data-doc-preview
                  data-kind="{{ $document->file_kind }}" data-title="{{ $document->name }}">Lihat</a>
    href = route file lewat aplikasi (lihat DocumentFileController). Tombol Unduh memakai ?download=1.
--}}
<div class="modal fade" id="docPreviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl modal-fullscreen-md-down">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h5 class="modal-title text-truncate" id="docPreviewTitle">Dokumen</h5>
                <div class="d-flex align-items-center gap-2 ms-auto">
                    <a href="#" class="btn btn-sm btn-outline-primary" id="docPreviewDownload">
                        <i class="ti ti-download me-1"></i>Unduh
                    </a>
                    <button type="button" class="btn-close ms-1" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
            </div>
            <div class="modal-body p-0 bg-light d-flex align-items-center justify-content-center position-relative" style="min-height: 60vh;">
                <div id="docPreviewLoading" class="position-absolute top-50 start-50 translate-middle text-center">
                    <div class="spinner-border text-primary"></div>
                </div>
                <img id="docPreviewImage" alt="" class="img-fluid d-none" style="max-height: 80vh; object-fit: contain;">
                <iframe id="docPreviewFrame" class="d-none w-100 border-0" style="height: 80vh;" title="Pratinjau dokumen"></iframe>
                <div id="docPreviewError" class="text-center text-muted p-4 d-none">
                    <i class="ti ti-file-alert fs-1 d-block mb-2"></i>
                    <span id="docPreviewErrorText">Dokumen tidak dapat ditampilkan.</span>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    (function () {
        var modalEl = document.getElementById('docPreviewModal');
        if (!modalEl || !window.bootstrap) return;
        var modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        var img = document.getElementById('docPreviewImage');
        var frame = document.getElementById('docPreviewFrame');
        var loading = document.getElementById('docPreviewLoading');
        var errorBox = document.getElementById('docPreviewError');
        var errorText = document.getElementById('docPreviewErrorText');
        var downloadBtn = document.getElementById('docPreviewDownload');

        function withDownload(url) {
            return url + (url.indexOf('?') === -1 ? '?' : '&') + 'download=1';
        }
        function reset() {
            img.classList.add('d-none'); img.removeAttribute('src');
            frame.classList.add('d-none'); frame.removeAttribute('src');
            errorBox.classList.add('d-none');
            loading.classList.remove('d-none');
        }
        function fail(text) {
            loading.classList.add('d-none');
            img.classList.add('d-none');
            frame.classList.add('d-none');
            errorText.textContent = text;
            errorBox.classList.remove('d-none');
        }

        // Browser HP (mis. Chrome Android) umumnya tidak bisa menampilkan PDF di dalam halaman
        var canInlinePdf = !!(navigator.pdfViewerEnabled || !/Android|iPhone|iPad|Mobile/i.test(navigator.userAgent));

        window.DocPreview = {
            open: function (url, kind, title) {
                reset();
                document.getElementById('docPreviewTitle').textContent = title || 'Dokumen';
                downloadBtn.href = withDownload(url);
                modal.show();

                if (kind === 'pdf') {
                    if (!canInlinePdf) {
                        fail('Pratinjau PDF tidak didukung di perangkat ini. Gunakan tombol Unduh.');
                        return;
                    }
                    frame.onload = function () { loading.classList.add('d-none'); };
                    frame.src = url;
                    frame.classList.remove('d-none');
                    return;
                }

                img.onload = function () { loading.classList.add('d-none'); img.classList.remove('d-none'); };
                img.onerror = function () { fail('Dokumen tidak dapat ditampilkan. Coba unduh file-nya.'); };
                img.src = url;
            }
        };

        modalEl.addEventListener('hidden.bs.modal', reset);
        // Dibuka dari modal lain (mis. modal OCR): jaga body tetap dalam mode modal
        modalEl.addEventListener('shown.bs.modal', function () {
            var backdrops = document.querySelectorAll('.modal-backdrop');
            if (backdrops.length > 1) backdrops[backdrops.length - 1].style.zIndex = 1060;
            modalEl.style.zIndex = 1065;
        });
        modalEl.addEventListener('hidden.bs.modal', function () {
            if (document.querySelector('.modal.show')) document.body.classList.add('modal-open');
        });

        document.addEventListener('click', function (e) {
            var link = e.target.closest && e.target.closest('a[data-doc-preview]');
            if (!link) return;
            e.preventDefault();
            window.DocPreview.open(link.getAttribute('href'), link.dataset.kind || 'image', link.dataset.title);
        });
    })();
</script>
