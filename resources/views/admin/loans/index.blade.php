@extends('admin.layouts.main')

@section('content')
<div class="page-wrapper">
    <div class="content">

        <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
            <div class="my-auto mb-2">
                <h2 class="mb-1">Loans</h2>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item">
                            <a href="#"><i class="ti ti-smart-home"></i></a>
                        </li>
                        <li class="breadcrumb-item">Finance</li>
                        <li class="breadcrumb-item active" aria-current="page">List Loans</li>
                    </ol>
                </nav>
            </div>

            <div class="d-flex my-xl-auto right-content align-items-center flex-wrap">
                {{-- <div class="me-2 mb-2">
                    <a href="{{ route('loans.export') }}" class="btn btn-white d-inline-flex align-items-center">
                        <i class="ti ti-file-export me-1"></i>Export Excel
                    </a>
                </div> --}}

                <div class="mb-2">
                    <a href="#" data-bs-toggle="modal" data-bs-target="#createLoanModal" class="btn btn-primary d-flex align-items-center">
                        <i class="ti ti-circle-plus me-2"></i>Tambah Loan
                    </a>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
                <h5>List Loans</h5>
            </div>

            <div class="card-body p-0">
                <div class="custom-datatable-filter table-responsive">
                    {{ $dataTable->table() }}
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="createLoanModal" tabindex="-1" aria-labelledby="createLoanModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">

                <div class="modal-header">
                    <h4 class="modal-title">Tambah Loan</h4>
                    <button type="button" class="btn-close custom-btn-close" data-bs-dismiss="modal">
                        <i class="ti ti-x"></i>
                    </button>
                </div>

                <form action="{{ route('loans.store') }}" method="POST">
                    @csrf

                    <div class="modal-body pb-0">

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">User</label>
                                <select name="user_id" class="form-select">
                                    @foreach ($users as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Jumlah Pinjaman</label>
                                <input type="number" step="0.01" name="amount" class="form-control" required>
                            </div>
                        </div>

                        <div class="row">

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Bunga (%)</label>
                                <input type="number" step="0.01" name="interest_rate" class="form-control" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tenor (bulan)</label>
                                <input type="number" name="tenor" class="form-control" required>
                            </div>

                        </div>

                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light me-2" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
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
@endpush