@extends('layouts.main')

@section('content')
    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <div class="content">

            <!-- Breadcrumb -->
            <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
                <div class="my-auto mb-2">
                    <h2 class="mb-1">Data Absensi</h2>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="index.html"><i class="ti ti-smart-home"></i></a>
                            </li>
                            <li class="breadcrumb-item">
                                HRM
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Data Absensi</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex my-xl-auto right-content align-items-center flex-wrap">
                    <div class="card-body p-0">
                        <form action="{{ route('attendances.get-alpha') }}" method="POST"
                            class="d-flex align-items-center">
                            @csrf
                            <div class="me-2">
                                <label class="small mb-1 d-block">Dari Tanggal</label>
                                <input type="date" name="start_date" class="form-control form-control-sm"
                                    value="{{ request('start_date', date('Y-m-d')) }}" required>
                            </div>
                            <div class="me-2">
                                <label class="small mb-1 d-block">Sampai Tanggal</label>
                                <input type="date" name="end_date" class="form-control form-control-sm"
                                    value="{{ request('end_date', date('Y-m-d')) }}" required>
                            </div>
                            <div class="ms-1">
                                <label class="small mb-1 d-block">&nbsp;</label>
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <i class="ti ti-user-x me-1"></i>Update Alpha Status
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <!-- /Breadcrumb -->

            <div class="card border-0">
                <div class="card-body">
                    <div class="row align-items-center mb-4">
                        <div class="col-md-5">
                            <div class="mb-3 mb-md-0">
                                <h4 class="mb-1">Detail Data Absensi Hari ini</h4>
                                <p>Data dari {{ $users->count() }} total Pegawai</p>
                            </div>
                        </div>
                    </div>
                    <div class="border rounded">
                        <div class="row gx-0">
                            <div class="col-md col-sm-4 border-end">
                                <div class="p-3">
                                    <span class="fw-medium mb-1 d-block">Regular</span>
                                    <div class="d-flex align-items-center justify-content-between">
                                        <h5>{{ $attendanceStats['regular'] }}</h5>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md col-sm-4 border-end">
                                <div class="p-3">
                                    <span class="fw-medium mb-1 d-block">Telat</span>
                                    <div class="d-flex align-items-center justify-content-between">
                                        <h5>{{ $attendanceStats['late'] }}</h5>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md col-sm-4 border-end">
                                <div class="p-3">
                                    <span class="fw-medium mb-1 d-block">Alpha</span>
                                    <div class="d-flex align-items-center justify-content-between">
                                        <h5>{{ $attendanceStats['alpha'] }}</h5>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md col-sm-4 border-end">
                                <div class="p-3">
                                    <span class="fw-medium mb-1 d-block">Cuti</span>
                                    <div class="d-flex align-items-center justify-content-between">
                                        <h5>{{ $attendanceStats['leave'] }}</h5>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md col-sm-4">
                                <div class="p-3">
                                    <span class="fw-medium mb-1 d-block">Izin</span>
                                    <div class="d-flex align-items-center justify-content-between">
                                        <h5>{{ $attendanceStats['permit'] }}</h5>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
                    <h5>Data Absensi</h5>
                    <form id="attendance-filter-form" action="{{ route('attendances.index') }}" method="GET"
                        class="d-flex my-xl-auto right-content align-items-center flex-wrap row-gap-3">
                        <!-- Site Filter -->
                        <div class="me-3">
                            <select name="site_id" class="form-control select2">
                                <option value="">Select Site</option>
                                @foreach ($sites as $site)
                                    <option value="{{ $site->id }}"
                                        {{ isset($filters['site_id']) && $filters['site_id'] == $site->id ? 'selected' : '' }}>
                                        {{ $site->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Date Range Filters -->
                        <div class="me-3">
                            <div class="input-icon-end position-relative">
                                <input type="date" name="start_date" class="form-control"
                                    value="{{ $filters['start_date'] ?? '' }}" placeholder="Start Date">
                            </div>
                        </div>
                        <div class="me-3">
                            <div class="input-icon-end position-relative">
                                <input type="date" name="end_date" class="form-control"
                                    value="{{ $filters['end_date'] ?? '' }}" placeholder="End Date">
                            </div>
                        </div>

                        <!-- Type Filter -->
                        <div class="dropdown me-3">
                            <a href="javascript:void(0);"
                                class="dropdown-toggle btn btn-white d-inline-flex align-items-center"
                                data-bs-toggle="dropdown">
                                {{ isset($filters['type']) ? ucfirst($filters['type']) : 'Select Status' }}
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end p-3">
                                <li>
                                    <a href="{{ route('attendances.index', array_merge(request()->except('type'), ['type' => ''])) }}"
                                        class="dropdown-item rounded-1">All</a>
                                </li>
                                <li>
                                    <a href="{{ route('attendances.index', array_merge(request()->except('type'), ['type' => 'regular'])) }}"
                                        class="dropdown-item rounded-1">Regular</a>
                                </li>
                                <li>
                                    <a href="{{ route('attendances.index', array_merge(request()->except('type'), ['type' => 'off'])) }}"
                                        class="dropdown-item rounded-1">OFF</a>
                                </li>
                                <li>
                                    <a href="{{ route('attendances.index', array_merge(request()->except('type'), ['type' => 'late'])) }}"
                                        class="dropdown-item rounded-1">Terlambat</a>
                                </li>
                                <li>
                                    <a href="{{ route('attendances.index', array_merge(request()->except('type'), ['type' => 'alpha'])) }}"
                                        class="dropdown-item rounded-1">Alpha</a>
                                </li>
                                <li>
                                    <a href="{{ route('attendances.index', array_merge(request()->except('type'), ['type' => 'leave'])) }}"
                                        class="dropdown-item rounded-1">Cuti</a>
                                </li>
                                <li>
                                    <a href="{{ route('attendances.index', array_merge(request()->except('type'), ['type' => 'permit'])) }}"
                                        class="dropdown-item rounded-1">Izin</a>
                                </li>
                            </ul>
                        </div>

                        <div class="mb-2">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="ti ti-filter me-1"></i> Filter
                            </button>
                            <a href="{{ route('attendances.index') }}" class="btn btn-outline-secondary">
                                <i class="ti ti-refresh me-1"></i> Reset
                            </a>
                        </div>
                    </form>
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
