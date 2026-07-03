@extends('admin.layouts.main')

@section('content')
<div class="page-wrapper">
    <div class="content">

        <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
            <div class="my-auto mb-2">
                <h2 class="mb-1">Data Pegawai - {{ $company->name }}</h2>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="ti ti-smart-home"></i></a></li>
                        <li class="breadcrumb-item">Master Data</li>
                        <li class="breadcrumb-item active" aria-current="page">Data Pegawai - {{ $company->name }}</li>
                    </ol>
                </nav>
            </div>
            <div class="d-flex my-xl-auto right-content align-items-center flex-wrap">
                <div class="me-2 mb-2">
                    <a href="#" data-bs-toggle="modal" data-bs-target="#exportModal" class="btn btn-success d-inline-flex align-items-center">
                        <i class="ti ti-file-export me-1"></i>Export Excel
                    </a>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
                <h5>List Pegawai - {{ $company->name }}</h5>
                <div class="d-flex align-items-center flex-wrap row-gap-2">
                    <div class="me-3">
                        <select id="siteFilter" class="form-control select2">
                            <option value="">Semua Site</option>
                            @foreach($sites as $site)
                                <option value="{{ $site->id }}">{{ $site->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="me-3">
                        <select id="statusFilter" class="form-control select2">
                            <option value="">Semua Status</option>
                            <option value="aktif">Aktif</option>
                            <option value="resign">Resign</option>
                        </select>
                    </div>
                    <button type="button" id="resetFilter" class="btn btn-outline-secondary">
                        <i class="ti ti-refresh me-1"></i> Reset
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="custom-datatable-filter table-responsive">
                    <table class="table table-bordered data-table" style="font-size: 11px; width: 100%;">
                        <thead>
                            <tr>
                                <th style="text-align: center; vertical-align: middle; width: 40px;">NO</th>
                                @foreach($columns as $col)
                                    <th style="text-align: center; vertical-align: middle; white-space: nowrap;">{{ $col['label'] }}</th>
                                @endforeach
                                <th style="text-align: center; vertical-align: middle; white-space: nowrap;">AKSI</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    <div class="modal fade" id="exportModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">Export Data Pegawai - {{ $company->name }}</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('employees.export') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Filter Site</label>
                            <select name="site_id" class="form-select select2-modal">
                                <option value="">Semua Site</option>
                                @foreach($sites as $site)
                                    <option value="{{ $site->id }}">{{ $site->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success">
                            <i class="ti ti-download me-1"></i>Download Excel
                        </button>
                    </div>
                </form>
            </div>
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
<script>
$(document).ready(function () {
    $('.select2').select2();

    var columns = [
        { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, width: '40px', className: 'text-center' },
        @foreach($columns as $col)
        { data: '{{ $col['key'] }}', name: '{{ $col['key'] }}', defaultContent: '-' },
        @endforeach
        { data: 'aksi', name: 'aksi', orderable: false, searchable: false, className: 'text-center' },
    ];

    var table = $('.data-table').DataTable({
        processing: true,
        serverSide: true,
        autoWidth: false,
        scrollX: true,
        ajax: {
            url: "{{ route('employees.company', $company->id) }}",
            type: "GET",
            data: function (d) {
                d.site_id = $('#siteFilter').val();
                d.status  = $('#statusFilter').val();
            }
        },
        columns: columns,
    });

    $('#siteFilter, #statusFilter').on('change', function () {
        table.ajax.reload();
    });

    $('#resetFilter').on('click', function () {
        $('#siteFilter').val('').trigger('change');
        $('#statusFilter').val('').trigger('change');
        table.ajax.reload();
    });

    $('#exportModal').on('shown.bs.modal', function () {
        $(this).find('.select2-modal').select2({
            dropdownParent: $(this),
            placeholder: 'Semua Site',
            allowClear: true,
        });
    });
});
</script>
@endpush
