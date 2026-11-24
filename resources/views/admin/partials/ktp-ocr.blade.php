{{--
    Modal Scan KTP (OCR) yang bisa dipakai ulang.

    Parameter:
      $ocrTargets   : [field OCR => selector input form], field: nik, name, gender, birth_place,
                      birth_date (YYYY-MM-DD), address, rt_rw, kelurahan, kecamatan
      $ocrOpenModal : id modal yang dibuka setelah "Isi ke Form" (opsional)
      $ocrUserId    : user pemilik KTP; foto KTP disimpan ke dokumen user ini (default: user login)

    Tombol pemicu: data-bs-toggle="modal" data-bs-target="#ktpScanModal".
    Setelah diisi, event "ktp-ocr:applied" (detail = hasil OCR) dikirim ke document.
--}}
    <div class="modal fade" id="ktpScanModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <div class="d-flex align-items-center">
                        <h4 class="modal-title me-2">Scan KTP (OCR)</h4>
                    </div>
                    <button type="button" class="btn-close custom-btn-close" data-bs-dismiss="modal"
                        aria-label="Close">
                        <i class="ti ti-x"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info py-2 fs-12">
                        Unggah foto/scan KTP yang jelas, lalu klik <strong>Mulai Scan</strong>. Gambar dikirim ke
                        server dan dibaca otomatis oleh layanan OCR. Foto KTP juga disimpan ke <strong>Dokumen</strong>
                        (menggantikan dokumen KTP sebelumnya).
                    </div>

                    <div class="mb-2">
                        <input type="file" id="ktpFileInput" class="form-control" accept="image/png,image/jpeg,image/webp">
                    </div>
                    <div class="border rounded text-center d-flex align-items-center justify-content-center"
                        style="min-height:180px; background:#f6f7f9; overflow:hidden;">
                        <img id="ktpPreview" alt="Preview KTP" class="img-fluid d-none" style="max-height:320px;">
                        <div id="ktpCanvasPlaceholder" class="text-muted py-5">
                            <i class="ti ti-photo fs-1"></i>
                            <div class="mt-2 fs-13">Belum ada gambar KTP</div>
                        </div>
                    </div>
                    <div id="ktpProgress" class="progress mt-2 d-none" style="height:18px;">
                        <div id="ktpProgressBar" class="progress-bar progress-bar-striped progress-bar-animated"
                            role="progressbar" style="width:0%">0%</div>
                    </div>
                    <div id="ktpDocStatus" class="small mt-2 d-none"></div>

                    <hr>
                    <h6 class="fw-semibold">Hasil OCR</h6>
                    <div class="row g-2" id="ktpResultWrap">
                        <div class="col-md-6"><label class="form-label fs-12 mb-0">NIK</label>
                            <input type="text" id="ktpRes_nik" class="form-control form-control-sm"></div>
                        <div class="col-md-6"><label class="form-label fs-12 mb-0">Nama</label>
                            <input type="text" id="ktpRes_name" class="form-control form-control-sm"></div>
                        <div class="col-md-6"><label class="form-label fs-12 mb-0">Jenis Kelamin</label>
                            <input type="text" id="ktpRes_gender" class="form-control form-control-sm"></div>
                        <div class="col-md-6"><label class="form-label fs-12 mb-0">Tempat Lahir</label>
                            <input type="text" id="ktpRes_birth_place" class="form-control form-control-sm"></div>
                        <div class="col-md-6"><label class="form-label fs-12 mb-0">Tanggal Lahir</label>
                            <input type="text" id="ktpRes_birth_date" class="form-control form-control-sm"
                                placeholder="YYYY-MM-DD"></div>
                        <div class="col-md-6"><label class="form-label fs-12 mb-0">RT/RW</label>
                            <input type="text" id="ktpRes_rt_rw" class="form-control form-control-sm"></div>
                        <div class="col-md-12"><label class="form-label fs-12 mb-0">Alamat</label>
                            <input type="text" id="ktpRes_address" class="form-control form-control-sm"></div>
                        <div class="col-md-6"><label class="form-label fs-12 mb-0">Kel/Desa</label>
                            <input type="text" id="ktpRes_kelurahan" class="form-control form-control-sm"></div>
                        <div class="col-md-6"><label class="form-label fs-12 mb-0">Kecamatan</label>
                            <input type="text" id="ktpRes_kecamatan" class="form-control form-control-sm"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-light border me-2" data-bs-dismiss="modal">Tutup</button>
                    <button type="button" id="ktpScanBtn" class="btn btn-dark" disabled>
                        <i class="ti ti-scan me-1"></i>Mulai Scan
                    </button>
                    <button type="button" id="ktpApplyBtn" class="btn btn-primary" disabled>
                        <i class="ti ti-check me-1"></i>Isi ke Form Profil
                    </button>
                </div>
            </div>
        </div>
    </div>

