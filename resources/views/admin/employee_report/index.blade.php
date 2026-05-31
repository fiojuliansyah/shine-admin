@extends('admin.layouts.main')

@section('content')
<div class="page-wrapper">
    <div class="content">

        <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
            <div class="my-auto mb-2">
                <h2 class="mb-1">Data Karyawan</h2>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="#"><i class="ti ti-smart-home"></i></a></li>
                        <li class="breadcrumb-item active" aria-current="page">Data Karyawan</li>
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

        <div class="card border-0 shadow-sm">
            <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
                <h5 class="mb-0">List Karyawan</h5>
                <div class="d-flex my-xl-auto right-content align-items-center flex-wrap row-gap-3">
                    <form id="filter-form" class="d-flex flex-wrap row-gap-3 align-items-center">
                        <div class="me-3">
                            <select name="site_id" id="filter_site" class="form-select select2" style="min-width:220px;">
                                <option value="">Semua Site</option>
                                @foreach ($sites as $site)
                                    <option value="{{ $site->id }}" @selected(request('site_id') == $site->id)>
                                        {{ $site->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="ti ti-filter me-1"></i>Filter
                            </button>
                            <a href="{{ route('employee-report.index') }}" class="btn btn-outline-secondary">
                                <i class="ti ti-refresh me-1"></i>Reset
                            </a>
                        </div>
                    </form>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="custom-datatable-filter table-responsive">
                    {{ $dataTable->table(['id' => 'employee-report-table']) }}
                </div>
            </div>
        </div>

    </div>

    {{-- Modal Export --}}
    <div class="modal fade" id="exportModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">Export Data Karyawan</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('employee-report.export') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Filter Site</label>
                            <select name="site_id" class="form-select select2-modal">
                                <option value="">Semua Site</option>
                                @foreach ($sites as $site)
                                    <option value="{{ $site->id }}">{{ $site->name }}</option>
                                @endforeach
                            </select>
                            <div class="form-text">Kosongkan untuk export semua site.</div>
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
{{ $dataTable->scripts(attributes: ['type' => 'module']) }}

<script>
$(document).ready(function () {
    $('.select2').select2();

    window.LaravelDataTables = window.LaravelDataTables || {};
    window.LaravelDataTables['employee-report-table'].on('preXhr.dt', function (e, settings, data) {
        data.site_id = $('#filter_site').val();
    });

    $('#filter-form').on('submit', function (e) {
        e.preventDefault();
        window.LaravelDataTables['employee-report-table'].ajax.reload();
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
