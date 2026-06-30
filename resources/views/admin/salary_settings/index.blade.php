@extends('admin.layouts.main')

@php
    $components = [
        'gaji_pokok'         => 'Gaji Pokok',
        'tunj_jabatan'       => 'Tunjangan Jabatan',
        'tunj_kehadiran'     => 'Tunjangan Kehadiran',
        'tunj_komunikasi'    => 'Tunjangan Komunikasi',
        'tunj_makan'         => 'Tunjangan Makan',
        'tunj_transport'     => 'Tunjangan Transport',
        'tunj_lembur_tetap'  => 'Tunjangan Lembur Tetap',
        'tunj_other_non_fix' => 'Tunjangan Other Non Fix',
    ];
@endphp

@section('content')
<div class="page-wrapper">
    <div class="content">

        <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-4">
            <div class="my-auto mb-2">
                <h2 class="mb-1">Pengaturan Gaji</h2>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="#"><i class="ti ti-smart-home"></i></a></li>
                        <li class="breadcrumb-item">Finance</li>
                        <li class="breadcrumb-item active">Pengaturan Gaji</li>
                    </ol>
                </nav>
            </div>
            <div class="d-flex my-xl-auto right-content align-items-center">
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambah">
                    <i class="ti ti-plus me-1"></i>Tambah Pengaturan Gaji
                </button>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between flex-wrap row-gap-2">
                <h5 class="card-title mb-0 text-primary">Daftar Pengaturan Gaji Karyawan</h5>
                <form method="GET" class="d-flex align-items-center gap-2">
                    <select name="site_id" class="form-select form-select-sm" style="width:180px;" onchange="this.form.submit()">
                        <option value="">Semua Site</option>
                        @foreach ($sites as $site)
                            <option value="{{ $site->id }}" {{ request('site_id') == $site->id ? 'selected' : '' }}>{{ $site->name }}</option>
                        @endforeach
                    </select>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-sm" placeholder="Cari nama / NIK..." style="width:200px;">
                    <button class="btn btn-sm btn-outline-primary"><i class="ti ti-search"></i></button>
                </form>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Karyawan</th>
                                <th>Site</th>
                                @foreach ($components as $label)
                                    <th class="text-end">{{ $label }}</th>
                                @endforeach
                                <th class="text-end">Total</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($settings as $i => $setting)
                                @php
                                    $total = collect(array_keys($components))->sum(fn ($k) => (float) $setting->$k);
                                @endphp
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td>
                                        <strong>{{ $setting->user->name ?? '-' }}</strong>
                                        <div class="text-muted small">{{ $setting->user->employee_nik ?? '-' }}</div>
                                    </td>
                                    <td>{{ $setting->user->site->name ?? '-' }}</td>
                                    @foreach (array_keys($components) as $field)
                                        <td class="text-end">Rp {{ number_format((float) $setting->$field, 0, ',', '.') }}</td>
                                    @endforeach
                                    <td class="text-end fw-bold text-primary">Rp {{ number_format($total, 0, ',', '.') }}</td>
                                    <td class="text-end text-nowrap">
                                        <button type="button" class="btn btn-sm btn-outline-primary btn-edit-salary"
                                            data-id="{{ $setting->id }}"
                                            data-user-id="{{ $setting->user_id }}"
                                            data-user-name="{{ $setting->user->name ?? '-' }}"
                                            @foreach (array_keys($components) as $field)
                                                data-{{ str_replace('_', '-', $field) }}="{{ (float) $setting->$field }}"
                                            @endforeach
                                        >
                                            <i class="ti ti-edit"></i>
                                        </button>
                                        <form action="{{ route('salary-settings.destroy', $setting->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus pengaturan gaji {{ $setting->user->name ?? '' }}?');">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger"><i class="ti ti-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ count($components) + 5 }}" class="text-center text-muted py-4">Belum ada pengaturan gaji.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0 mt-4">
            <div class="card-header bg-white py-3">
                <h6 class="card-title mb-0 text-muted">Variabel Template yang Tersedia</h6>
            </div>
            <div class="card-body">
                <div class="row g-2">
                    @foreach ([
                        ['[gaji_pokok]', 'Gaji Pokok'],
                        ['[tunj_jabatan]', 'Tunjangan Jabatan'],
                        ['[tunj_kehadiran]', 'Tunjangan Kehadiran'],
                        ['[tunj_komunikasi]', 'Tunjangan Komunikasi'],
                        ['[tunj_makan]', 'Tunjangan Makan'],
                        ['[tunj_transport]', 'Tunjangan Transport'],
                        ['[tunj_lembur_tetap]', 'Tunjangan Lembur Tetap'],
                        ['[tunj_other_non_fix]', 'Tunjangan Other Non Fix'],
                    ] as $token)
                        <div class="col-md-4 col-lg-3">
                            <div class="d-flex align-items-center gap-2 p-2 border rounded bg-light">
                                <code class="text-primary">{{ $token[0] }}</code>
                                <div style="font-size:12px;">{{ $token[1] }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <small class="text-muted d-block mt-2">Gunakan variabel ini di template surat. Nilainya otomatis diformat Rupiah (mis. Rp 1.500.000).</small>
            </div>
        </div>
    </div>

    {{-- Modal Tambah --}}
    <div class="modal fade" id="modalTambah" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Tambah Pengaturan Gaji</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('salary-settings.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Karyawan</label>
                            <select name="user_id" class="form-select" required>
                                <option value="" disabled selected>Pilih Karyawan</option>
                                @foreach ($employees as $emp)
                                    <option value="{{ $emp->id }}">{{ $emp->name }} ({{ $emp->employee_nik ?? '-' }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="row g-3">
                            @foreach ($components as $field => $label)
                                <div class="col-md-6">
                                    <label class="form-label">{{ $label }}</label>
                                    <div class="input-group">
                                        <span class="input-group-text">Rp</span>
                                        <input type="number" step="1" min="0" name="{{ $field }}" class="form-control" value="0">
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary"><i class="ti ti-device-floppy me-1"></i>Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Edit --}}
    <div class="modal fade" id="modalEdit" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title">Edit Pengaturan Gaji <span id="editUserName" class="fw-normal"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="" method="POST" id="formEdit">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="user_id" id="edit_user_id">
                    <div class="modal-body">
                        <div class="row g-3">
                            @foreach ($components as $field => $label)
                                <div class="col-md-6">
                                    <label class="form-label">{{ $label }}</label>
                                    <div class="input-group">
                                        <span class="input-group-text">Rp</span>
                                        <input type="number" step="1" min="0" name="{{ $field }}" id="edit_{{ $field }}" class="form-control" value="0">
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-warning"><i class="ti ti-device-floppy me-1"></i>Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
    document.querySelectorAll('.btn-edit-salary').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const id = btn.dataset.id;
            const form = document.getElementById('formEdit');
            form.action = "{{ url('manage/salary-settings') }}/" + id;
            document.getElementById('edit_user_id').value = btn.dataset.userId;
            document.getElementById('editUserName').textContent = '- ' + btn.dataset.userName;

            const fields = ['gaji_pokok','tunj_jabatan','tunj_kehadiran','tunj_komunikasi','tunj_makan','tunj_transport','tunj_lembur_tetap','tunj_other_non_fix'];
            fields.forEach(function (f) {
                const key = f.replace(/_/g, '-');
                const input = document.getElementById('edit_' + f);
                if (input) input.value = btn.dataset[toCamel(key)] ?? 0;
            });

            new bootstrap.Modal(document.getElementById('modalEdit')).show();
        });
    });

    function toCamel(s) {
        return s.replace(/-([a-z])/g, function (m, c) { return c.toUpperCase(); });
    }
</script>
@endpush
