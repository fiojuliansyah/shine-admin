@extends('website.layouts.app')

@section('content')
<div class="page-wrapper">
    <div class="content">
        <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
            <div class="my-auto mb-2">
                <h6 class="fw-medium d-inline-flex align-items-center mb-3 mb-sm-0">
                    <a href="{{ route('applicants.profiles.index') }}">
                        <i class="ti ti-arrow-left me-2"></i>Kembali ke Profil
                    </a>
                </h6>
            </div>
        </div>

        <div class="row">
            <div class="col-xl-5">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-primary-transparent">
                        <h5 class="card-title mb-0">Upload KTP</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Metode Scan</label>
                            <select id="ocr-method" class="form-select">
                                <option value="local">OCR Sekarang (Polygon - di perangkat)</option>
                                <option value="openai">OpenAI Vision (akurasi tinggi)</option>
                            </select>
                        </div>

                        <div class="text-center mb-3">
                            <div id="preview-box" class="border rounded-3 d-flex align-items-center justify-content-center bg-light" style="min-height:250px; position:relative; overflow:hidden;">
                                <img id="ktp-preview" src="#" class="img-fluid rounded-3" style="display:none; max-height:300px;">
                                <canvas id="ktp-canvas" style="display:none; max-width:100%; cursor:crosshair;"></canvas>
                                <div id="placeholder-ui">
                                    <i class="ti ti-id-badge text-muted" style="font-size:5rem;"></i>
                                    <p class="text-muted mt-2">Pilih foto KTP yang jelas</p>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <input type="file" id="input-ktp" class="form-control" accept="image/*">
                        </div>

                        <div id="polygon-tools" class="mb-3" style="display:none;">
                            <label class="form-label text-muted small mb-1">Field aktif (klik lalu geser kotak di gambar)</label>
                            <div id="field-list" class="d-flex flex-wrap gap-1 mb-2"></div>
                            <div class="d-flex gap-2">
                                <button type="button" id="btn-reset-template" class="btn btn-sm btn-outline-secondary">
                                    <i class="ti ti-rotate me-1"></i>Reset
                                </button>
                                <button type="button" id="btn-save-template" class="btn btn-sm btn-outline-success">
                                    <i class="ti ti-device-floppy me-1"></i>Simpan Template
                                </button>
                            </div>
                        </div>

                        <button id="btn-scan" class="btn btn-primary w-100 py-2" disabled>
                            <i class="ti ti-scan me-1"></i>Mulai Scan OCR
                        </button>

                        <div id="ocr-loader" class="mt-3" style="display:none;">
                            <div class="progress progress-xs mb-1">
                                <div id="ocr-progress" class="progress-bar bg-success" style="width:0%"></div>
                            </div>
                            <small class="text-muted" id="ocr-status">Memproses...</small>
                        </div>

                        <div id="raw-text-box" class="mt-3" style="display:none;">
                            <label class="form-label text-muted small">Raw OCR (untuk debug)</label>
                            <textarea id="raw-text" class="form-control form-control-sm font-monospace" rows="6" readonly style="font-size:11px;"></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-7">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-dark-transparent">
                        <h5 class="card-title mb-0">Hasil Ekstraksi Data</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('applicant.profiles.update-ocr') }}" method="POST">
                            @csrf
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">NIK</label>
                                    <input type="text" name="nik" id="res-nik" class="form-control fw-bold">
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Nama Lengkap</label>
                                    <input type="text" name="name" id="res-nama" class="form-control">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Tempat Lahir</label>
                                    <input type="text" name="birth_place" id="res-tempat" class="form-control">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Tanggal Lahir</label>
                                    <input type="text" name="birth_date" id="res-tgl" class="form-control">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Jenis Kelamin</label>
                                    <input type="text" name="gender" id="res-gender" class="form-control">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">RT/RW</label>
                                    <input type="text" name="rt_rw" id="res-rtrw" class="form-control">
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Alamat</label>
                                    <textarea name="address" id="res-alamat" class="form-control" rows="3"></textarea>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Kel/Desa</label>
                                    <input type="text" name="kelurahan" id="res-kelurahan" class="form-control">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Kecamatan</label>
                                    <input type="text" name="kecamatan" id="res-kecamatan" class="form-control">
                                </div>
                            </div>
                            <div class="d-flex justify-content-end border-top pt-3">
                                <button type="submit" class="btn btn-success px-5">Simpan Perubahan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="/admin/assets/js/ktp-ocr.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const inputKtp    = document.getElementById('input-ktp');
    const previewImg  = document.getElementById('ktp-preview');
    const canvas      = document.getElementById('ktp-canvas');
    const placeholder = document.getElementById('placeholder-ui');
    const btnScan     = document.getElementById('btn-scan');
    const loader      = document.getElementById('ocr-loader');
    const progressBar = document.getElementById('ocr-progress');
    const statusText  = document.getElementById('ocr-status');
    const rawTextBox  = document.getElementById('raw-text-box');
    const rawText     = document.getElementById('raw-text');
    const methodSel   = document.getElementById('ocr-method');
    const polygonTools= document.getElementById('polygon-tools');
    const fieldList   = document.getElementById('field-list');
    const btnReset    = document.getElementById('btn-reset-template');
    const btnSave     = document.getElementById('btn-save-template');

    const openaiUrl = '{{ route('applicant.profiles.ocr-openai') }}';
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    let editor = null;
    let sourceCanvas = null;
    let currentFile = null;

    function buildFieldList() {
        if (!window.KtpOcr) return;
        fieldList.innerHTML = '';
        KtpOcr.FIELDS.forEach(function (f, idx) {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'btn btn-sm ' + (idx === 0 ? 'btn-dark' : 'btn-outline-dark');
            btn.textContent = f.label;
            btn.dataset.key = f.key;
            btn.addEventListener('click', function () {
                fieldList.querySelectorAll('button').forEach(function (b) {
                    b.className = 'btn btn-sm btn-outline-dark';
                });
                btn.className = 'btn btn-sm btn-dark';
                if (editor) editor.setActiveField(f.key);
            });
            fieldList.appendChild(btn);
        });
    }

    function applyMethodUi() {
        const isLocal = methodSel.value === 'local';
        polygonTools.style.display = (isLocal && sourceCanvas) ? 'block' : 'none';
        if (isLocal) {
            canvas.style.display = sourceCanvas ? 'block' : 'none';
            previewImg.style.display = 'none';
        } else {
            canvas.style.display = 'none';
            previewImg.style.display = currentFile ? 'block' : 'none';
        }
    }
    methodSel.addEventListener('change', applyMethodUi);

    function setField(id, value) {
        const el = document.getElementById(id);
        if (el && value) el.value = value;
    }

    function fillResults(m) {
        setField('res-nik', m.nik);
        setField('res-nama', m.name);
        setField('res-gender', m.gender);
        setField('res-tempat', m.birth_place);
        setField('res-tgl', m.birth_date);
        setField('res-rtrw', m.rt_rw);
        setField('res-alamat', m.address);
        setField('res-kelurahan', m.kelurahan);
        setField('res-kecamatan', m.kecamatan);
    }

    inputKtp.addEventListener('change', function () {
        const file = this.files[0];
        if (!file) return;
        currentFile = file;

        const reader = new FileReader();
        reader.onload = e => { previewImg.src = e.target.result; };
        reader.readAsDataURL(file);

        if (window.KtpOcr) {
            KtpOcr.loadImageToCanvas(file).then(function (res) {
                sourceCanvas = res.canvas;
                placeholder.style.display = 'none';
                if (!editor) {
                    editor = new KtpOcr.PolygonEditor(canvas, { template: KtpOcr.loadTemplate() });
                    buildFieldList();
                }
                editor.setImage(res.image);
                btnScan.disabled = false;
                applyMethodUi();
            }).catch(function () {
                placeholder.style.display = 'none';
                btnScan.disabled = false;
                applyMethodUi();
            });
        } else {
            placeholder.style.display = 'none';
            btnScan.disabled = false;
            applyMethodUi();
        }
    });

    function runLocalScan() {
        if (!sourceCanvas || !editor) { alert('Gambar belum siap.'); return; }
        loader.style.display = 'block';
        progressBar.style.width = '0%';
        statusText.textContent = 'Memproses...';
        btnScan.disabled = true;

        KtpOcr.scan(sourceCanvas, editor.getTemplate(), function (phase, value, label) {
            const pct = Math.round(value * 100);
            progressBar.style.width = pct + '%';
            statusText.textContent = (phase === 'field' ? 'Membaca ' + (label || '') : 'OCR...') + ' ' + pct + '%';
        }).then(function (result) {
            rawText.value = JSON.stringify(result.raw, null, 2);
            rawTextBox.style.display = 'block';
            fillResults(result.mapped);
            progressBar.style.width = '100%';
            statusText.textContent = 'Selesai!';
        }).catch(function (err) {
            alert(err.message || 'OCR gagal');
        }).finally(function () {
            loader.style.display = 'none';
            btnScan.disabled = false;
        });
    }

    function runOpenaiScan() {
        if (!currentFile) { alert('Unggah gambar KTP terlebih dahulu.'); return; }
        loader.style.display = 'block';
        progressBar.style.width = '30%';
        statusText.textContent = 'Mengirim ke OpenAI...';
        btnScan.disabled = true;

        const fd = new FormData();
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
            rawText.value = JSON.stringify(json.mapped, null, 2);
            rawTextBox.style.display = 'block';
            fillResults(json.mapped || {});
            progressBar.style.width = '100%';
            statusText.textContent = 'Selesai!';
        }).catch(function (err) {
            alert(err.message || 'OpenAI gagal');
        }).finally(function () {
            loader.style.display = 'none';
            btnScan.disabled = false;
        });
    }

    btnScan.addEventListener('click', function () {
        if (methodSel.value === 'openai') runOpenaiScan();
        else runLocalScan();
    });

    if (btnReset) btnReset.addEventListener('click', function () { if (editor) editor.resetTemplate(); });
    if (btnSave) btnSave.addEventListener('click', function () {
        if (!editor) return;
        KtpOcr.saveTemplate(editor.getTemplate());
        btnSave.innerHTML = '<i class="ti ti-check me-1"></i>Tersimpan';
        setTimeout(function () { btnSave.innerHTML = '<i class="ti ti-device-floppy me-1"></i>Simpan Template'; }, 1500);
    });

    window.addEventListener('resize', function () {
        if (editor && sourceCanvas) { editor.resize(); editor.draw(); }
    });
});
</script>
@endsection