@once
    @push('js')
    <script>
        (function () {
            var preview = document.getElementById('ktpPreview');
            var placeholder = document.getElementById('ktpCanvasPlaceholder');
            var fileInput = document.getElementById('ktpFileInput');
            var scanBtn = document.getElementById('ktpScanBtn');
            var applyBtn = document.getElementById('ktpApplyBtn');
            var progress = document.getElementById('ktpProgress');
            var progressBar = document.getElementById('ktpProgressBar');
            var scanUrl = '{{ route('ktp-ocr.scan') }}';
            var ocrTargets = @json($ocrTargets ?? []);
            var ocrOpenModal = @json($ocrOpenModal ?? null);
            var ocrUserId = @json($ocrUserId ?? null);
            var docStatus = document.getElementById('ktpDocStatus');
            var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            var FIELDS = ['nik', 'name', 'gender', 'birth_place', 'birth_date', 'address', 'rt_rw', 'kelurahan', 'kecamatan'];

            var currentFile = null;

            function fillResults(m) {
                FIELDS.forEach(function (key) {
                    document.getElementById('ktpRes_' + key).value = m[key] || '';
                });
            }

            function showDocStatus(json) {
                docStatus.classList.remove('d-none', 'text-success', 'text-danger');
                if (json.document) {
                    docStatus.classList.add('text-success');
                    docStatus.innerHTML = '<i class="ti ti-circle-check me-1"></i>Foto KTP tersimpan ke Dokumen. ';
                    if (json.document.url) {
                        var link = document.createElement('a');
                        link.href = json.document.url;
                        link.textContent = 'Lihat';
                        link.setAttribute('data-doc-preview', '');
                        link.dataset.kind = json.document.kind || 'image';
                        link.dataset.title = 'KTP';
                        docStatus.appendChild(link);
                    }
                } else if (json.document_error) {
                    docStatus.classList.add('text-danger');
                    docStatus.textContent = json.document_error;
                } else {
                    docStatus.classList.add('d-none');
                }
            }

            function setProgress(pct, text) {
                progress.classList.remove('d-none');
                progressBar.style.width = pct + '%';
                progressBar.textContent = text || (pct + '%');
            }

            fileInput.addEventListener('change', function (e) {
                var file = e.target.files && e.target.files[0];
                if (!file) return;
                currentFile = file;
                if (preview.src) URL.revokeObjectURL(preview.src);
                preview.src = URL.createObjectURL(file);
                preview.classList.remove('d-none');
                placeholder.style.display = 'none';
                progress.classList.add('d-none');
                scanBtn.disabled = false;
                applyBtn.disabled = true;
            });

            scanBtn.addEventListener('click', function () {
                if (!currentFile) { alert('Unggah gambar KTP terlebih dahulu.'); return; }
                scanBtn.disabled = true;
                applyBtn.disabled = true;
                setProgress(40, 'Membaca KTP...');

                docStatus.classList.add('d-none');
                var fd = new FormData();
                fd.append('image', currentFile);
                if (ocrUserId) fd.append('user_id', ocrUserId);

                fetch(scanUrl, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                    body: fd
                }).then(function (resp) {
                    return resp.json().catch(function () { return {}; }).then(function (json) {
                        if (!resp.ok) throw new Error(json.message || 'Gagal memproses OCR');
                        return json;
                    });
                }).then(function (json) {
                    fillResults(json.mapped || {});
                    setProgress(100, 'Selesai');
                    applyBtn.disabled = false;
                    showDocStatus(json);
                }).catch(function (err) {
                    setProgress(0, 'Gagal');
                    alert(err.message || 'OCR gagal');
                }).finally(function () {
                    scanBtn.disabled = false;
                });
            });

            applyBtn.addEventListener('click', function () {
                var m = {};
                FIELDS.forEach(function (key) {
                    m[key] = document.getElementById('ktpRes_' + key).value;
                });

                // $.val() agar input tanggal (flatpickr) & select ikut ter-update
                Object.keys(ocrTargets).forEach(function (key) {
                    var $el = jQuery(ocrTargets[key]);
                    if ($el.length && m[key]) $el.val(m[key]).trigger('change');
                });

                document.dispatchEvent(new CustomEvent('ktp-ocr:applied', { detail: m }));

                bootstrap.Modal.getInstance(document.getElementById('ktpScanModal')).hide();
                if (ocrOpenModal && document.getElementById(ocrOpenModal)) {
                    new bootstrap.Modal(document.getElementById(ocrOpenModal)).show();
                }
            });
        })();
    </script>
    @endpush
@endonce
