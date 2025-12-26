@extends('layouts.main')

@section('content')
<div class="page-wrapper">
    <div class="content">

        <!-- Breadcrumb -->
        <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
            <div class="my-auto mb-2">
                <h2 class="mb-1">Data Lembur</h2>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item">
                            <a href="{{ route('dashboard') }}"><i class="ti ti-smart-home"></i></a>
                        </li>
                        <li class="breadcrumb-item">
                            HRM
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">Data Lembur</li>
                    </ol>
                </nav>
            </div>
            <div class="d-flex my-xl-auto right-content align-items-center flex-wrap ">
                <div class="me-2 mb-2">
                    {{-- <div class="dropdown">
                        <a href="javascript:void(0);" class="dropdown-toggle btn btn-white d-inline-flex align-items-center" data-bs-toggle="dropdown">
                            <i class="ti ti-file-export me-1"></i>Export
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end p-3">
                            <li>
                                <a href="javascript:void(0);" class="dropdown-item rounded-1"><i class="ti ti-file-type-pdf me-1"></i>Export as PDF</a>
                            </li>
                            <li>
                                <a href="javascript:void(0);" class="dropdown-item rounded-1"><i class="ti ti-file-type-xls me-1"></i>Export as Excel </a>
                            </li>
                        </ul>
                    </div> --}}
                </div>
            </div>
        </div>
        <!-- /Breadcrumb -->

        <!-- Overtime Counts -->
        <div class="row">
            <div class="col-xl-4 col-md-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center flex-wrap justify-content-between">
                            <div>
                                <p class="fs-12 fw-medium mb-0 text-gray-5">Total Pengajuan</p>
                                <h4>{{ $overtimeCount }}</h4>
                            </div>
                            <div>
                                <span class="p-2 br-10 bg-transparent-primary border border-primary d-flex align-items-center justify-content-center"><i class="ti ti-user-check text-primary fs-18"></i></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-md-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center flex-wrap justify-content-between">
                            <div>
                                <p class="fs-12 fw-medium mb-0 text-gray-5">Pending Request</p>
                                <h4>{{ $pendingCount }}</h4>
                            </div>
                            <div>
                                <span class="p-2 br-10 bg-transparent-purple border border-purple d-flex align-items-center justify-content-center"><i class="ti ti-user-exclamation text-purple fs-18"></i></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-md-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center flex-wrap justify-content-between">
                            <div>
                                <p class="fs-12 fw-medium mb-0 text-gray-5">Rejected</p>
                                <h4>{{ $rejectedCount }}</h4>
                            </div>
                            <div>
                                <span class="p-2 br-10 bg-skyblue-transparent border border-skyblue d-flex align-items-center justify-content-center"><i class="ti ti-user-exclamation text-skyblue fs-18"></i></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- /Overtime Counts -->

        <!-- Performance Indicator list -->
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
                <h5>Data Lembur</h5>
                <div class="d-flex my-xl-auto right-content align-items-center flex-wrap row-gap-3">
                    <form id="filter-form" action="{{ route('overtimes.index') }}" method="GET" class="d-flex flex-wrap">
                        <div class="me-3 mb-2">
                            <div class="input-icon-end position-relative">
                                <input type="date" name="start_date" class="form-control" value="{{ $filters['start_date'] ?? '' }}" placeholder="Start Date">
                            </div>
                        </div>
                        <div class="me-3 mb-2">
                            <div class="input-icon-end position-relative">
                                <input type="date" name="end_date" class="form-control" value="{{ $filters['end_date'] ?? '' }}" placeholder="End Date">
                            </div>
                        </div>
                        <div class="dropdown me-3 mb-2">
                            <select name="user_id" class="form-control select2">
                                <option value="">Pilih Pegawai</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ isset($filters['user_id']) && $filters['user_id'] == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="dropdown me-3 mb-2">
                            <select name="site_id" class="form-control select2">
                                <option value="">Pilih Project</option>
                                @foreach($sites as $site)
                                    <option value="{{ $site->id }}" {{ isset($filters['site_id']) && $filters['site_id'] == $site->id ? 'selected' : '' }}>
                                        {{ $site->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="dropdown me-3 mb-2">
                            <select name="status" class="form-control select2">
                                <option value="">Pilih Status</option>
                                @foreach(['pending', 'approved', 'rejected'] as $status)
                                    <option value="{{ $status }}" {{ isset($filters['status']) && $filters['status'] == $status ? 'selected' : '' }}>
                                        {{ ucfirst($status) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-2">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="ti ti-filter me-1"></i> Filter
                            </button>
                            <a href="{{ route('overtimes.index') }}" class="btn btn-outline-secondary">
                                <i class="ti ti-refresh me-1"></i> Reset
                            </a>
                        </div>
                    </form>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="custom-datatable-filter table-responsive">
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
@endpush