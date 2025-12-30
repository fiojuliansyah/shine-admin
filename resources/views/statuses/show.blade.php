@extends('layouts.main')

@section('content')
<div class="page-wrapper">
    <div class="content">

        <!-- Breadcrumb -->
        <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
            <div class="my-auto mb-2">
                <h2 class="mb-1">{{ $status->name }} List</h2>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item">
                            <a href="index.html"><i class="ti ti-smart-home"></i></a>
                        </li>
                        <li class="breadcrumb-item">Recruitment</li>
                        <li class="breadcrumb-item">Kandidat</li>
                        <li class="breadcrumb-item active">{{ $status->slug }}</li>
                    </ol>
                </nav>
            </div>
            <div class="d-flex my-xl-auto right-content align-items-center flex-wrap">
                @if($status->process_to_offering == 'yes')
                    <div class="mb-2 me-2">
                        <button type="button" class="btn btn-white d-inline-flex align-items-center" data-bs-toggle="modal" data-bs-target="#confirmation">
                            <i class="ti ti-circle-plus me-2"></i>Proses Offering
                        </button>
                    </div>
                @endif
                <div class="mb-2">
                    <button type="button" class="btn btn-primary d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#bulkUpdate">
                        <i class="ti ti-circle-plus me-2"></i>Bulk Update
                    </button>
                </div>
            </div>
        </div>
        <!-- /Breadcrumb -->

        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
                <h5>List Kandidat</h5>
            </div>
            <div class="card-body p-0">
                <div class="custom-datatable-filter table-responsive">
                    <table class="table table-bordered data-table" style="font-size: 12px; table-layout: fixed; width: 100%;">
                        <thead>
                            <tr>
                                <th><input type="checkbox" id="select-all"></th>
                                <th>NIK Karyawan</th>
                                <th>Nama Pelamar</th>
                                <th>Lowongan Pekerjaan</th>
                                <th>Jabatan</th>
                                <th>Progress</th>
                                <th>Resume</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bulk Update Modal -->
