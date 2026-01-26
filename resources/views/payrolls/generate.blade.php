@extends('layouts.main')

@section('content')
<div class="page-wrapper">
    <div class="content">
        <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
            <div class="my-auto mb-2">
                <h2 class="mb-1">Generate Payroll</h2>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item">
                            <a href="index.html"><i class="ti ti-smart-home"></i></a>
                        </li>
                        <li class="breadcrumb-item">
                            Finance & Accounts
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">Generate Payroll</li>
                    </ol>
                </nav>
            </div>
        </div>
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title mb-4">Generate Payroll</h4>
                        <form action="{{ route('payroll.generate') }}" method="POST">
                            @csrf
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label for="site_id" class="form-label">Site</label>
                                        <select class="form-select" id="site_id" name="site_id">
                                            <option value="">All Site</option>
                                            @foreach($sites as $site)
                                                <option value="{{ $site->id }}">{{ $site->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label for="month" class="form-label">Bulan</label>
                                        <select class="form-select" id="month" name="month" required>
                                            @foreach(range(1, 12) as $m)
                                                <option value="{{ $m }}" {{ date('n') == $m ? 'selected' : '' }}>
                                                    {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label for="year" class="form-label">Tahun</label>
                                        <input type="number" class="form-control" id="year" name="year" 
                                            value="{{ date('Y') }}" min="2020" max="2099" required>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label">&nbsp;</label>
                                        <button type="submit" class="btn btn-primary w-100">Generate Payroll</button>
                                    </div>
                                </div>
                            </div>
                        </form>                        
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title mb-4">Generated Payrolls by Site and Period</h4>
                        {{ $dataTable->table() }}
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
<!-- Modal Delete -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete the payroll for <strong id="payrollSiteName"></strong> (<span id="payrollEndDate"></span>)?
                <br><br>
                <input type="text" class="form-control" id="confirmInput" placeholder="Tulis CONFIRM untuk memproses">
            </div>
            <div class="modal-footer">
                <form id="deleteForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="button" class="btn btn-light me-2" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger" id="confirmDeleteBtn" disabled>Hapus</button>
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
<script type="text/javascript">
    $(document).ready(function () {
        $(document).on('click', '.deletePayroll', function () {
            let siteName = $(this).data('site');
            let endDate = $(this).data('date');
            let deleteUrl = $(this).data('url');

            $('#payrollSiteName').text(siteName);
            $('#payrollEndDate').text(endDate);
            $('#deleteForm').attr('action', deleteUrl);

            $('#deleteModal').modal('show');
        });

        // Konfirmasi input sebelum menghapus
        $('#confirmInput').on('input', function () {
            let confirmDeleteBtn = $('#confirmDeleteBtn');
            if ($(this).val() === "CONFIRM") {
                confirmDeleteBtn.prop('disabled', false);
            } else {
                confirmDeleteBtn.prop('disabled', true);
            }
        });
    });
</script>
@endpush

