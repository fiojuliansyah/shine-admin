{{--
    Modal Scan KTP (OCR) yang bisa dipakai ulang.

    Parameter:
      $ocrTargets   : [field OCR => selector input form], field: nik, name, gender, birth_place,
                      birth_date (YYYY-MM-DD), address, rt_rw, kelurahan, kecamatan
      $ocrOpenModal : id modal yang dibuka setelah "Isi ke Form" (opsional)

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
                    <div class="row mb-2">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold mb-1">Metode Scan</label>
                            <select id="ktpMethod" class="form-select">
                                <option value="local">OCR Sekarang (Polygon - di perangkat)</option>
                                <option value="openai">OpenAI Vision (akurasi tinggi)</option>
                            </select>
                        </div>
                    </div>
                    <div id="ktpInfoLocal" class="alert alert-info py-2 fs-12">
                        Unggah foto/scan KTP, sesuaikan kotak polygon agar pas di tiap field, lalu klik
                        <strong>Mulai Scan</strong>. Proses OCR berjalan di perangkat Anda.
                    </div>
                    <div id="ktpInfoOpenai" class="alert alert-warning py-2 fs-12 d-none">
                        Gambar KTP dikirim ke server lalu diteruskan ke OpenAI untuk dibaca. Polygon tidak
                        dipakai pada metode ini. Klik <strong>Mulai Scan</strong> setelah mengunggah gambar.
                    </div>

                    <div class="row">
                        <div class="col-md-7">
                            <div class="mb-2">
                                <input type="file" id="ktpFileInput" class="form-control" accept="image/*">
                            </div>
                            <div id="ktpCanvasWrap" class="border rounded position-relative"
                                style="min-height:180px; background:#f6f7f9; overflow:hidden;">
                                <canvas id="ktpCanvas" style="width:100%; display:block; cursor:crosshair;"></canvas>
                                <div id="ktpCanvasPlaceholder" class="text-center text-muted py-5">
                                    <i class="ti ti-photo fs-1"></i>
                                    <div class="mt-2 fs-13">Belum ada gambar KTP</div>
                                </div>
                            </div>
                            <div id="ktpProgress" class="progress mt-2 d-none" style="height:18px;">
                                <div id="ktpProgressBar" class="progress-bar progress-bar-striped progress-bar-animated"
                                    role="progressbar" style="width:0%">0%</div>
                            </div>
                        </div>
                        <div class="col-md-5" id="ktpPolygonPanel">
                            <label class="form-label fw-semibold mb-1">Field aktif (klik untuk atur polygon)</label>
                            <div id="ktpFieldList" class="list-group mb-2" style="max-height:200px; overflow:auto;"></div>
                            <div class="d-flex gap-2 flex-wrap">
                                <button type="button" id="ktpResetTemplate" class="btn btn-sm btn-outline-secondary">
                                    <i class="ti ti-rotate me-1"></i>Reset Template
                                </button>
                                <button type="button" id="ktpSaveTemplate" class="btn btn-sm btn-outline-success">
                                    <i class="ti ti-device-floppy me-1"></i>Simpan Template
                                </button>
                            </div>
                        </div>
                    </div>

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
    <script src="/admin/assets/js/ktp-ocr.js"></script>
    <script>
        (function () {
            if (!window.KtpOcr) return;

            var canvas = document.getElementById('ktpCanvas');
            var placeholder = document.getElementById('ktpCanvasPlaceholder');
            var fileInput = document.getElementById('ktpFileInput');
            var fieldList = document.getElementById('ktpFieldList');
            var scanBtn = document.getElementById('ktpScanBtn');
            var applyBtn = document.getElementById('ktpApplyBtn');
            var resetBtn = document.getElementById('ktpResetTemplate');
            var saveBtn = document.getElementById('ktpSaveTemplate');
            var progress = document.getElementById('ktpProgress');
            var progressBar = document.getElementById('ktpProgressBar');
            var methodSel = document.getElementById('ktpMethod');
            var polygonPanel = document.getElementById('ktpPolygonPanel');
            var infoLocal = document.getElementById('ktpInfoLocal');
            var infoOpenai = document.getElementById('ktpInfoOpenai');
            var openaiUrl = '{{ route('ktp-ocr.openai') }}';
            var ocrTargets = @json($ocrTargets ?? []);
            var ocrOpenModal = @json($ocrOpenModal ?? null);
            var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            var editor = null;
            var sourceCanvas = null;
            var currentFile = null;
            var lastResult = null;

            function applyMethodUi() {
                var isOpenai = methodSel.value === 'openai';
                polygonPanel.classList.toggle('d-none', isOpenai);
                infoLocal.classList.toggle('d-none', isOpenai);
                infoOpenai.classList.toggle('d-none', !isOpenai);
            }
            methodSel.addEventListener('change', applyMethodUi);
            applyMethodUi();

            function fillResults(m) {
                document.getElementById('ktpRes_nik').value = m.nik || '';
                document.getElementById('ktpRes_name').value = m.name || '';
                document.getElementById('ktpRes_gender').value = m.gender || '';
                document.getElementById('ktpRes_birth_place').value = m.birth_place || '';
                document.getElementById('ktpRes_birth_date').value = m.birth_date || '';
                document.getElementById('ktpRes_address').value = m.address || '';
                document.getElementById('ktpRes_rt_rw').value = m.rt_rw || '';
                document.getElementById('ktpRes_kelurahan').value = m.kelurahan || '';
                document.getElementById('ktpRes_kecamatan').value = m.kecamatan || '';
            }

            function buildFieldList() {
                fieldList.innerHTML = '';
                KtpOcr.FIELDS.forEach(function (f, idx) {
                    var btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = 'list-group-item list-group-item-action d-flex align-items-center' +
                        (idx === 0 ? ' active' : '');
                    btn.dataset.key = f.key;
                    var dot = document.createElement('span');
                    dot.style.cssText = 'display:inline-block;width:12px;height:12px;border-radius:3px;margin-right:8px;background:' +
                        (KtpOcr.FIELD_COLORS[f.key] || '#3498db');
                    btn.appendChild(dot);
                    btn.appendChild(document.createTextNode(f.label));
                    btn.addEventListener('click', function () {
                        fieldList.querySelectorAll('.list-group-item').forEach(function (el) {
                            el.classList.remove('active');
                        });
                        btn.classList.add('active');
                        if (editor) editor.setActiveField(f.key);
                    });
                    fieldList.appendChild(btn);
                });
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
                KtpOcr.loadImageToCanvas(file).then(function (res) {
                    sourceCanvas = res.canvas;
                    placeholder.style.display = 'none';
                    canvas.style.display = 'block';
                    if (!editor) {
                        editor = new KtpOcr.PolygonEditor(canvas, { template: KtpOcr.loadTemplate() });
                        buildFieldList();
                    }
                    editor.setImage(res.image);
                    scanBtn.disabled = false;
                    applyBtn.disabled = true;
                }).catch(function (err) {
                    alert(err.message || 'Gagal memuat gambar');
                });
            });

            function runLocalScan() {
                if (!sourceCanvas || !editor) return;
                scanBtn.disabled = true;
                applyBtn.disabled = true;
                setProgress(2, 'Memuat OCR...');
                KtpOcr.scan(sourceCanvas, editor.getTemplate(), function (phase, value, label) {
                    if (phase === 'field') {
                        setProgress(Math.round(value * 100), 'Membaca ' + (label || ''));
                    } else if (phase === 'ocr') {
                        setProgress(Math.round(value * 100), 'OCR...');
                    }
                }).then(function (result) {
                    lastResult = result;
                    fillResults(result.mapped);
                    setProgress(100, 'Selesai');
                    scanBtn.disabled = false;
                    applyBtn.disabled = false;
                }).catch(function (err) {
                    setProgress(0, 'Gagal');
                    alert(err.message || 'OCR gagal');
                    scanBtn.disabled = false;
                });
            }

            function runOpenaiScan() {
                if (!currentFile) { alert('Unggah gambar KTP terlebih dahulu.'); return; }
                scanBtn.disabled = true;
                applyBtn.disabled = true;
                setProgress(20, 'Mengirim ke OpenAI...');

                var fd = new FormData();
                fd.append('image', currentFile);

                fetch(openaiUrl, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                    body: fd
                }).then(function (resp) {
                    return resp.json().then(function (json) {
                        if (!resp.ok) throw new Error(json.message || 'Gagal memproses');
                        return json;
                    });
                }).then(function (json) {
                    fillResults(json.mapped || {});
                    setProgress(100, 'Selesai');
                    scanBtn.disabled = false;
                    applyBtn.disabled = false;
                }).catch(function (err) {
                    setProgress(0, 'Gagal');
                    alert(err.message || 'OpenAI gagal');
                    scanBtn.disabled = false;
                });
            }

            scanBtn.addEventListener('click', function () {
                if (methodSel.value === 'openai') runOpenaiScan();
                else runLocalScan();
            });

            applyBtn.addEventListener('click', function () {
                var m = {};
                ['nik', 'name', 'gender', 'birth_place', 'birth_date', 'address', 'rt_rw', 'kelurahan', 'kecamatan'].forEach(function (key) {
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

            resetBtn.addEventListener('click', function () {
                if (editor) editor.resetTemplate();
            });

            saveBtn.addEventListener('click', function () {
                if (!editor) return;
                KtpOcr.saveTemplate(editor.getTemplate());
                saveBtn.innerHTML = '<i class="ti ti-check me-1"></i>Tersimpan';
                setTimeout(function () {
                    saveBtn.innerHTML = '<i class="ti ti-device-floppy me-1"></i>Simpan Template';
                }, 1500);
            });

            window.addEventListener('resize', function () {
                if (editor && sourceCanvas) { editor.resize(); editor.draw(); }
            });
        })();
    </script>
    @endpush
@endonce
