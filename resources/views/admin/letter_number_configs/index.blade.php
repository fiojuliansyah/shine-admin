@extends('admin.layouts.main')

@section('content')
<div class="page-wrapper">
    <div class="content">

        <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-4">
            <div class="my-auto mb-2">
                <h2 class="mb-1">Konfigurasi Nomor Surat</h2>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="#"><i class="ti ti-smart-home"></i></a></li>
                        <li class="breadcrumb-item">E-Recruitment</li>
                        <li class="breadcrumb-item active">Konfigurasi Nomor Surat</li>
                    </ol>
                </nav>
            </div>
            <div class="d-flex my-xl-auto right-content align-items-center">
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambah">
                    <i class="ti ti-plus me-1"></i>Tambah Konfigurasi
                </button>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h5 class="card-title mb-0 text-primary">Daftar Konfigurasi Nomor Surat</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Nama Konfigurasi</th>
                                <th>Format</th>
                                <th>Prefix</th>
                                <th>Digit Urut</th>
                                <th>Preview</th>
                                <th>Keterangan</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($configs as $i => $config)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td><strong>{{ $config->name }}</strong></td>
                                <td><code>{{ $config->format }}</code></td>
                                <td>{{ $config->prefix ?? '-' }}</td>
                                <td>{{ $config->padding }}</td>
                                <td>
                                    <span class="badge bg-primary-subtle text-primary">
                                        {{ $config->generateNumber(1) }}
                                    </span>
                                </td>
                                <td>{{ $config->description ?? '-' }}</td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-outline-primary"
                                        onclick="editConfig({{ $config->id }}, '{{ addslashes($config->name) }}', '{{ addslashes($config->format) }}', '{{ addslashes($config->prefix ?? '') }}', {{ $config->padding }}, '{{ addslashes($config->description ?? '') }}')">
                                        <i class="ti ti-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-danger"
                                        onclick="deleteConfig({{ $config->id }}, '{{ addslashes($config->name) }}')">
                                        <i class="ti ti-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">Belum ada konfigurasi nomor surat.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0 mt-4">
            <div class="card-header bg-white py-3">
                <h6 class="card-title mb-0 text-muted">Token yang Tersedia</h6>
            </div>
            <div class="card-body">
                <div class="row g-2">
                    @foreach([
                        ['{no}', 'Nomor urut (sesuai digit)', '001'],
                        ['{romawi}', 'Bulan romawi', 'V'],
                        ['{tahun}', 'Tahun 4 digit', '2026'],
                        ['{tahun_pendek}', 'Tahun 2 digit', '26'],
                        ['{bulan}', 'Bulan angka 2 digit', '05'],
                        ['{kode_site}', 'Kode/unique_id site', 'JKT'],
                        ['{kode_tipe}', 'Kode tipe surat', 'SPK'],
                        ['{kode_company}', 'Kode perusahaan', 'CK'],
                        ['{kode_jabatan}', 'Kode jabatan karyawan', 'SEC'],
                        ['{prefix}', 'Prefix tetap dari field prefix', 'SPK'],
                    ] as $token)
                    <div class="col-md-4 col-lg-3">
                        <div class="d-flex align-items-center gap-2 p-2 border rounded bg-light">
                            <code class="text-primary">{{ $token[0] }}</code>
                            <div>
                                <div style="font-size:12px;">{{ $token[1] }}</div>
                                <div style="font-size:11px;color:#888;">Contoh: <strong>{{ $token[2] }}</strong></div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

    </div>
</div>

{{-- MODAL TAMBAH --}}
<div class="modal fade" id="modalTambah" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary">
                <h5 class="modal-title text-white">Tambah Konfigurasi Nomor Surat</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('letter-number-configs.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    @include('admin.letter_number_configs.partials.form')
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL EDIT --}}
<div class="modal fade" id="modalEdit" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-warning">
                <h5 class="modal-title">Edit Konfigurasi Nomor Surat</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body p-4">
                    @include('admin.letter_number_configs.partials.form', ['isEdit' => true])
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL DELETE --}}
<div class="modal fade" id="modalDelete" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-danger">
                <h5 class="modal-title text-white">Hapus Konfigurasi</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="deleteForm" method="POST">
                @csrf
                @method('DELETE')
                <div class="modal-body">
                    <p>Apakah Anda yakin ingin menghapus konfigurasi <strong id="deleteConfigName"></strong>?</p>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">Hapus</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
    function editConfig(id, name, format, prefix, padding, description) {
        const form = document.getElementById('editForm');
        form.action = '/manage/letter-number-configs/' + id;
        form.querySelector('[name="name"]').value = name;
        form.querySelector('[name="format"]').value = format;
        form.querySelector('[name="prefix"]').value = prefix;
        form.querySelector('[name="padding"]').value = padding;
        form.querySelector('[name="description"]').value = description;
        updatePreview('editPreview', format, prefix, padding);
        new bootstrap.Modal(document.getElementById('modalEdit')).show();
    }

    function deleteConfig(id, name) {
        document.getElementById('deleteForm').action = '/manage/letter-number-configs/' + id;
        document.getElementById('deleteConfigName').innerText = name;
        new bootstrap.Modal(document.getElementById('modalDelete')).show();
    }

    function updatePreview(previewId, format, prefix, padding) {
        format = format || document.querySelector('[name="format"]').value;
        prefix = prefix !== undefined ? prefix : document.querySelector('[name="prefix"]').value;
        padding = padding || parseInt(document.querySelector('[name="padding"]').value) || 3;
        const no = String(1).padStart(padding, '0');
        const now = new Date();
        const romawi = ['I','II','III','IV','V','VI','VII','VIII','IX','X','XI','XII'][now.getMonth()];
        const tahun = now.getFullYear();
        const tahun_pendek = String(tahun).slice(-2);
        const bulan = String(now.getMonth() + 1).padStart(2, '0');
        const preview = format
            .replace('{no}', no).replace('{romawi}', romawi)
            .replace('{tahun}', tahun).replace('{tahun_pendek}', tahun_pendek)
            .replace('{bulan}', bulan).replace('{kode_site}', 'SITE')
            .replace('{kode_tipe}', 'TIPE').replace('{kode_company}', 'COMP')
            .replace('{kode_jabatan}', 'JAB').replace('{prefix}', prefix);
        const el = document.getElementById(previewId);
        if (el) el.innerText = preview;
    }

    document.querySelectorAll('[name="format"],[name="prefix"],[name="padding"]').forEach(el => {
        el.addEventListener('input', function() {
            const form = this.closest('form');
            const previewId = form.id === 'editForm' ? 'editPreview' : 'addPreview';
            updatePreview(previewId,
                form.querySelector('[name="format"]').value,
                form.querySelector('[name="prefix"]').value,
                parseInt(form.querySelector('[name="padding"]').value)
            );
        });
    });

    updatePreview('addPreview', '{no}/{kode_tipe}/{romawi}/{tahun}', '', 3);
</script>
@endpush
