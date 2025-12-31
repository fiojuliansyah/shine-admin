@extends('layouts.main')

@section('content')
<div class="page-wrapper">
    <div class="content">

        <!-- Breadcrumb -->
        <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
            <div class="my-auto mb-2">
                <h2 class="mb-1">Admin Dashboard</h2>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item">
                            <a href="index.html"><i class="ti ti-smart-home"></i></a>
                        </li>
                        <li class="breadcrumb-item active">
                            Dashboard
                        </li>
                    </ol>
                </nav>
            </div>
        </div>
        <!-- /Breadcrumb -->

        <!-- Welcome Wrap -->
        <div class="card border-0">
            <div class="card-body d-flex align-items-center justify-content-between flex-wrap pb-1">
                <div class="d-flex align-items-center mb-3">
                    <span class="avatar avatar-xl flex-shrink-0">
                        <img src="{{ Auth::user()->profile['avatar_url'] ?? '/assets/media/avatars/blank.png' }}" class="rounded-circle" alt="img">
                    </span>
                    <div class="ms-3">
                        <h3 class="mb-2">Welcome Back to KARYAX, {{ Auth::user()->name }}</h3>
                        {{-- <p>You have <span class="text-primary text-decoration-underline">21</span> Pending Approvals & <span class="text-primary text-decoration-underline">14</span> Leave Requests</p> --}}
                    </div>
                </div>
                <div class="d-flex align-items-center flex-wrap mb-1">
                    <a href="{{ route('sites.index') }}" class="btn btn-secondary btn-md me-2 mb-2"><i class="ti ti-square-rounded-plus me-1"></i>Tambah Project</a>
                    <a href="{{ route('careers.index') }}" class="btn btn-primary btn-md mb-2"><i class="ti ti-square-rounded-plus me-1"></i>Tambah Lowongan</a>
                </div>
            </div>
        </div>
        <!-- /Welcome Wrap -->

        <div class="row">

            <!-- Widget Info -->
            <div class="col-xxl-8 d-flex">
                <div class="row flex-fill">
                    <div class="col-md-3 d-flex">
                        <div class="card flex-fill">
                            <div class="card-body">
                                <span class="avatar rounded-circle bg-primary mb-2">
                                    <i class="ti ti-calendar-share fs-16"></i>
                                </span>
                                <h6 class="fs-13 fw-medium text-default mb-1">Ikhtisar Kehadiran</h6>
                                <h3 class="mb-3">{{ $attendanceCount }}</h3>
                                <a href="{{ route('attendances.index') }}" class="link-default">View Details</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 d-flex">
                        <div class="card flex-fill">
                            <div class="card-body">
                                <span class="avatar rounded-circle bg-secondary mb-2">
                                    <i class="ti ti-browser fs-16"></i>
                                </span>
                                <h6 class="fs-13 fw-medium text-default mb-1">Total Area Project</h6>
                                <h3 class="mb-3">{{ $siteCount }}</h3>
                                <a href="{{ route('sites.index') }}" class="link-default">Lihat semua</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 d-flex">
                        <div class="card flex-fill">
                            <div class="card-body">
                                <span class="avatar rounded-circle bg-info mb-2">
                                    <i class="ti ti-building fs-16"></i>
                                </span>
                                <h6 class="fs-13 fw-medium text-default mb-1">Total perusahaan</h6>
                                <h3 class="mb-3">{{ $companyCount }}</h3>
                                <a href="{{ route('companies.index') }}" class="link-default">Lihat semua</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 d-flex">
                        <div class="card flex-fill">
                            <div class="card-body">
                                <span class="avatar rounded-circle bg-pink mb-2">
                                    <i class="ti ti-checklist fs-16"></i>
                                </span>
                                <h6 class="fs-13 fw-medium text-default mb-1">Total Lowongan</h6>
                                <h3 class="mb-3">{{ $careerCount }}</h3>
                                <a href="{{ route('careers.index') }}" class="link-default">Lihat semua</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 d-flex">
                        <div class="card flex-fill">
                            <div class="card-body">
                                <span class="avatar rounded-circle bg-purple mb-2">
                                    <i class="ti ti-users-group  fs-16"></i>
                                </span>
                                <h6 class="fs-13 fw-medium text-default mb-1">Pegawai</h6>
                                <h3 class="mb-3">{{ $userCount }}</h3>
                                <a href="{{ route('employees.index') }}" class="link-default">Lihat semua</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 d-flex">
                        <div class="card flex-fill">
                            <div class="card-body">
                                <span class="avatar rounded-circle bg-danger mb-2">
                                    <i class="ti ti-browser fs-16"></i>
                                </span>
                                <h6 class="fs-13 fw-medium text-default mb-1">Budget Payroll Bulan Lalu</h6>
                                <h3 class="mb-3">Rp. {{ number_format($totalCompanyExpense) }}</h3>
                                <a href="{{ route('payroll.generate') }}" class="link-default">Lihat semua</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 d-flex">
                        <div class="card flex-fill">
                            <div class="card-body">
                                <span class="avatar rounded-circle bg-success mb-2">
                                    <i class="ti ti-users-group fs-16"></i>
                                </span>
                                <h6 class="fs-13 fw-medium text-default mb-1">Pelamar</h6>
                                <h3 class="mb-3">{{ $applicantCount }} </h3>
                                <a href="{{ route('applicants.index') }}" class="link-default">Lihat semua</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /Widget Info -->

            <!-- Employees By Department -->
            <div class="col-xxl-4 d-flex">
                <div class="card flex-fill">
                    <div class="card-header pb-2 d-flex align-items-center justify-content-between flex-wrap">
                        <h5 class="mb-2">Users By Role</h5>
                    </div>
                    <div class="card-body">
                        <div id="roles-chart"></div>
                        <p class="fs-13"><i class="ti ti-circle-filled me-2 fs-8 text-primary"></i>Pegawai berdasarkan Jabatan updated on <span class="text-success fw-bold">{{ date('d M Y') }}</span>
                        </p>
                    </div>
                </div>
            </div>
            <!-- /Employees By Department -->

        </div>

        <div class="row">

            <!-- Total Employee -->
            <div class="col-xxl-4 d-flex">
                <div class="card flex-fill">
                    <div class="card-header pb-2 d-flex align-items-center justify-content-between flex-wrap">
                        <h5 class="mb-2">Status Kandidat</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <p class="fs-13 mb-3">Total Kandidat</p>
                            <h3 class="mb-3">{{ $applicantCount }}</h3>
                        </div>
                        <div class="progress-stacked emp-stack mb-3">
                            @php
                                $colors = [
                                    'Applied' => 'primary',
                                    'Interview' => 'warning',
                                    'Test' => 'info',
                                    'Rejected' => 'danger',
                                ];
                                
                                $defaultColor = 'secondary';
                            @endphp
                            
                            @foreach($statusData as $name => $data)
                                <div class="progress" role="progressbar" aria-label="{{ $name }}" 
                                     aria-valuenow="{{ $data['percentage'] }}" 
                                     aria-valuemin="0" aria-valuemax="100" 
                                     style="width: {{ $data['percentage'] }}%">
                                    <div class="progress-bar bg-{{ isset($colors[$name]) ? $colors[$name] : $defaultColor }}"></div>
                                </div>
                            @endforeach
                        </div>
                        <div class="border mb-3">
                            <div class="row gx-0">
                                @php $i = 0; @endphp
                                @foreach($statusData as $name => $data)
                                    <div class="col-6">
                                        <div class="p-2 flex-fill {{ $i % 2 == 0 ? 'border-end' : '' }} {{ $i < 2 ? 'border-bottom' : '' }} {{ $i % 2 == 1 ? 'text-end' : '' }}">
                                            <p class="fs-13 mb-2">
                                                <i class="ti ti-square-filled {{ $i % 2 == 1 ? 'me-2' : '' }} text-{{ isset($colors[$name]) ? $colors[$name] : $defaultColor }} fs-12 {{ $i % 2 == 0 ? 'me-2' : '' }}"></i>
                                                {{ $name }} <span class="text-gray-9">({{ $data['percentage'] }}%)</span>
                                            </p>
                                            <h2 class="display-1">{{ $data['count'] }}</h2>
                                        </div>
                                    </div>
                                    @php $i++; @endphp
                                @endforeach
                            </div>
                        </div>
                        <h6 class="mb-2">Top Posisi</h6>
                        <div class="p-2 d-flex align-items-center justify-content-between border border-primary bg-primary-100 br-5 mb-4">
                            <div class="d-flex align-items-center overflow-hidden">
                                <span class="me-2">
                                    <i class="ti ti-briefcase-filled text-primary fs-24"></i>
                                </span>
                                <div>
                                    <h6 class="text-truncate mb-1 fs-14 fw-medium">{{ $topPosition->name ?? 'n/a' }}</h6>
                                    <p class="fs-13">{{ $topPosition->applicants_count ?? '0' }} Pelamar</p>
                                </div>
                            </div>
                            <div class="text-end">
                                <p class="fs-13 mb-1">Kebutuhan</p>
                                <h5 class="text-primary">{{ $topPosition->candidate ?? '0' }} Orang</h5>
                            </div>
                        </div>
                        <a href="{{ route('applicants.index') }}" class="btn btn-light btn-md w-100">Lihat Semua Kandidat</a>
                    </div>
                </div>
            </div>
            <!-- /Total Employee -->

            <!-- Attendance Overview -->
            <div class="col-xxl-4 col-xl-6 d-flex">
                <div class="card flex-fill">
                    <div class="card-header pb-2 d-flex align-items-center justify-content-between flex-wrap">
                        <h5 class="mb-2">Tinjau Absensi Hari ini</h5>
                    </div>
                    <div class="card-body">
                        <div class="chartjs-wrapper-demo position-relative mb-4">
                            <canvas id="attendance" height="200"></canvas>
                            <div class="position-absolute text-center attendance-canvas">
                                <p class="fs-13 mb-1">Total Absensi</p>
                                <h3>{{ $attendanceCount }}</h3>
                            </div>
                        </div>
                        <h6 class="mb-3">Status</h6>
                        <div class="d-flex align-items-center justify-content-between">
                            <p class="f-13 mb-2"><i class="ti ti-circle-filled text-success me-1"></i>Regular</p>
                            <p class="f-13 fw-medium text-gray-9 mb-2">{{ $attendanceStats['present_percentage'] }}%</p>
                        </div>
                        <div class="d-flex align-items-center justify-content-between">
                            <p class="f-13 mb-2"><i class="ti ti-circle-filled text-secondary me-1"></i>Telat</p>
                            <p class="f-13 fw-medium text-gray-9 mb-2">{{ $attendanceStats['late_percentage'] }}%</p>
                        </div>
                        <div class="d-flex align-items-center justify-content-between">
                            <p class="f-13 mb-2"><i class="ti ti-circle-filled text-warning me-1"></i>Izin</p>
                            <p class="f-13 fw-medium text-gray-9 mb-2">{{ $attendanceStats['permission_percentage'] }}%</p>
                        </div>
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <p class="f-13 mb-2"><i class="ti ti-circle-filled text-danger me-1"></i>Alpha</p>
                            <p class="f-13 fw-medium text-gray-9 mb-2">{{ $attendanceStats['absent_percentage'] }}%</p>
                        </div>
                        <div class="bg-light br-5 box-shadow-xs p-2 pb-0 d-flex align-items-center justify-content-between flex-wrap">
                            <div class="d-flex align-items-center">
                                <p class="mb-2 me-2">Total Absensi</p>
                                <div class="avatar-list-stacked avatar-group-sm mb-2">
                                    @foreach($absentees as $index => $absentee)
                                        @if($index < 4)
                                            <span class="avatar avatar-rounded">
                                                <img class="border border-white" src="{{ $absentee->user->profile->avatar_url ?? 'assets/img/profiles/avatar-' . rand(10, 30) . '.jpg' }}" alt="img">
                                            </span>
                                        @endif
                                    @endforeach
                                    @if(count($absentees) > 4)
                                        <a class="avatar bg-primary avatar-rounded text-fixed-white fs-10" href="javascript:void(0);">
                                            +{{ count($absentees) - 4 }}
                                        </a>
                                    @endif
                                </div>
                            </div>
                            <a href="{{ route('attendances.index') }}" class="fs-13 link-primary text-decoration-underline mb-2">View Details</a>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /Attendance Overview -->
            <div class="col-xxl-4 col-xl-6 d-flex">
                <div class="card flex-fill">
                    <div class="card-header pb-2 d-flex align-items-center justify-content-between flex-wrap">
                        <h5 class="mb-2">Clock-In/Out</h5>
                    </div>
                    <div class="card-body">
                        <div>
                            @forelse($latestAttendances as $index => $attendance)
                                <div class="{{ $index < 2 ? 'mb-3 p-2 border' : 'mb-3 p-2 border' }} {{ $attendance->status == 'late' ? 'border-dashed' : '' }} br-5">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div class="d-flex align-items-center">
                                            <a href="javascript:void(0);" class="avatar flex-shrink-0">
                                                <img src="{{ $attendance->user->profile->avatar_url ?? '/assets/media/avatars/blank.png' }}" class="rounded-circle border border-2" alt="img">
                                            </a>
                                            <div class="ms-2">
                                                <h6 class="fs-14 fw-medium text-truncate">{{ $attendance->user->name ?? '' }}</h6>
                                                <p class="fs-13">
                                                    @foreach ($attendance->user->roles as $role)
                                                        {{ $role->name }}
                                                    @endforeach
                                                </p>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-center">
                                            <a href="javascript:void(0);" class="link-default me-2"><i class="ti ti-clock-share"></i></a>
                                            <span class="fs-10 fw-medium d-inline-flex align-items-center badge badge-{{ $attendance->status == 'regular' ? 'success' : ($attendance->status == 'late' ? 'danger' : 'warning') }}">
                                                <i class="ti ti-circle-filled fs-5 me-1"></i>{{ \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') }}
                                            </span>
                                        </div>
                                    </div>
                                    
                                    @if($attendance->clock_in && $attendance->clock_out)
                                        <div class="d-flex align-items-center justify-content-between flex-wrap mt-2 border br-5 p-2 pb-0">
                                            <div>
                                                <p class="mb-1 d-inline-flex align-items-center"><i class="ti ti-circle-filled text-success fs-5 me-1"></i>Clock In</p>
                                                <h6 class="fs-13 fw-normal mb-2">{{ \Carbon\Carbon::parse($attendance->clock_in)->format('h:i A') }}</h6>
                                            </div>
                                            <div>
                                                <p class="mb-1 d-inline-flex align-items-center"><i class="ti ti-circle-filled text-danger fs-5 me-1"></i>Clock Out</p>
                                                <h6 class="fs-13 fw-normal mb-2">{{ \Carbon\Carbon::parse($attendance->clock_out)->format('h:i A') }}</h6>
                                            </div>
                                            <div>
                                                <p class="mb-1 d-inline-flex align-items-center"><i class="ti ti-circle-filled text-warning fs-5 me-1"></i>Duration</p>
                                                @php
                                                    $duration = \Carbon\Carbon::parse($attendance->clock_in)->diffInHours(\Carbon\Carbon::parse($attendance->clock_out));
                                                    $minutes = \Carbon\Carbon::parse($attendance->clock_in)->diffInMinutes(\Carbon\Carbon::parse($attendance->clock_out)) % 60;
                                                @endphp
                                                <h6 class="fs-13 fw-normal mb-2">{{ $duration }}:{{ str_pad($minutes, 2, '0', STR_PAD_LEFT) }} Hrs</h6>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @empty
                                <div class="p-3 text-center">
                                    <p>Absensi tidak ditemukan</p>
                                </div>
                            @endforelse
                            
                            <!-- Late Employees Section -->
                            @if(count($lateAttendances) > 0)
                                <h6 class="mb-2">Telat</h6>
                                @foreach($lateAttendances as $lateAttendance)
                                    <div class="d-flex align-items-center justify-content-between mb-3 p-2 border border-dashed br-5">
                                        <div class="d-flex align-items-center">
                                            <span class="avatar flex-shrink-0">
                                                <img src="{{ $lateAttendance->user->profile->avatar_url ?? '/assets/media/avatars/blank.png' }}" class="rounded-circle border border-2" alt="img">
                                            </span>
                                            <div class="ms-2">
                                                <h6 class="fs-14 fw-medium text-truncate">
                                                    {{ $lateAttendance->user->name }}
                                                    @php
                                                        $lateMinutes = \Carbon\Carbon::parse($lateAttendance->clock_in)->diffInMinutes(\Carbon\Carbon::parse($lateAttendance->expected_clock_in));
                                                    @endphp
                                                    <span class="fs-10 fw-medium d-inline-flex align-items-center badge badge-success">
                                                        <i class="ti ti-clock-hour-11 me-1"></i>{{ $lateMinutes }} Min
                                                    </span>
                                                </h6>
                                                <p class="fs-13">
                                                    @foreach ($lateAttendance->user->roles as $role)
                                                        {{ $role->name }}
                                                    @endforeach
                                                </p>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-center">
                                            <a href="javascript:void(0);" class="link-default me-2"><i class="ti ti-clock-share"></i></a>
                                            <span class="fs-10 fw-medium d-inline-flex align-items-center badge badge-danger">
                                                <i class="ti ti-circle-filled fs-5 me-1"></i>{{ \Carbon\Carbon::parse($lateAttendance->clock_in)->format('H:i') }}
                                            </span>
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                            
                            <a href="{{ route('attendances.index') }}" class="btn btn-light btn-md w-100">Lihat absensi selengkapnya</a>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <div class="row">

            <!-- Jobs Applicants -->
            <div class="col-xxl-6 d-flex">
                <div class="card flex-fill">
                    <div class="card-header pb-2 d-flex align-items-center justify-content-between flex-wrap">
                        <h5 class="mb-2">Pelamar Pekerjaan</h5>
                        <a href="{{ route('careers.index') }}" class="btn btn-light btn-md mb-2">Lihat semua</a>
                    </div>
                    <div class="card-body">
                        <ul class="nav nav-tabs tab-style-1 nav-justified d-sm-flex d-block p-0 mb-4" role="tablist">
                            <li class="nav-item" role="presentation">
                                <a class="nav-link fw-medium" data-bs-toggle="tab" data-bs-target="#openings" aria-current="page" href="#openings" aria-selected="true" role="tab">Lowongan</a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link fw-medium active" data-bs-toggle="tab" data-bs-target="#applicants" href="#applicants" aria-selected="false" tabindex="-1" role="tab">Pelamar</a>
                            </li>
                        </ul>
                        <div class="tab-content">
                            <div class="tab-pane fade" id="openings">
                                @forelse($latestJobs as $job)
                                    <div class="d-flex align-items-center justify-content-between {{ !$loop->last ? 'mb-4' : 'mb-0' }}">
                                        <div class="d-flex align-items-center">
                                            <div class="ms-2 overflow-hidden">
                                                <p class="text-dark fw-medium text-truncate mb-0"><a href="{{ route('careers.show', $job->id) }}">{{ $job->name }}</a></p>
                                                <span class="fs-12">No of Openings: {{ $job->name }}</span>
                                            </div>
                                        </div>
                                        <a href="{{ route('careers.edit', $job->id) }}" class="btn btn-light btn-sm p-0 btn-icon d-flex align-items-center justify-content-center"><i class="ti ti-edit"></i></a>
                                    </div>
                                @empty
                                    <div class="text-center p-3">
                                        <p>No job openings available</p>
                                    </div>
                                @endforelse
                            </div>
                            <div class="tab-pane fade show active" id="applicants">
                                @forelse($latestApplicants as $applicant)
                                    <div class="d-flex align-items-center justify-content-between {{ !$loop->last ? 'mb-4' : 'mb-0' }}">
                                        <div class="d-flex align-items-center">
                                            <a href="{{ route('users.account', $applicant->user->id) }}" class="avatar overflow-hidden flex-shrink-0">
                                                <img src="{{ $applicant->user->profile->avatar_url ?? '/assets/media/avatars/blank.png' }}" class="img-fluid rounded-circle" alt="img">
                                            </a>
                                            <div class="ms-2 overflow-hidden">
                                                <p class="text-dark fw-medium text-truncate mb-0"><a href="{{ route('users.account', $applicant->user->id) }}">{{ $applicant->user->name }}</a></p>
                                                <span class="fs-13 d-inline-flex align-items-center">Applied: {{ $applicant->career->name }}<i class="ti ti-circle-filled fs-4 mx-2 text-primary"></i>{{ $applicant->career->location ?? 'Indonesia' }}</span>
                                            </div>
                                        </div>
                                        <span class="badge badge-{{ ['secondary', 'info', 'pink', 'purple', 'success', 'warning'][rand(0, 5)] }} badge-xs">{{ $applicant->career->title ?? 'Applicant' }}</span>
                                    </div>
                                @empty
                                    <div class="text-center p-3">
                                        <p>pelamar tidak tersedia</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /Jobs Applicants -->
            
            <!-- Employees -->
            <div class="col-xxl-6 col-xl-6 d-flex">
                <div class="card flex-fill">
                    <div class="card-header pb-2 d-flex align-items-center justify-content-between flex-wrap">
                        <h5 class="mb-2">Employees</h5>
                        <a href="{{ route('employees.index') }}" class="btn btn-light btn-md mb-2">Lihat semua</a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">	
                            <table class="table table-nowrap mb-0">
                                <thead>
                                    <tr>
                                        <th>Nama</th>
                                        <th>Jabatan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($recentEmployees as $employee)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <a href="{{ route('employees.show', $employee->id) }}" class="avatar">
                                                        <img src="{{ $employee->profile->avatar_url ?? '/assets/media/avatars/blank.png' }}" class="img-fluid rounded-circle" alt="img">
                                                    </a>
                                                    <div class="ms-2">
                                                        <h6 class="fw-medium"><a href="{{ route('employees.show', $employee->id) }}">{{ $employee->name }}</a></h6>
                                                        <span class="fs-12">{{ $employee->employee_nik ?? 'Employee' }}</span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge badge-primary badge-xs">
                                                    {{ $employee->roles->first()->name ?? 'Employee' }}
                                                </span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="2" class="text-center">No employees found</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>


    </div>


</div>
@endsection

@push('js')
<script>
    $(document).ready(function() {
        // Get roles data from the PHP variable passed to the view
        var rolesData = @json($rolesData);
        
        renderRolesChart(rolesData);
        
        function renderRolesChart(data) {
            if (!data || !data.length) {
                $("#roles-chart").html("<p class='text-center'>No data available</p>");
                return;
            }
            
            // Sort data by count for better visualization
            data.sort((a, b) => b.count - a.count);
            
            var options = {
                series: [{
                    name: 'Users',
                    data: data.map(item => item.count)
                }],
                chart: {
                    type: 'bar',
                    height: 250,
                    toolbar: {
                        show: false
                    }
                },
                plotOptions: {
                    bar: {
                        horizontal: true,  // Changed to horizontal bar chart
                        borderRadius: 4,
                        distributed: false,
                        dataLabels: {
                            position: 'bottom'
                        },
                    }
                },
                dataLabels: {
                    enabled: true,
                    textAnchor: 'start',
                    style: {
                        colors: ['#333']
                    },
                    formatter: function(val) {
                        return val;
                    },
                    offsetX: 0
                },
                stroke: {
                    width: 1,
                    colors: ['transparent']
                },
                xaxis: {
                    categories: data.map(item => item.name),
                    labels: {
                        style: {
                            fontSize: '12px'
                        }
                    }
                },
                yaxis: {
                    
                },
                fill: {
                    opacity: 1,
                    colors: ['#FE5B24']
                },
                tooltip: {
                    y: {
                        formatter: function (val) {
                            return val + " users"
                        }
                    }
                },
                responsive: [{
                    breakpoint: 480,
                    options: {
                        chart: {
                            height: 350
                        },
                        legend: {
                            position: 'bottom'
                        }
                    }
                }]
            };
    
            var chart = new ApexCharts(document.querySelector("#roles-chart"), options);
            chart.render();
        }
    });
</script>

<script>
    $(document).ready(function() {
        var canvas = document.getElementById('attendance');
        canvas.style.maxWidth = '200px'; 
        canvas.style.maxHeight = '200px';
        canvas.style.margin = '0 auto'; 

        var attendanceData = {
            datasets: [{
                data: [
                    {{ $attendanceStats['present_count'] }}, 
                    {{ $attendanceStats['late_count'] }}, 
                    {{ $attendanceStats['permission_count'] }}, 
                    {{ $attendanceStats['absent_count'] }}
                ],
                backgroundColor: [
                    '#28a745', // Success/Present
                    '#6c757d', // Secondary/Late
                    '#ffc107', // Warning/Permission
                    '#dc3545'  // Danger/Absent
                ],
                borderWidth: 0,
                cutout: '70%'
            }]
        };

        var attendanceConfig = {
            type: 'doughnut',
            data: attendanceData,
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                var labels = ['Absen', 'Telat', 'Izin', 'Alpha'];
                                var percentages = [
                                    {{ $attendanceStats['present_percentage'] }},
                                    {{ $attendanceStats['late_percentage'] }},
                                    {{ $attendanceStats['permission_percentage'] }},
                                    {{ $attendanceStats['absent_percentage'] }}
                                ];
                                var label = labels[context.dataIndex] || '';
                                var percentage = percentages[context.dataIndex] || 0;
                                return label + ': ' + percentage + '%';
                            }
                        }
                    }
                }
            }
        };

        var ctx = canvas.getContext('2d');
        var attendanceChart = new Chart(ctx, attendanceConfig);

        $('.attendance-canvas').css({
            'top': '50%',
            'left': '50%',
            'transform': 'translate(-50%, -50%)',
            'width': '80%'  // Make the text area smaller
        });
        
        $('.attendance-canvas p').css('font-size', '11px');
        $('.attendance-canvas h3').css('font-size', '16px');
    });
</script>
<!-- Chart JS -->
<script src="/admin/assets/plugins/apexchart/apexcharts.min.js"></script>
<script src="/admin/assets/plugins/apexchart/chart-data.js"></script>

<!-- Chart JS -->
<script src="/admin/assets/plugins/chartjs/chart.min.js"></script>
<script src="/admin/assets/plugins/chartjs/chart-data.js"></script>
@endpush
