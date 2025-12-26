@extends('layouts.main')

@section('content')
    <div class="page-wrapper">
        <div class="content">

            <!-- Breadcrumb -->
            <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
                <div class="my-auto mb-2">
                    <h2 class="mb-1">Data Laporan Security Patroll</h2>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="index.html"><i class="ti ti-smart-home"></i></a>
                            </li>
                            <li class="breadcrumb-item">
                                REPORTS
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Data Laporan Security Patroll</li>
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
                <div class="col-xl-6 col-md-6">
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
                                    <h4>{{ $totalCount }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-6 col-md-6">
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
                                    <p class="mb-1">Today Reports</p>
                                    <h4>{{ $todayCount }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /reports Info -->

            {{-- pie chart --}}
            <div class="my-2 p-3 card">
                <div class="row">
                    <div class="col-6">
                        <h3 class="mb-4 text-center">Task Security Patroll Daily</h3>
                        <canvas id="taskProgressPie" width="400" height="400"></canvas>
                    </div>
                    <div class="col-6">
                        <h3 class="mb-4 text-center">Jumlah Keliling Patrolli Filter Hari Ini</h3>
                        <div class="mt-2 d-flex flex-column justify-content-between">
                            <ul class="list-group list-group-flush">
                                @foreach ($patrollSessions as $patroll)
                                    <li class="my-1">
                                        <a href="{{ route('patrollReport.index') . '?patroll-turn=' . $patroll->turn }}"
                                            class="text-decoration-none small d-flex justify-content-between align-items-center w-100 
                {{ request('patroll-turn') == $patroll->turn ? 'bg-primary text-white p-2 rounded' : 'bg-light p-2 rounded' }}">
                                            <span class="fw-semibold">{{ $patroll->turn }} Keliling</span>
                                            <span>{{ $patroll->patroll_code }}</span>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>

                            <div class="mt-2">
                                {{ $patrollSessions->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            {{-- /pie chart --}}

            <!-- reports list -->
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
                    <h5>Report List</h5>
                    <div class="d-flex my-xl-auto right-content align-items-center flex-wrap row-gap-3">
                        <form id="filter-form" method="GET" class="d-flex flex-wrap row-gap-3">
                            <input type="hidden" name="patroll-turn" value="{{ request('patroll-turn') }}">
                            <!-- Date Filter -->
                            <div class="me-3">
                                <div class="input-icon-end position-relative">
                                    <input type="date" name="date" class="form-control"
                                        value="{{ $filters['date'] ?? '' }}" placeholder="Tanggal">
                                </div>
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

                            <!-- Floor Filter -->
                            <div class="dropdown me-3">
                                <select name="floor_id" class="form-select select2">
                                    <option value="">Lantai Gedung</option>
                                    @foreach ($floors as $floor)
                                        <option value="{{ $floor->id }}"
                                            {{ isset($filters['floor_id']) && $filters['floor_id'] == $floor->id ? 'selected' : '' }}>
                                            {{ $floor->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-2">
                                <button type="submit" class="btn btn-primary me-2">
                                    <i class="ti ti-filter me-1"></i> Filter
                                </button>
                                <a href="{{ route('patrollReport.index') }}" class="btn btn-outline-secondary">
                                    <i class="ti ti-refresh me-1"></i> Reset
                                </a>
                            </div>
                        </form>

                        <div class="mb-2">
                            <a href="{{ route('patrollReport.export', request()->all()) }}"
                                class="btn btn-outline-secondary">
                                <i class="ti ti-refresh me-1"></i> Export
                            </a>
                        </div>
                        <div class="mb-2 mx-2">
                            <button class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#printToday">
                                <i class="fa-solid fa-print"></i> Print Today
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="custom-datatable-filter table-responsive">
                        {{ $dataTable->table(['id' => 'patrollreport-table']) }}
                    </div>
                </div>
            </div>
            <!-- /reports list -->
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="printToday" tabindex="-1" aria-labelledby="printToday" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title" id="printToday">Pilih Site</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @foreach ($sites as $site)
                        <a href="{{ route('patrollReport.print', $site->id) }}"
                            class="text-decoration-none small d-flex justify-content-between align-items-center w-100 siteList my-1 p-2 rounded" target="_blank">
                            <span>{{ $site->name }}</span>
                        </a>
                    @endforeach
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('css')
    <link rel="stylesheet" href="/admin/assets/css/dataTables.bootstrap5.min.css">
    <style>
        .siteList {
            background-color: rgba(224, 222, 222, 0.724);
            cursor: pointer;
        }

        .siteList:hover {
            background-color: #k;
            color: white;
        }
    </style>
@endpush

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script src="/admin/assets/js/jquery.dataTables.min.js"></script>
    <script src="/admin/assets/js/dataTables.bootstrap5.min.js"></script>
    {{ $dataTable->scripts(attributes: ['type' => 'module']) }}

    <script>
        const data = {
            labels: ['Selesai ({{ $doneTasks }})', 'Belum Selesai ({{ $pendingTasks }})'],
            datasets: [{
                label: 'Persentase Task Hari Ini',
                data: [{{ $donePercent }}, {{ $pendingPercent }}],
                backgroundColor: [
                    'rgba(75, 192, 192, 0.5)', // selesai
                    'rgba(255, 99, 132, 0.5)' // belum
                ],
                borderColor: [
                    'rgba(75, 192, 192, 1)',
                    'rgba(255, 99, 132, 1)'
                ],
                borderWidth: 1
            }]
        };

        const config = {
            type: 'pie',
            data: data,
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        };

        new Chart(document.getElementById('taskProgressPie'), config);
    </script>
@endpush
