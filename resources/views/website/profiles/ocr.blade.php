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
                        <div class="text-center mb-3">
                            <div id="preview-box" class="border rounded-3 d-flex align-items-center justify-content-center bg-light" style="min-height:250px; overflow:hidden;">
                                <img id="ktp-preview" src="#" alt="Preview KTP" class="img-fluid rounded-3" style="display:none; max-height:300px;">
                                <div id="placeholder-ui">
                                    <i class="ti ti-id-badge text-muted" style="font-size:5rem;"></i>
                                    <p class="text-muted mt-2">Pilih foto KTP yang jelas</p>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <input type="file" id="input-ktp" class="form-control" accept="image/png,image/jpeg,image/webp" data-doc-crop>
                        </div>

                        <button id="btn-scan" class="btn btn-primary w-100 py-2" disabled>
                            <i class="ti ti-scan me-1"></i>Mulai Scan OCR
                        </button>

                        <p class="text-muted small mt-2 mb-0">Foto KTP juga otomatis disimpan ke Dokumen Anda (menggantikan KTP sebelumnya).</p>
                        <div id="ocr-doc-status" class="small mt-2" style="display:none;"></div>

                        <div id="ocr-loader" class="mt-3" style="display:none;">
                            <div class="progress progress-xs mb-1">
                                <div id="ocr-progress" class="progress-bar bg-success" style="width:0%"></div>
                            </div>
                            <small class="text-muted" id="ocr-status">Memproses...</small>
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

<script>
document.addEventListener('DOMContentLoaded', function () {
    const inputKtp    = document.getElementById('input-ktp');
    const previewImg  = document.getElementById('ktp-preview');
    const placeholder = document.getElementById('placeholder-ui');
    const btnScan     = document.getElementById('btn-scan');
    const loader      = document.getElementById('ocr-loader');
    const progressBar = document.getElementById('ocr-progress');
    const statusText  = document.getElementById('ocr-status');

    const scanUrl   = '{{ route('applicant.profiles.ocr-scan') }}';
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    let currentFile = null;

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
        reader.onload = e => {
            previewImg.src = e.target.result;
            previewImg.style.display = 'block';
            placeholder.style.display = 'none';
        };
        reader.readAsDataURL(file);
        btnScan.disabled = false;
    });

    btnScan.addEventListener('click', function () {
        if (!currentFile) { alert('Unggah gambar KTP terlebih dahulu.'); return; }
        loader.style.display = 'block';
        progressBar.style.width = '40%';
        statusText.textContent = 'Membaca KTP...';
        btnScan.disabled = true;

        const fd = new FormData();
        fd.append('image', currentFile);

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
            progressBar.style.width = '100%';
            statusText.textContent = 'Selesai!';

            const docStatus = document.getElementById('ocr-doc-status');
            if (json.document) {
                docStatus.className = 'small mt-2 text-success';
                docStatus.innerHTML = '<i class="ti ti-circle-check me-1"></i>Foto KTP tersimpan ke Dokumen.';
                docStatus.style.display = 'block';
            } else if (json.document_error) {
                docStatus.className = 'small mt-2 text-danger';
                docStatus.textContent = json.document_error;
                docStatus.style.display = 'block';
            }
        }).catch(function (err) {
            alert(err.message || 'OCR gagal');
        }).finally(function () {
            loader.style.display = 'none';
            btnScan.disabled = false;
        });
    });
});
</script>
@include('partials.document-crop')
@endsection
