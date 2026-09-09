@extends('admin.layouts.main')

@section('content')
<div class="page-wrapper">
    <div class="content">
        <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
            <div class="my-auto mb-2">
                <h2 class="mb-1">Import Data Pegawai</h2>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="ti ti-smart-home"></i></a></li>
                        <li class="breadcrumb-item">Data Pegawai</li>
                        <li class="breadcrumb-item active">Import</li>
                    </ol>
                </nav>
            </div>
            <a href="{{ route('employees.import.template') }}" class="btn btn-outline-primary">
                <i class="ti ti-file-download me-1"></i>Download Template
            </a>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
        @endif

        <div class="row">
            <div class="col-xl-7">
                <div class="card border-0 shadow-sm">
                    <div class="card-header"><h5 class="mb-0">Upload File Pegawai</h5></div>
                    <form id="employeeImportForm" action="{{ route('employees.import') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="card-body">
                            <label for="file" class="form-label fw-bold">File Excel/CSV</label>
                            <input type="file" id="file" name="file" class="form-control" accept=".xlsx,.xls,.csv" required>
                            <div class="form-text">Maksimal 10 MB. Gunakan template agar susunan kolom sesuai.</div>
                            <div id="importProgress" class="mt-4 d-none">
                                <div class="d-flex justify-content-between mb-1"><span id="progressLabel">Menyiapkan import...</span><span id="progressPercent">0%</span></div>
                                <div class="progress" style="height: 22px"><div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated" style="width: 0%">0%</div></div>
                                <div id="progressDetail" class="small text-muted mt-2"></div>
                            </div>
                            <div id="importError" class="alert alert-danger d-none mt-3 mb-0"></div>
                        </div>
                        <div class="card-footer text-end">
                            <button type="submit" id="importButton" class="btn btn-primary"><i class="ti ti-file-upload me-1"></i>Import Pegawai</button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="col-xl-5">
                <div class="card border-0 shadow-sm">
                    <div class="card-header"><h5 class="mb-0">Ketentuan Import</h5></div>
                    <div class="card-body">
                        <ul class="mb-0 ps-3">
                            <li class="mb-2">Wajib: PT, NIK Karyawan, Nama, Email, Jabatan, dan Project.</li>
                            <li class="mb-2">PT menggunakan kode perusahaan dan Jabatan menggunakan kode jabatan.</li>
                            <li class="mb-2">Project akan dibuat otomatis jika belum ada, area diisi dari kolom Area/Wilayah.</li>
                            <li class="mb-2">Jabatan akan dibuat otomatis jika belum ada.</li>
                            <li class="mb-2">Status kosong dianggap <strong>ACTIVE</strong>; isi <strong>RESIGN</strong> untuk pegawai resign.</li>
                            <li class="mb-2">Tanggal menggunakan format <strong>dd-mm-yyyy</strong>.</li>
                            <li>Manager diisi bebas (nama/NIK/email), disimpan pada profil pegawai.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('employeeImportForm');
    const fileInput = document.getElementById('file');
    const button = document.getElementById('importButton');
    const progress = document.getElementById('importProgress');
    const bar = document.getElementById('progressBar');
    const percent = document.getElementById('progressPercent');
    const label = document.getElementById('progressLabel');
    const detail = document.getElementById('progressDetail');
    const errorBox = document.getElementById('importError');
    const csrf = document.querySelector('input[name="_token"]').value;

    const routes = {
        upload: @json(route('employees.import')),
        validate: @json(route('employees.import.validate')),
        persist: @json(route('employees.import.persist')),
        storeResult: @json(route('employees.import.store-result')),
    };

    function setBar(pct) {
        pct = Math.max(0, Math.min(100, Math.round(pct)));
        bar.style.width = pct + '%';
        bar.textContent = pct + '%';
        percent.textContent = pct + '%';
    }

    function fail(msg) {
        errorBox.textContent = msg;
        errorBox.classList.remove('d-none');
        button.disabled = false;
        bar.classList.add('bg-danger');
    }

    async function post(url, body) {
        const res = await fetch(url, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
            body: body,
        });
        const data = await res.json().catch(() => ({}));
        if (!res.ok) {
            throw new Error(data.message || 'Terjadi kesalahan saat memproses import.');
        }
        return data;
    }

    async function runValidate(token, total) {
        let offset = 0;
        label.textContent = 'Memeriksa data...';
        while (true) {
            const fd = new FormData();
            fd.append('token', token);
            fd.append('offset', offset);
            const data = await post(routes.validate, fd);
            offset = data.offset;
            setBar(total ? (offset / total) * 50 : 50);
            detail.textContent = `Diperiksa ${offset}/${total} baris • ${data.errorCount} error`;
            if (data.done) return data;
        }
    }

    async function runPersist(token, totalValid) {
        let offset = 0;
        label.textContent = 'Menyimpan data...';
        let last = {};
        while (true) {
            const fd = new FormData();
            fd.append('token', token);
            fd.append('offset', offset);
            const data = await post(routes.persist, fd);
            offset = data.offset;
            last = data;
            setBar(50 + (totalValid ? (offset / totalValid) * 50 : 50));
            detail.textContent = `Disimpan ${offset}/${totalValid} • ${data.created} baru, ${data.updated} update`;
            if (data.done) return last;
        }
    }

    form.addEventListener('submit', async function (e) {
        e.preventDefault();
        if (!fileInput.files.length) return;

        button.disabled = true;
        errorBox.classList.add('d-none');
        bar.classList.remove('bg-danger');
        progress.classList.remove('d-none');
        setBar(0);
        label.textContent = 'Mengunggah file...';
        detail.textContent = '';

        try {
            const fd = new FormData();
            fd.append('file', fileInput.files[0]);
            const up = await post(routes.upload, fd);

            const validate = await runValidate(up.token, up.total);

            if (validate.errorCount > 0) {
                label.textContent = 'Ditemukan kesalahan';
                detail.textContent = `${validate.errorCount} error ditemukan. Mengalihkan ke laporan...`;
                const fd2 = new FormData();
                fd2.append('token', up.token);
                const r = await post(routes.storeResult, fd2);
                window.location.href = r.redirect;
                return;
            }

            const result = await runPersist(up.token, validate.validRows);
            setBar(100);
            bar.classList.add('bg-success');
            label.textContent = 'Import selesai';
            let msg = `Berhasil: ${result.created} pegawai baru, ${result.updated} diperbarui`;
            if (result.sitesCreated) msg += `, ${result.sitesCreated} site baru`;
            if (result.rolesCreated) msg += `, ${result.rolesCreated} jabatan baru`;
            detail.textContent = msg + '.';
        } catch (err) {
            fail(err.message);
        }
    });
});
</script>
@endpush
