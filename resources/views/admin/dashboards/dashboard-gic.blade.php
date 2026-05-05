@extends('admin.layouts.main')

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
                        <h3 class="mb-2">Welcome Back to Ciptakarir, {{ Auth::user()->name }}</h3>
                    </div>
                </div>
                <div class="d-flex align-items-center flex-wrap mb-1">
                    <a href="{{ route('sites.index') }}" class="btn btn-secondary btn-md me-2 mb-2"><i class="ti ti-square-rounded-plus me-1"></i>Tambah Project</a>
                    <a href="{{ route('careers.index') }}" class="btn btn-primary btn-md mb-2"><i class="ti ti-square-rounded-plus me-1"></i>Tambah Lowongan</a>
                </div>
            </div>
        </div>

        <div class="row">

            <div class="col-md-2 d-flex">
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
            <div class="col-md-2 d-flex">
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
            <div class="col-md-2 d-flex">
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
            <div class="col-md-2 d-flex">
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
            <div class="col-md-2 d-flex">
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

        </div>

        <div class="row">
            
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
            
            <div class="col-xxl-4 col-xl-6 d-flex">
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
            <div class="col-xxl-4 col-xl-6 d-flex">
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
        </div>
    </div>


</div>
@endsection
