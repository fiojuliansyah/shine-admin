@extends('admin.layouts.main')

@section('content')
<div class="page-wrapper">
    <div class="content">

        <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
            <div class="my-auto mb-2">
                <h2 class="mb-1">Pemberkasan</h2>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item">
                            <a href="#"><i class="ti ti-smart-home"></i></a>
                        </li>
                        <li class="breadcrumb-item">Recruitment</li>
                        <li class="breadcrumb-item">Kandidat</li>
                        <li class="breadcrumb-item active">Pemberkasan</li>
                    </ol>
                </nav>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5>List Pemberkasan</h5>
            </div>

            <div class="p-3">
                <div class="row">
                    <div class="col-md-3">
                        <input type="date" id="start_date" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <input type="date" id="end_date" class="form-control">
                    </div>
                    <div class="col-md-2">
                        <button id="filter" class="btn btn-primary w-100">Filter</button>
                    </div>
                    <div class="col-md-2">
                        <button id="reset" class="btn btn-secondary w-100">Reset</button>
                    </div>
                </div>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    {{ $dataTable->table() }}
                </div>
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
$(function () {
    const tableId = 'applicants-table';
    
    $('#filter').click(function () {
        window.LaravelDataTables[tableId].draw();
    });

    $('#reset').click(function () {
        $('#start_date').val('');
        $('#end_date').val('');
        window.LaravelDataTables[tableId].draw();
    });

    $('#' + tableId).on('preXhr.dt', function (e, settings, data) {
        data.start_date = $('#start_date').val();
        data.end_date = $('#end_date').val();
    });
});
</script>
@endpush