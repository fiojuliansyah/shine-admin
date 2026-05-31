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
                            <div id="preview-box" class="border rounded-3 d-flex align-items-center justify-content-center bg-light" style="min-height:250px;">
                                <img id="ktp-preview" src="#" class="img-fluid rounded-3" style="display:none; max-height:300px;">
                                <div id="placeholder-ui">
                                    <i class="ti ti-id-badge text-muted" style="font-size:5rem;"></i>
                                    <p class="text-muted mt-2">Pilih foto KTP yang jelas</p>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <input type="file" id="input-ktp" class="form-control" accept="image/*">
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
    const inputKtp   = document.getElementById('input-ktp');
    const previewImg = document.getElementById('ktp-preview');
    const placeholder= document.getElementById('placeholder-ui');
    const btnScan    = document.getElementById('btn-scan');
    const loader     = document.getElementById('ocr-loader');
    const progressBar= document.getElementById('ocr-progress');
    const statusText = document.getElementById('ocr-status');
    const rawTextBox = document.getElementById('raw-text-box');
    const rawText    = document.getElementById('raw-text');

    inputKtp.addEventListener('change', function () {
        const file = this.files[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = e => {
            previewImg.src = e.target.result;
            previewImg.style.display = 'block';
            placeholder.style.display = 'none';
            btnScan.disabled = false;
        };
        reader.readAsDataURL(file);
    });

    btnScan.addEventListener('click', async function () {
        loader.style.display = 'block';
        progressBar.style.width = '0%';
        statusText.textContent = 'Memproses...';
        btnScan.disabled = true;

        try {
            const worker = await Tesseract.createWorker('ind', 1, {
                logger: m => {
                    if (m.status === 'recognizing text') {
                        const pct = Math.round(m.progress * 100);
                        progressBar.style.width = pct + '%';
                        statusText.textContent = 'Memproses... ' + pct + '%';
                    }
                }
            });

            const { data: { text } } = await worker.recognize(previewImg.src);
            await worker.terminate();

            rawText.value = text;
            rawTextBox.style.display = 'block';

            parseKtp(text);

            progressBar.style.width = '100%';
            statusText.textContent = 'Selesai!';

        } catch (err) {
            console.error(err);
            alert("Terjadi kesalahan saat memproses gambar.");
        } finally {
            loader.style.display = 'none';
            btnScan.disabled = false;
        }
    });

    function parseKtp(raw) {
        const lines = raw.split('\n')
            .map(l => l.trim())
            .filter(l => l.length > 1);

        // Untuk tiap baris, pisahkan keyword (sebelum ':') dan nilai (setelah ':')
        // Jika tidak ada ':', seluruh baris dianggap keyword+nilai
        const parsed = lines.map(l => {
            const colonIdx = l.indexOf(':');
            if (colonIdx !== -1) {
                return {
                    keyword: l.substring(0, colonIdx).toUpperCase().replace(/[|\-]/g, ' ').replace(/\s+/g, ' ').trim(),
                    value:   l.substring(colonIdx + 1).trim(),
                };
            }
            return {
                keyword: l.toUpperCase().replace(/[|\-]/g, ' ').replace(/\s+/g, ' ').trim(),
                value:   '',
            };
        });

        // --- NIK: cari 16 digit berurutan di seluruh teks ---
        const fullText = raw.replace(/\s/g, '');
        const nikMatch = fullText.match(/\d{16}/);
        if (nikMatch) setField('res-nik', nikMatch[0]);

        const stopPattern = /^(AGAMA|STATUS|PEKERJAAN|KEWARGANEGARAAN|BERLAKU|JENIS\s*KELAMIN|GOL)/;

        for (let i = 0; i < parsed.length; i++) {
            const { keyword, value } = parsed[i];

            // --- NAMA ---
            if (/^NAMA\b/.test(keyword) && value) {
                setField('res-nama', value);
            }

            // --- TEMPAT / TGL LAHIR ---
            if (/TEMPAT|TGL\s*LAHIR|TANGGAL\s*LAHIR/.test(keyword) && value) {
                const parts = value.split(',');
                if (parts.length >= 2) {
                    setField('res-tempat', parts[0].trim());
                    setField('res-tgl', parts.slice(1).join(',').trim());
                } else {
                    const dateMatch = value.match(/^(.*?)\s*(\d{2}[\s\-]\d{2}[\s\-]\d{4})/);
                    if (dateMatch) {
                        setField('res-tempat', dateMatch[1].trim());
                        setField('res-tgl', dateMatch[2].trim());
                    } else {
                        setField('res-tempat', value);
                    }
                }
            }

            // --- ALAMAT (termasuk RT/RW, Kel, Kec di baris berikutnya) ---
            if (/^ALAMAT\b/.test(keyword)) {
                let parts = [value].filter(Boolean);

                for (let j = i + 1; j < parsed.length; j++) {
                    const nk = parsed[j].keyword;
                    const nv = parsed[j].value;

                    if (stopPattern.test(nk)) break;
                    if (/^(NAMA|NIK|TEMPAT|TGL|TANGGAL)/.test(nk)) break;

                    if (/^RT\s*[\/\\]?\s*RW\b/.test(nk)) {
                        if (nv) parts.push('RT/RW ' + nv);
                    } else if (/^(KEL|KELURAHAN|DESA)\b/.test(nk)) {
                        if (nv) parts.push('Kel. ' + nv);
                    } else if (/^(KEC|KECAMATAN)\b/.test(nk)) {
                        if (nv) parts.push('Kec. ' + nv);
                    } else if (nv) {
                        parts.push(nv);
                    } else if (parsed[j].value === '' && parsed[j].keyword) {
                        // baris tanpa colon — kemungkinan lanjutan alamat
                        const raw_line = lines[j];
                        if (!/^(AGAMA|STATUS|PEKERJAAN|KEWARGANEGARAAN|BERLAKU|JENIS|GOL|RT|KEL|KEC|NAMA|NIK|TEMPAT)/.test(raw_line.toUpperCase())) {
                            parts.push(raw_line.trim());
                        } else {
                            break;
                        }
                    }
                }

                setField('res-alamat', parts.join(', '));
            }
        }
    }

    function setField(id, value) {
        const el = document.getElementById(id);
        if (el && value) el.value = value;
    }
});
</script>
@endsection
