@extends('admin.layouts.main')

@section('content')
<div class="page-wrapper">
    <div class="content">

        <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-4">
            <div class="my-auto mb-2">
                <h2 class="mb-1">Konfigurasi NIK Karyawan</h2>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="#"><i class="ti ti-smart-home"></i></a></li>
                        <li class="breadcrumb-item">Master Data</li>
                        <li class="breadcrumb-item active">Konfigurasi NIK</li>
                    </ol>
                </nav>
            </div>
            <div class="d-flex my-xl-auto right-content align-items-center">
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambah">
                    <i class="ti ti-plus me-1"></i>Tambah Konfigurasi
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
            <div class="card-header bg-white py-3">
                <h5 class="card-title mb-0 text-primary">Daftar Konfigurasi NIK</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Nama</th>
                                <th>Perusahaan</th>
                                <th>Format</th>
                                <th>Prefix</th>
                                <th>Digit</th>
                                <th>Start</th>
                                <th>Current</th>
                                <th>Default</th>
                                <th>Preview</th>
                                <th>Keterangan</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($configs as $i => $config)
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td><strong>{{ $config->name }}</strong></td>
                                    <td>{{ $config->company->name ?? '-' }} <small class="text-muted">({{ $config->company->unique_id ?? '-' }})</small></td>
                                    <td><code>{{ $config->format }}</code></td>
                                    <td>{{ $config->prefix ?? '-' }}</td>
                                    <td>{{ $config->padding }}</td>
                                    <td>{{ $config->start_number }}</td>
                                    <td>{{ $config->current_number }}</td>
                                    <td>
                                        @if ($config->is_default)
                                            <span class="badge bg-success">Default</span>
                                        @else
                                            <span class="badge bg-light text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-primary-subtle text-primary font-monospace">
                                            {{ $config->previewNik() }}
                                        </span>
                                    </td>
                                    <td>{{ $config->description ?? '-' }}</td>
                                    <td class="text-end">
                                        <button type="button" class="btn btn-sm btn-outline-primary btn-edit-config"
                                            data-id="{{ $config->id }}"
                                            data-company-id="{{ $config->company_id }}"
                                            data-name="{{ $config->name }}"
                                            data-format="{{ $config->format }}"
                                            data-prefix="{{ $config->prefix }}"
                                            data-padding="{{ $config->padding }}"
                                            data-start-number="{{ $config->start_number }}"
                                            data-current-number="{{ $config->current_number ?? 0 }}"
                                            data-is-default="{{ $config->is_default ? 1 : 0 }}"
                                            data-description="{{ $config->description }}">
                                            <i class="ti ti-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger btn-delete-config"
                                            data-id="{{ $config->id }}"
                                            data-name="{{ $config->name }}">
                                            <i class="ti ti-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="12" class="text-center text-muted py-4">Belum ada konfigurasi NIK.</td>
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
                    @foreach ([
                        ['{no}', 'Nomor urut (sesuai digit)', '00007'],
                        ['{prefix}', 'Prefix tetap dari field prefix', 'EMP'],
                        ['{kode_jabatan}', 'Kode jabatan (role->code)', 'SEC'],
                        ['{kode_company}', 'unique_id dari Company', 'CK'],
                        ['{tanggal_join}', 'Tanggal join (dd)', '15'],
                        ['{bulan_join}', 'Bulan join (mm)', '05'],
                        ['{tahun_join}', 'Tahun join (YYYY)', '2026'],
                        ['{tahun_join_pendek}', 'Tahun join (yy)', '26'],
                    ] as $token)
                        <div class="col-md-4 col-lg-3">
                            <div class="d-flex align-items-center gap-2 p-2 border rounded bg-light">
                                <code class="text-primary token-copy" role="button" data-token="{{ $token[0] }}">{{ $token[0] }}</code>
                                <div>
                                    <div style="font-size:12px;">{{ $token[1] }}</div>
                                    <div style="font-size:11px;color:#888;">Contoh: <strong>{{ $token[2] }}</strong></div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <small class="text-muted d-block mt-2">Klik token untuk menyalin ke clipboard.</small>
            </div>
        </div>
    </div>

    {{-- Modal Tambah --}}
    <div class="modal fade" id="modalTambah" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Tambah Konfigurasi NIK</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('employee-nik-configs.store') }}" method="POST" id="formTambah">
                    @csrf
                    <div class="modal-body">
                        @include('admin.employee_nik_configs.partials.form', ['companies' => $companies, 'isEdit' => false])
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
                    <h5 class="modal-title">Edit Konfigurasi NIK</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="" method="POST" id="formEdit">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        @include('admin.employee_nik_configs.partials.form', ['companies' => $companies, 'isEdit' => true])
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-warning"><i class="ti ti-device-floppy me-1"></i>Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Hapus --}}
    <div class="modal fade" id="modalHapus" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Hapus Konfigurasi</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="" method="POST" id="formHapus">
                    @csrf
                    @method('DELETE')
                    <div class="modal-body">
                        Hapus konfigurasi <strong id="hapusNama"></strong>?
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger"><i class="ti ti-trash me-1"></i>Hapus</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
$(document).ready(function () {
    $('.modal').on('shown.bs.modal', function () {
        $(this).find('.select2-modal').select2({ dropdownParent: $(this) });
    });

    function bindLivePreview($modal) {
        function update() {
            var $form = $modal.find('form');
            var data = {
                _token: $('meta[name="csrf-token"]').attr('content') || $form.find('input[name="_token"]').val(),
                company_id: $form.find('[name="company_id"]').val(),
                format: $form.find('[name="format"]').val(),
                prefix: $form.find('[name="prefix"]').val(),
                padding: $form.find('[name="padding"]').val(),
                start_number: $form.find('[name="start_number"]').val(),
            };
            if (!data.company_id || !data.format) {
                $form.find('.nik-preview').text('-');
                return;
            }
            $.post('{{ route("employee-nik-configs.preview") }}', data)
                .done(function (resp) {
                    $form.find('.nik-preview').text(resp.preview || '-');
                })
                .fail(function () {
                    $form.find('.nik-preview').text('Format tidak valid');
                });
        }
        $modal.find('[name="company_id"], [name="format"], [name="prefix"], [name="padding"], [name="start_number"]')
            .on('input change', update);
        $modal.on('shown.bs.modal', update);
    }

    bindLivePreview($('#modalTambah'));
    bindLivePreview($('#modalEdit'));

    $('.token-copy').on('click', function () {
        var token = $(this).data('token');
        var $modal = $('.modal.show');
        if ($modal.length) {
            var $input = $modal.find('[name="format"]');
            $input.val(($input.val() || '') + token).trigger('input');
        } else {
            navigator.clipboard && navigator.clipboard.writeText(token);
        }
    });

    $('.btn-edit-config').on('click', function () {
        var $btn = $(this);
        var id = $btn.data('id');
        var $form = $('#formEdit');
        $form.attr('action', '/manage/employee-nik-configs/' + id);
        $form.find('[name="company_id"]').val($btn.data('company-id')).trigger('change');
        $form.find('[name="name"]').val($btn.data('name'));
        $form.find('[name="format"]').val($btn.data('format'));
        $form.find('[name="prefix"]').val($btn.data('prefix'));
        $form.find('[name="padding"]').val($btn.data('padding'));
        $form.find('[name="start_number"]').val($btn.data('start-number'));
        $form.find('[name="current_number"]').val($btn.data('current-number'));
        $form.find('[name="description"]').val($btn.data('description'));
        $form.find('[name="is_default"]').prop('checked', $btn.data('is-default') == 1);
        $('#modalEdit').modal('show');
    });

    $('.btn-delete-config').on('click', function () {
        var $btn = $(this);
        $('#formHapus').attr('action', '/manage/employee-nik-configs/' + $btn.data('id'));
        $('#hapusNama').text($btn.data('name'));
        $('#modalHapus').modal('show');
    });
});
</script>
@endpush
