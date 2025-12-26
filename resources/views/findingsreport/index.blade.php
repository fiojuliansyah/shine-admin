@extends('layouts.main')

@section('content')
    <div class="page-wrapper">
        <div class="content">

            <!-- Breadcrumb -->
            <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
                <div class="my-auto mb-2">
                    <h2 class="mb-1">Data Laporan Temuan</h2>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="index.html"><i class="ti ti-smart-home"></i></a>
                            </li>
                            <li class="breadcrumb-item">
                                REPORTS
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Data Laporan Temuan</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex my-xl-auto right-content align-items-center flex-wrap ">
                    <div class="me-2 mb-2">
                        {{-- <div class="dropdown">
                        <a href="javascript:void(0);" class="dropdown-toggle btn btn-white d-inline-flex align-items-center" data-bs-toggle="dropdown">
                            <i class="ti ti-file-export me-1"></i>Export
                        </a>
                        <ul class="dropdown-menu  dropdown-menu-end p-3">
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

            <!-- reports Info -->
            <div class="row">
                <div class="col-xl-4 col-md-6">
                    <div class="card bg-green-img">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0 me-2">
                                        <span
                                            class="avatar avatar-md rounded-circle bg-white d-flex align-items-center justify-content-center">
                                            <i class="ti ti-file-text text-success fs-18"></i>
                                        </span>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <p class="mb-1">Total Reports</p>
                                    <h4>{{ $reportCount }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4 col-md-6">
                    <div class="card bg-yellow-img">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0 me-2">
                                        <span
                                            class="avatar avatar-md rounded-circle bg-white d-flex align-items-center justify-content-center">
                                            <i class="ti ti-user-check text-warning fs-18"></i>
                                        </span>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <p class="mb-1">Resolved Reports</p>
                                    <h4>{{ $solvedCount }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4 col-md-6">
                    <div class="card bg-blue-img">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0 me-2">
                                        <span
                                            class="avatar avatar-md rounded-circle bg-white d-flex align-items-center justify-content-center">
                                            <i class="ti ti-clock text-info fs-18"></i>
                                        </span>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <p class="mb-1">Pending Reportd</p>
                                    <h4>{{ $pendingCount }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /reports Info -->

            <!-- reports list -->
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
                    <h5>Report List</h5>
                    <div class="d-flex my-xl-auto right-content align-items-center flex-wrap row-gap-3">
                        <form id="filter-form" method="GET" class="d-flex flex-wrap row-gap-3">
                            <!-- Date Filter -->
                            <div class="me-3">
                                <div class="input-icon-end position-relative">
                                    <input type="date" name="date" class="form-control"
                                        value="{{ $filters['date'] ?? '' }}" placeholder="Tanggal">
                                </div>
                            </div>

                            <!-- Report Type Filter -->
                            <div class="dropdown me-3">
                                <select name="type" class="form-select ">
                                    <option selected disabled>Tipe Laporan Temuan</option>
                                    <option value="low"
                                        {{ isset($filters['type']) && $filters['type'] == 'low' ? 'selected' : '' }}>Low
                                    </option>
                                    <option value="medium"
                                        {{ isset($filters['type']) && $filters['type'] == 'medium' ? 'selected' : '' }}>
                                        Medium</option>
                                    <option value="high"
                                        {{ isset($filters['type']) && $filters['type'] == 'high' ? 'selected' : '' }}>High
                                    </option>
                                </select>
                            </div>

                            <!-- Employee Filter -->
                            <div class="dropdown me-3">
                                <select name="user_id" class="form-select select2">
                                    <option value="">Pegawai</option>
                                    @foreach ($users as $user)
                                        <option value="{{ $user->id }}"
                                            {{ isset($filters['user_id']) && $filters['user_id'] == $user->id ? 'selected' : '' }}>
                                            {{ $user->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Site Filter -->
                            <div class="dropdown me-3">
                                <select name="site_id" class="form-select select2">
                                    <option value="">Site</option>
                                    @foreach ($sites as $site)
                                        <option value="{{ $site->id }}"
                                            {{ isset($filters['site_id']) && $filters['site_id'] == $site->id ? 'selected' : '' }}>
                                            {{ $site->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Status Filter -->
                            <div class="dropdown me-3">
                                <select name="status" class="form-select">
                                    <option value="">Status</option>
                                    <option value="pending"
                                        {{ isset($filters['status']) && $filters['status'] == 'pending' ? 'selected' : '' }}>
                                        Pending</option>
                                    <option value="solved"
                                        {{ isset($filters['status']) && $filters['status'] == 'solved' ? 'selected' : '' }}>
                                        Solved</option>
                                </select>
                            </div>

                            <div class="mb-2">
                                <button type="submit" class="btn btn-primary me-2">
                                    <i class="ti ti-filter me-1"></i> Filter
                                </button>
                                <a href="{{ route('findingReport.index') }}" class="btn btn-outline-secondary">
                                    <i class="ti ti-refresh me-1"></i> Reset
                                </a>
                            </div>
                        </form>
                        <div class="mb-2">
                            <a href="{{ route('findingReport.export', request()->all()) }}" class="btn btn-outline-secondary">
                                <i class="ti ti-refresh me-1"></i> Export
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="custom-datatable-filter table-responsive">
                        {{ $dataTable->table(['id' => 'findingsreport-table']) }}
                    </div>
                </div>
            </div>
            <!-- /reports list -->
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
