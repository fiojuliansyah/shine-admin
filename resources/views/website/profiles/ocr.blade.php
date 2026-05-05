@extends('admin.layouts.main')

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
                            <div id="preview-box" class="border rounded-3 d-flex align-items-center justify-content-center bg-light" style="min-height: 250px;">
                                <img id="ktp-preview" src="#" class="img-fluid rounded-3" style="display: none; max-height: 300px;">
                                <div id="placeholder-ui">
                                    <i class="ti ti-id-badge text-muted" style="font-size: 5rem;"></i>
                                    <p class="text-muted mt-2">Pilih foto KTP yang jelas</p>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <input type="file" id="input-ktp" class="form-control" accept="image/*">
                        </div>

                        <button id="btn-scan" class="btn btn-primary w-100 py-2" disabled>
                            Mulai Scan OCR
                        </button>

                        <div id="ocr-loader" class="mt-3" style="display: none;">
                            <div class="progress progress-xs mb-1">
                                <div id="ocr-progress" class="progress-bar bg-success" style="width: 0%"></div>
                            </div>
                            <small class="text-muted" id="ocr-status text-truncate">Memproses...</small>
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
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Alamat</label>
                                    <textarea name="address" id="res-alamat" class="form-control" rows="3"></textarea>
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

<script src="https://cdn.jsdelivr.net/npm/tesseract.js@5/dist/tesseract.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const inputKtp = document.getElementById('input-ktp');
    const previewImg = document.getElementById('ktp-preview');
    const placeholder = document.getElementById('placeholder-ui');
    const btnScan = document.getElementById('btn-scan');
    const loader = document.getElementById('ocr-loader');
    const progressBar = document.getElementById('ocr-progress');
    const statusText = document.getElementById('ocr-status');

    inputKtp.addEventListener('change', function () {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function (e) {
                previewImg.src = e.target.result;
                previewImg.style.display = 'block';
                placeholder.style.display = 'none';
                btnScan.disabled = false;
            }
            reader.readAsDataURL(file);
        }
    });

    btnScan.addEventListener('click', async function () {
        loader.style.display = 'block';
        btnScan.disabled = true;

        try {
            const worker = await Tesseract.createWorker('ind');
            const { data: { text } } = await worker.recognize(previewImg.src);
            
            const nikMatch = text.match(/\d{16}/);
            if (nikMatch) document.getElementById('res-nik').value = nikMatch[0];

            const lines = text.split('\n');
            lines.forEach(line => {
                const clean = line.toUpperCase().replace(/:/g, "").trim();
                if (clean.includes("NAMA")) document.getElementById('res-nama').value = clean.replace("NAMA", "").trim();
                if (clean.includes("ALAMAT")) document.getElementById('res-alamat').value = clean.replace("ALAMAT", "").trim();
                if (clean.includes("TEMPAT")) {
                    let ttl = clean.replace("TEMPAT/TGL LAHIR", "").trim();
                    let parts = ttl.split(",");
                    if (parts.length > 1) {
                        document.getElementById('res-tempat').value = parts[0].trim();
                        document.getElementById('res-tgl').value = parts[1].trim();
                    }
                }
            });

            await worker.terminate();
        } catch (err) {
            alert("Terjadi kesalahan saat memproses gambar.");
        } finally {
            loader.style.display = 'none';
            btnScan.disabled = false;
        }
    });
});
</script>
@endsection