<div class="modal fade" id="bulkUpdate" tabindex="-1" aria-labelledby="bulkUpdateLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Bulk Update</h4>
                <button type="button" class="btn-close custom-btn-close" data-bs-dismiss="modal" aria-label="Close">
                    <i class="ti ti-x"></i>
                </button>
            </div>
            <form id="bulk-update-form" action="{{ route('bulk.update.status') }}" method="POST">
                @csrf
                <div class="modal-body pb-0">
                    <div class="mb-3">
                        <label class="form-label">Pilih Tingkat</label>
                        <select class="form-select" name="status_id" id="bulk-status-id">
                            <option disabled selected>Pilih Tingkat</option>
                            @foreach ($statuses as $item)
                                <option value="{{ $item->id }}">{{ $item->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light me-2" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
                <input type="hidden" name="applicant_ids[]" id="applicant-ids">
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="confirmation" tabindex="-1" aria-labelledby="confirmationLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">KONFIRMASI</h4>
                <button type="button" class="btn-close custom-btn-close" data-bs-dismiss="modal" aria-label="Close">
                    <i class="ti ti-x"></i>
                </button>
            </div>
            <div class="modal-body">
                <!-- Confirmation Content -->
                <p>Apakah Anda yakin ingin melanjutkan dengan tindakan ini?</p>
                <p><strong>Detail Tindakan:</strong></p>
                <ul>
                    <li class="mb-2">Pelamar yang akan diproses <strong>wajib diupdate <span style="color: red">EMPLOYEE ID atau NIK KARYAWAN dan ROLE atau JABATAN</span> di Actions->Edit pada page ini sebelum melakukan Offering</strong></li>
                    <li class="mb-2">Pelamar yang akan diproses <strong>akan bisa langsung menjalankan Aplikasi Mobile</strong></li>
                    <li class="mb-2">Pastikan anda Lapor atau konfirmasi untuk <strong>Posting Payroll sebelum Offering / PKWT Digital</strong></li>
                    <li class="mb-2">
                        Siapkan template di menu 
                        <nav>
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item">
                                    <a href="index.html"><i class="ti ti-smart-home"></i></a>
                                </li>
                                <li class="breadcrumb-item">HRM</li>
                                <li class="breadcrumb-item">Digital Letter</li>
                                <li class="breadcrumb-item active">Buat Template</li>
                            </ol>
                        </nav>
                    </li>
                </ul>
                <p class="mt-2">Pastikan semua informasi yang dimasukkan sudah benar sebelum melanjutkan.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light me-2" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#bulkOffering">Lanjutkan</button>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="bulkOffering" tabindex="-1" aria-labelledby="bulkOfferingLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Konfigurasi Offering & PKWT</h4>
                <button type="button" class="btn-close custom-btn-close" data-bs-dismiss="modal" aria-label="Close">
                    <i class="ti ti-x"></i>
                </button>
            </div>
            <form id="bulk-offering-form" action="{{ route('bulk.update.offering') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Pilih Penempatan / Site</label>
                            <select class="form-select" name="site_id" required>
                                <option disabled selected>Pilih Penempatan</option>
                                @foreach ($sites as $site)
                                    <option value="{{ $site->id }}">{{ $site->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Template Surat (PKWT)</label>
                            <select class="form-select" name="letter_id" required>
                                <option disabled selected>Pilih Template</option>
                                @foreach ($letters as $letter) <option value="{{ $letter->id }}">{{ $letter->title }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tanggal Mulai Kontrak</label>
                            <input type="date" name="start_date" class="form-control" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tanggal Berakhir Kontrak</label>
                            <input type="date" name="end_date" class="form-control" required>
                        </div>

                        <div class="col-md-12 mb-3">
                            <label class="form-label">Nomor Surat (Optional)</label>
                            <input type="text" name="letter_number" class="form-control" placeholder="Contoh: 001/HRD/PKWT/2025">
                            <small class="text-muted italic">*Kosongkan jika ingin generate otomatis</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light me-2" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Proses & Generate Letter</button>
                </div>
                <div id="offering-applicant-container"></div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('css')
<link rel="stylesheet" href="/admin/assets/css/dataTables.bootstrap5.min.css">
@endpush

@push('js')
<script src="/admin/assets/js/jquery.dataTables.min.js"></script>
<script src="/admin/assets/js/dataTables.bootstrap5.min.js"></script>
<script type="text/javascript">
$(function () {
    var selectedIds = [];

    // Initialize DataTable
    var table = $('.data-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('statuses.show', $status->slug) }}",
        columns: [
            { data: 'checkbox', name: 'checkbox', orderable: false, searchable: false },
            { data: 'employee', name: 'employee' },
            { data: 'name', name: 'name' },
            { data: 'career', name: 'career' },
            { data: 'role', name: 'role' },
            { data: 'progress', name: 'progress' },
            { data: 'resume', name: 'resume' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        scrollX: true,
        drawCallback: function() {
            $('.data-table input[type="checkbox"]').each(function() {
                if (selectedIds.includes($(this).val())) {
                    $(this).prop('checked', true);
                }
            });
            toggleBulkUpdateButton();
        }
    });

    // Select/Deselect all rows
    $('#select-all').on('click', function () {
        var rows = table.rows({ 'search': 'applied' }).nodes();
        var isChecked = this.checked;
        
        $('input[type="checkbox"]', rows).prop('checked', isChecked);
        
        // Update selectedIds array based on whether the checkbox is checked or unchecked
        if (isChecked) {
            // Add all checked IDs to selectedIds
            $('input[type="checkbox"]:checked', rows).each(function() {
                if (!selectedIds.includes($(this).val())) {
                    selectedIds.push($(this).val());
                }
            });
        } else {
            // Clear selectedIds if unchecked
            selectedIds = [];
        }
        toggleBulkUpdateButton();
    });

    // Handle individual checkbox changes
    $('.data-table').on('change', 'input[type="checkbox"]', function () {
        toggleBulkUpdateButton();

        var id = $(this).val();
        if ($(this).prop('checked')) {
            selectedIds.push(id);
        } else {
            selectedIds = selectedIds.filter(function(value) {
                return value !== id;
            });
        }
    });

    // Toggle button state based on selected checkboxes
    function toggleBulkUpdateButton() {
        var selected = $('.data-table input[type="checkbox"]:checked').length;
        if (selected > 0) {
            $('#bulk-update-btn').prop('disabled', false);
        } else {
            $('#bulk-update-btn').prop('disabled', true);
        }
    }

    // Handle form submission for bulk offering
    $('#bulk-offering-form').submit(function(e) {
        e.preventDefault();

        if (selectedIds.length === 0) {
            alert('Mohon pilih setidaknya satu pelamar');
            return;
        }

        // Bersihkan container hidden input sebelum append
        $('#offering-applicant-container').empty();

        $.each(selectedIds, function(index, id) {
            $('<input>').attr({
                type: 'hidden',
                name: 'applicant_ids[]',
                value: id
            }).appendTo('#offering-applicant-container');
        });

        this.submit();
    });
});
</script>
@endpush

