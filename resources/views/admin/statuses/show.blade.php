@extends('admin.layouts.main')

@section('content')
<div class="page-wrapper">
    <div class="content">

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
                @if($status->is_applicant_document == 'yes')
                    <div class="mb-2 me-2">
                        <button type="button" class="btn btn-white d-inline-flex align-items-center" data-bs-toggle="modal" data-bs-target="#applicantDocument">
                            <i class="ti ti-circle-plus me-2"></i>Update Dokumen Pelamar
                        </button>
                    </div>
                @endif
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

<div class="modal fade" id="bulkUpdate" tabindex="-1" aria-labelledby="bulkUpdateLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Bulk Update</h4>
                <button type="button" class="btn-close custom-btn-close" data-bs-dismiss="modal" aria-label="Close">
                    <i class="ti ti-x"></i>
                </button>
            </div>
            <form id="form-bulk-update-status" action="{{ route('bulk.update.status') }}" method="POST">
                @csrf
                <div class="modal-body pb-0">
                    <div class="mb-3">
                        <label class="form-label">Pilih Tingkat</label>
                        <select class="form-select" name="status_id" required>
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
                <div class="applicant-ids-container"></div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="applicantDocument" tabindex="-1" aria-labelledby="applicantDocumentLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Konfigurasi Dokumen Digital</h4>
                <button type="button" class="btn-close custom-btn-close" data-bs-dismiss="modal" aria-label="Close">
                    <i class="ti ti-x"></i>
                </button>
            </div>
            <form id="form-applicant-document" action="{{ route('bulk.update.applicant-document') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Pilih Perusahaan</label>
                            <select class="form-select select-company" data-target="#site-applicant-document" required>
                                <option disabled selected>Pilih Perusahaan</option>
                                @foreach ($companies as $company)
                                    <option value="{{ $company->id }}">{{ $company->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Pilih Penempatan / Site</label>
                            <select class="form-select" id="site-applicant-document" name="site_id" required>
                                <option disabled selected>Pilih Perusahaan Dahulu</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Template Surat</label>
                            <select class="form-select" name="letter_id" required>
                                <option disabled selected>Pilih Template</option>
                                @foreach ($letters as $letter) 
                                    <option value="{{ $letter->id }}">{{ $letter->title }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tanggal Kontrak</label>
                            <input type="date" name="start_date" class="form-control" required>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Nomor Surat (Optional)</label>
                            <input type="text" name="letter_number" class="form-control" placeholder="001/HRD/PKWT/2025">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light me-2" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Proses & Generate Letter</button>
                </div>
                <div class="applicant-ids-container"></div>
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
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#bulkOffering">Lanjutkan</button>
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
            <form id="form-bulk-offering" action="{{ route('bulk.update.offering') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Pilih Perusahaan</label>
                            <select class="form-select select-company" data-target="#site-bulk-offering" required>
                                <option disabled selected>Pilih Perusahaan</option>
                                @foreach ($companies as $company)
                                    <option value="{{ $company->id }}">{{ $company->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Pilih Penempatan / Site</label>
                            <select class="form-select" id="site-bulk-offering" name="site_id" required>
                                <option disabled selected>Pilih Perusahaan Dahulu</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Template Surat</label>
                            <select class="form-select select-letter" name="letter_id" required>
                                <option disabled selected>Pilih Penempatan Dahulu</option>
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
                            <input type="text" name="letter_number" class="form-control" placeholder="001/HRD/PKWT/2025">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light me-2" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Proses & Generate Letter</button>
                </div>
                <div class="applicant-ids-container"></div>
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
        scrollX: true
    });

    $('.select-company').on('change', function() {
        var companyId = $(this).val();
        var $form = $(this).closest('form');
        var $siteDropdown = $form.find('select[name="site_id"]');
        var $letterDropdown = $form.find('select[name="letter_id"]');

        $siteDropdown.empty().append('<option disabled selected>Loading Site...</option>');
        $letterDropdown.empty().append('<option disabled selected>Pilih Penempatan Dahulu</option>');

        if (companyId) {
            $.ajax({
                url: '/manage/get-sites-by-company/' + companyId,
                type: 'GET',
                success: function(data) {
                    $siteDropdown.empty().append('<option disabled selected>Pilih Penempatan</option>');
                    $.each(data, function(key, site) {
                        $siteDropdown.append('<option value="' + site.id + '">' + site.name + '</option>');
                    });
                }
            });
        }
    });

    $('select[name="site_id"]').on('change', function() {
        var siteId = $(this).val();
        var $form = $(this).closest('form');
        var $letterDropdown = $form.find('select[name="letter_id"]');

        $letterDropdown.empty().append('<option disabled selected>Loading Template...</option>');

        if (siteId) {
            $.ajax({
                url: '/manage/get-letters-by-site/' + siteId,
                type: 'GET',
                success: function(data) {
                    $letterDropdown.empty().append('<option disabled selected>Pilih Template</option>');
                    $.each(data, function(key, letter) {
                        $letterDropdown.append('<option value="' + letter.id + '">' + letter.title + '</option>');
                    });
                }
            });
        }
    });

    $('#select-all').on('click', function () {
        $('.applicant-checkbox').prop('checked', this.checked);
    });

    $('form').on('submit', function(e) {
        var form = $(this);
        var formId = form.attr('id');
        
        if (['form-bulk-update-status', 'form-applicant-document', 'form-bulk-offering'].includes(formId)) {
            var selectedIds = [];
            $('.applicant-checkbox:checked').each(function() {
                selectedIds.push($(this).val());
            });

            if (selectedIds.length === 0) {
                e.preventDefault();
                alert('Mohon pilih setidaknya satu kandidat dari tabel.');
                return false;
            }

            var container = form.find('.applicant-ids-container');
            container.empty();
            $.each(selectedIds, function(index, id) {
                $('<input>').attr({
                    type: 'hidden',
                    name: 'applicant_ids[]',
                    value: id
                }).appendTo(container);
            });
        }
    });
});
</script>
@endpush