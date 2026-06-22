@extends('admin.layouts.main')

@section('content')
    <div class="page-wrapper">
        <div class="content">

            <!-- Breadcrumb -->
            <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
                <div class="my-auto mb-2">
                    <h6 class="fw-medium d-inline-flex align-items-center mb-3 mb-sm-0"><a
                            href="{{ route('admins.index') }}">
                            <i class="ti ti-arrow-left me-2"></i>Data Pegawai</a>
                    </h6>
                </div>
            </div>
            <!-- /Breadcrumb -->

            <div class="row">
                <div class="col-xl-4">
                    <div class="card card-bg-1">
                        <div class="card-body p-0">
                            <span class="avatar avatar-xl avatar-rounded border border-2 border-white m-auto d-flex mb-2">
                                <img src="{{ $user->profile['avatar_url'] ?? '/assets/media/avatars/blank.png' }}"
                                    class="w-auto h-auto" alt="Img">
                            </span>
                            <div class="text-center px-3 pb-3 border-bottom">
                                <div class="mb-3">
                                    <h5 class="d-flex align-items-center justify-content-center mb-1">{{ $user->name }}<i
                                            class="ti ti-discount-check-filled text-success ms-1"></i></h5>
                                    @if ($user->profile?->face_id == null)
                                        <span class="badge rounded-pill bg-outline-danger"><i
                                                class="ti ti-point-filled me-1"></i>belum melakukan verifikasi muka</span>
                                        <br>
                                        <br>
                                    @endif
                                    @if (!empty($user->getRoleNames()))
                                        @foreach ($user->getRoleNames() as $v)
                                            <span class="badge badge-soft-dark fw-medium me-2">
                                                <i class="ti ti-point-filled me-1"></i>
                                                {{ $v }}
                                            </span>
                                        @endforeach
                                    @endif
                                </div>
                                <div>
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <span class="d-inline-flex align-items-center">
                                            <i class="ti ti-id me-2"></i>
                                            NIK Karyawan
                                        </span>
                                        <p class="text-dark">{{ $user->employee_nik ?? '-' }}</p>
                                    </div>
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <span class="d-inline-flex align-items-center">
                                            <i class="ti ti-phone me-2"></i>
                                            Phone
                                        </span>
                                        <p class="text-dark">{{ $user->phone ?? '-' }}</p>
                                    </div>
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <span class="d-inline-flex align-items-center">
                                            <i class="ti ti-mail-check me-2"></i>
                                            Email
                                        </span>
                                        <a href="javascript:void(0);"
                                            class="text-info d-inline-flex align-items-center">{{ $user->email ?? '-' }}<i
                                                class="ti ti-copy text-dark ms-2"></i></a>
                                    </div>
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <span class="d-inline-flex align-items-center">
                                            <i class="ti ti-id me-2"></i>
                                            NIK
                                        </span>
                                        <p class="text-dark text-end">{{ $user->nik ?? '-' }}</p>
                                    </div>
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <span class="d-inline-flex align-items-center">
                                            <i class="ti ti-map-pin-check me-2"></i>
                                            Area Poject
                                        </span>
                                        <p class="text-dark text-end">{{ $user->site->name ?? '-' }}</p>
                                    </div>
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <span class="d-inline-flex align-items-center">
                                            <i class="ti ti-user me-2"></i>
                                            Atasan
                                        </span>
                                        <p class="text-dark text-end">{{ $user->leader->name ?? '-' }}</p>
                                    </div>
                                    <div class="d-flex align-items-center justify-content-between">
                                        <span class="d-inline-flex align-items-center">
                                            <i class="ti ti-building me-2"></i>
                                            Department
                                        </span>
                                        <p class="text-dark text-end">
                                            @if ($user->department_id == 1)
                                                Head Office
                                            @elseif($user->department_id == 2)
                                                Mobile
                                            @else
                                                Reliver
                                            @endif
                                        </p>
                                    </div>
                                    <div class="mt-4">
                                        <a href="#" class="btn btn-dark w-100" data-bs-toggle="modal"
                                            data-bs-target="#employeeModal"><i class="ti ti-edit me-1"></i>Edit Info</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-8">
                    <div class="card">
                        <div class="card-header">
                            <div class="d-flex align-items-center justify-content-between flex-fill">
                                <h5>Informasi Profil</h5>
                                <div class="d-flex">
                                    <a href="#" class="btn btn-sm btn-outline-primary me-2" data-bs-toggle="modal"
                                        data-bs-target="#ktpScanModal">
                                        <i class="ti ti-scan me-1"></i>Scan KTP
                                    </a>
                                    <a href="#" class="btn btn-icon btn-sm" data-bs-toggle="modal"
                                        data-bs-target="#profileModal">
                                        <i class="ti ti-edit"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <span class="d-inline-flex align-items-center">
                                        Jenis Kelamin
                                    </span>
                                    <h6 class="d-flex align-items-center fw-medium mt-1">{{ $user->profile->gender ?? '' }}
                                    </h6>
                                </div>
                                <div class="col-md-3">
                                    <span class="d-inline-flex align-items-center">
                                        Tempat & Tanggal Lahir
                                    </span>
                                    <h6 class="d-flex align-items-center fw-medium mt-1">
                                        {{ $user->profile->birth_place ?? '' }}, {{ $user->profile->birth_date ?? '' }}
                                    </h6>
                                </div>
                                <div class="col-md-3">
                                    <span class="d-inline-flex align-items-center">
                                        Nama Ibu Kandung
                                    </span>
                                    <h6 class="d-flex align-items-center fw-medium mt-1">
                                        {{ $user->profile->mother_name ?? '' }}</h6>
                                </div>
                                <div class="col-md-3">
                                    <span class="d-inline-flex align-items-center">
                                        No NPWP
                                    </span>
                                    <h6 class="d-flex align-items-center fw-medium mt-1">
                                        {{ $user->profile->npwp_number ?? '' }}</h6>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-md-3">
                                    <span class="d-inline-flex align-items-center">
                                        Status Pernikahan
                                    </span>
                                    <h6 class="d-flex align-items-center fw-medium mt-1">
                                        {{ $user->profile->marriage_status ?? '' }}</h6>
                                </div>
                                <div class="col-md-3">
                                    <span class="d-inline-flex align-items-center">
                                        Alamat
                                    </span>
                                    <h6 class="d-flex align-items-center fw-medium mt-1">
                                        {{ $user->profile->address ?? '' }}</h6>
                                </div>
                                <div class="col-md-3">
                                    <span class="d-inline-flex align-items-center">
                                        Tanggal Join
                                    </span>
                                    <h6 class="d-flex align-items-center fw-medium mt-1">
                                        {{ $user->profile->join_date ?? '' }}</h6>
                                </div>
                                <div class="col-md-3">
                                    <span class="d-inline-flex align-items-center">
                                        Tanggal Resign
                                    </span>
                                    <h6 class="d-flex align-items-center fw-medium mt-1">
                                        {{ $user->profile->resign_date ?? '' }}</h6>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card mt-4">
                        <div class="card-header">
                            <div class="d-flex align-items-center justify-content-between flex-fill">
                                <h5>Informasi BANK</h5>
                                <div class="d-flex">
                                    <a href="#" class="btn btn-icon btn-sm" data-bs-toggle="modal"
                                        data-bs-target="#bankModal">
                                        <i class="ti ti-edit"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <span class="d-inline-flex align-items-center">
                                        Nama Bank
                                    </span>
                                    <h6 class="d-flex align-items-center fw-medium mt-1">
                                        {{ $user->profile->bank_name ?? '' }}</h6>
                                </div>
                                <div class="col-md-4">
                                    <span class="d-inline-flex align-items-center">
                                        Nama Rekening
                                    </span>
                                    <h6 class="d-flex align-items-center fw-medium mt-1">
                                        {{ $user->profile->account_name ?? '' }}</h6>
                                </div>
                                <div class="col-md-4">
                                    <span class="d-inline-flex align-items-center">
                                        No Rekening
                                    </span>
                                    <h6 class="d-flex align-items-center fw-medium mt-1">
                                        {{ $user->profile->account_number ?? '' }}</h6>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card mt-4">
                        <div class="card-header">
                            <div class="d-flex align-items-center justify-content-between flex-fill">
                                <h5>Dokumen</h5>
                                <div class="d-flex">
                                    <a href="#" class="btn btn-icon btn-sm" data-bs-toggle="modal"
                                        data-bs-target="#documentModal">
                                        <i class="ti ti-edit"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                @forelse ($documents as $document)
                                    <div class="col-md-3 mb-4">
                                        <div class="card">
                                            <div class="card-body">
                                                <h6 class="card-title">{{ $document->name }}</h6>
                                                <p class="card-text">{{ $document->description }}</p>
                                                <a href="{{ $document->file_url }}" class="btn btn-primary"
                                                    target="_blank">
                                                    <i class="ti ti-download"></i> Download
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-center">tidak ada dokumen yang di upload</p>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
    <div class="modal fade" id="employeeModal">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <div class="d-flex align-items-center">
                        <h4 class="modal-title me-2">Edit Akun</h4>
                    </div>
                    <button type="button" class="btn-close custom-btn-close" data-bs-dismiss="modal"
                        aria-label="Close">
                        <i class="ti ti-x"></i>
                    </button>
                </div>
                <form class="form" action="{{ route('users.update.account', $user->id) }}" method="POST">
                    @method('PUT')
                    @csrf
                    <div class="modal-body pb-0 ">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Nama<span class="text-danger"> *</span></label>
                                    <input type="text" name="name" id="account_name" class="form-control"
                                        value="{{ $user->name }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Email <span class="text-danger"> *</span></label>
                                    <input type="email" name="email" class="form-control"
                                        value="{{ $user->email }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Employee ID <span class="text-danger"> *</span></label>
                                    <input type="text" name="employee_nik" class="form-control"
                                        value="{{ $user->employee_nik }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">NIK KTP <span class="text-danger"> *</span></label>
                                    <input type="text" name="nik" id="account_nik" class="form-control"
                                        value="{{ $user->nik }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Phone <span class="text-danger"> *</span></label>
                                    <div class="pass-group">
                                        <input type="text" name="phone" class="pass-input form-control"
                                            value="{{ $user->phone }}">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Password <span class="text-danger"> *</span></label>
                                    <div class="pass-group">
                                        <input type="password" name="password" class="pass-input form-control">
                                        <span class="ti toggle-password ti-eye-off"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Leader</label>
                                    <select class="form-control select2" name="leader_id">
                                        <option value="">Choose Leader</option>
                                        @foreach ($users as $item)
                                            <option value="{{ $item->id }}"
                                                {{ $user->leader_id == $item->id ? 'selected' : '' }}>
                                                {{ $item->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3 ">
                                    <label class="form-label">Department <span class="text-danger"> *</span></label>
                                    <div class="pass-group">
                                        <select class="form-select" name="department_id">
                                            <option value="1" {{ $user->department_id == '1' ? 'selected' : '' }}>
                                                Head Office</option>
                                            <option value="2" {{ $user->department_id == '2' ? 'selected' : '' }}>
                                                Mobile</option>
                                            <option value="3" {{ $user->department_id == '3' ? 'selected' : '' }}>
                                                Reliver</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3 ">
                                    <label class="form-label">Site Utama <span class="text-danger"> *</span></label>
                                    <div class="pass-group">
                                        <select class="form-select" name="site_id">
                                            @foreach ($sites as $site)
                                                <option value="{{ $site->id }}"
                                                    {{ $user->site_id == $site->id ? 'selected' : '' }}>
                                                    {{ $site->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3 ">
                                    <label class="form-label">Is Leader<span class="text-danger"> *</span></label>
                                    <div class="form-check form-check-md form-switch me-2">
                                        <input class="form-check-input me-2" type="checkbox" name="has_sign_leader"
                                            {{ $userHasSignLeader->has_sign_leader == 1 ? 'checked' : '' }}>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="mb-3 ">
                                    <label class="form-label">Jabatan <span class="text-danger"> *</span></label>
                                    <div class="pass-group">
                                        <select class="select2" name="roles[]" multiple>
                                            @foreach ($roles as $role)
                                                <option value="{{ $role }}"
                                                    {{ in_array($role, $user->roles->pluck('name')->toArray()) ? 'selected' : '' }}>
                                                    {{ $role }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="mb-3 ">
                                    <label class="form-label">Sites <span class="text-danger"> *</span></label>
                                    <div class="pass-group">
                                        <select class="select2" name="sites[]" multiple>
                                            @foreach ($sites as $site)
                                                <option value="{{ $site->id }}"
                                                    {{ in_array($site->name, $userSites->sites_leader->pluck('name')->toArray()) ? 'selected' : '' }}>
                                                    {{ $site->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="border-bottom mb-3 pb-3">
                                    <h4>Notifications</h4>
                                </div>
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th class="w-75 ps-2 border-0">Modules</th>
                                                <th class="border-0">Push</th>
                                                <th class="border-0">Whatsapp</th>
                                                <th class="border-0">SMS</th>
                                                <th class="pe-0 border-0">Email</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td class="ps-0">
                                                    <h5 class="mb-1 fw-medium">Notifikasi Job Portal</h5>
                                                    <p>Notifikasi pelamar baru</p>
                                                </td>
                                                <td>
                                                    <div class="form-check form-check-md form-switch me-2">
                                                        <input class="form-check-input me-2" type="checkbox"
                                                            name="job_portal_push"
                                                            {{ optional($notificationSettings)->job_portal_push ? 'checked' : '' }}>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="form-check form-check-md form-switch me-2">
                                                        <input class="form-check-input me-2" type="checkbox"
                                                            name="job_portal_whatsapp"
                                                            {{ optional($notificationSettings)->job_portal_whatsapp ? 'checked' : '' }}>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="form-check form-check-md form-switch me-2">
                                                        <input class="form-check-input me-2" type="checkbox"
                                                            name="job_portal_sms"
                                                            {{ optional($notificationSettings)->job_portal_sms ? 'checked' : '' }}>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="form-check form-check-md form-switch me-2">
                                                        <input class="form-check-input me-2" type="checkbox"
                                                            name="job_portal_email"
                                                            {{ optional($notificationSettings)->job_portal_email ? 'checked' : '' }}>
                                                    </div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-light border me-2"
                            data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal fade" id="profileModal">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <div class="d-flex align-items-center">
                        <h4 class="modal-title me-2">Edit Profil</h4></span>
                    </div>
                    <button type="button" class="btn-close custom-btn-close" data-bs-dismiss="modal"
                        aria-label="Close">
                        <i class="ti ti-x"></i>
                    </button>
                </div>
                <form class="form" action="{{ route('users.update.profile', $user->id) }}" method="POST">
                    @csrf
                    <div class="modal-body pb-0 ">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Jenis Kelamin<span class="text-danger"> *</span></label>
                                    <select class="form-select" name="gender" id="profile_gender">
                                        <option>Pilih</option>
                                        <option value="Laki-Laki"
                                            {{ $user->profile?->gender == 'Laki-Laki' ? 'selected' : '' }}>Laki-Laki
                                        </option>
                                        <option value="Perempuan"
                                            {{ $user->profile?->gender == 'Perempuan' ? 'selected' : '' }}>Perempuan
                                        </option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Status Pernikahan<span class="text-danger"> *</span></label>
                                    <select class="form-select" name="marriage_status">
                                        <option>Pilih</option>
                                        @if ($user->profile && isset($user->profile['marriage_status']))
                                            <option value="TK-0"
                                                {{ $user->profile['marriage_status'] == 'TK-0' ? 'selected' : '' }}>TK-0 :
                                                Tidak Kawin (lajang/janda/duda)</option>
                                            <option value="TK-1"
                                                {{ $user->profile['marriage_status'] == 'TK-1' ? 'selected' : '' }}>TK-1 :
                                                Duda/Janda (punya anak 1)</option>
                                            <option value="TK-2"
                                                {{ $user->profile['marriage_status'] == 'TK-2' ? 'selected' : '' }}>TK-2 :
                                                Duda/Janda (punya anak 2)</option>
                                            <option value="TK-3"
                                                {{ $user->profile['marriage_status'] == 'TK-3' ? 'selected' : '' }}>TK-3 :
                                                Duda/Janda (punya anak 3)</option>
                                            <option value="K-0"
                                                {{ $user->profile['marriage_status'] == 'K-0' ? 'selected' : '' }}>K-0 :
                                                Kawin</option>
                                            <option value="K-1"
                                                {{ $user->profile['marriage_status'] == 'K-1' ? 'selected' : '' }}>K-1 :
                                                Kawin (punya anak 1)</option>
                                            <option value="K-2"
                                                {{ $user->profile['marriage_status'] == 'K-2' ? 'selected' : '' }}>K-2 :
                                                Kawin (punya anak 2)</option>
                                            <option value="K-3"
                                                {{ $user->profile['marriage_status'] == 'K-3' ? 'selected' : '' }}>K-3 :
                                                Kawin (punya anak 3)</option>
                                        @else
                                            <option value="TK-0">TK-0 : Tidak Kawin (lajang/janda/duda)</option>
                                            <option value="TK-1">TK-1 : Duda/Janda (punya anak 1)</option>
                                            <option value="TK-2">TK-2 : Duda/Janda (punya anak 2)</option>
                                            <option value="TK-3">TK-3 : Duda/Janda (punya anak 3)</option>
                                            <option value="K-0">K-0 : Kawin</option>
                                            <option value="K-1">K-1 : Kawin (punya anak 1)</option>
                                            <option value="K-2">K-2 : Kawin (punya anak 2)</option>
                                            <option value="K-3">K-3 : Kawin (punya anak 3)</option>
                                        @endif
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Tempat Lahir<span class="text-danger"> *</span></label>
                                    <input type="text" name="birth_place" id="profile_birth_place" class="form-control"
                                        value="{{ $user->profile?->birth_place }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Tanggal Lahir<span class="text-danger"> *</span></label>
                                    <input type="date" name="birth_date" id="profile_birth_date" class="form-control"
                                        value="{{ $user->profile?->birth_date }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Nama Ibu Kandung<span class="text-danger"> *</span></label>
                                    <input type="text" name="mother_name" class="form-control"
                                        value="{{ $user->profile?->mother_name }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">No NPWP<span class="text-danger"> *</span></label>
                                    <input type="text" name="npwp_number" class="form-control"
                                        value="{{ $user->profile?->npwp_number }}">
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label class="form-label">Alamat<span class="text-danger"> *</span></label>
                                    <input type="text" name="address" id="profile_address" class="form-control"
                                        value="{{ $user->profile?->address }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">RT/RW</label>
                                    <input type="text" name="rt_rw" id="profile_rt_rw" class="form-control"
                                        value="{{ $user->profile?->rt_rw }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Kel/Desa</label>
                                    <input type="text" name="kelurahan" id="profile_kelurahan" class="form-control"
                                        value="{{ $user->profile?->kelurahan }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Kecamatan</label>
                                    <input type="text" name="kecamatan" id="profile_kecamatan" class="form-control"
                                        value="{{ $user->profile?->kecamatan }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Tanggal Join<span class="text-danger"> *</span></label>
                                    <input type="date" name="join_date" class="form-control"
                                        value="{{ $user->profile?->join_date }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Tanggal Resign<span class="text-danger"> *</span></label>
                                    <input type="date" name="resign_date" class="form-control"
                                        value="{{ $user->profile?->resign_date }}">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-light border me-2"
                            data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal fade" id="bankModal">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <div class="d-flex align-items-center">
                        <h4 class="modal-title me-2">Edit Informasi Bank</h4></span>
                    </div>
                    <button type="button" class="btn-close custom-btn-close" data-bs-dismiss="modal"
                        aria-label="Close">
                        <i class="ti ti-x"></i>
                    </button>
                </div>
                <form class="form" action="{{ route('users.update.profile', $user->id) }}" method="POST">
                    @csrf
                    <div class="modal-body pb-0 ">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Nama BANK<span class="text-danger"> *</span></label>
                                    <input type="text" name="bank_name" class="form-control"
                                        value="{{ $user->profile?->bank_name }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Nama rekening<span class="text-danger"> *</span></label>
                                    <input type="text" name="account_name" class="form-control"
                                        value="{{ $user->profile?->account_name }}">
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label class="form-label">No rekening<span class="text-danger"> *</span></label>
                                    <input type="text" name="account_number" class="form-control"
                                        value="{{ $user->profile?->account_number }}">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-light border me-2"
                            data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal fade" id="documentModal">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <div class="d-flex align-items-center">
                        <h4 class="modal-title me-2">Tambah Dokumen</h4>
                    </div>
                    <button type="button" class="btn-close custom-btn-close" data-bs-dismiss="modal"
                        aria-label="Close">
                        <i class="ti ti-x"></i>
                    </button>
                </div>
                <div class="text-center mt-4">
                    <p>Hanya menerima hasil <strong>SCAN</strong> <i class="fas fa-check-circle text-success"></i> dan
                        tidak menerima hasil <strong>FOTO</strong> <i class="fas fa-times-circle text-danger"></i></p>
                </div>
                <form class="form" action="{{ route('users.document.store', $user->id) }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body pb-0">
                        <div class="row">
                            <!-- Tipe Dokumen -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Tipe Dokumen<span class="text-danger"> *</span></label>
                                    <select class="form-select" name="name" required>
                                        <option value="">Pilih Tipe Dokumen</option>
                                        <option value="KTP">KTP</option>
                                        <option value="SIM">SIM</option>
                                        <option value="NPWP">NPWP</option>
                                        <option value="IJAZAH">IJAZAH</option>
                                        <option value="KARTU KELUARGA">KARTU KELUARGA</option>
                                        <option value="PAKLARING">PAKLARING</option>
                                        <option value="CERTIFICATE">CERTIFICATE</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Deskripsi Dokumen -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Deskripsi Dokumen<span class="text-danger"> *</span></label>
                                    <input type="text" name="description" class="form-control"
                                        placeholder="Masukkan deskripsi dokumen" required>
                                </div>
                            </div>

                            <!-- Upload File -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">File Dokumen<span class="text-danger"> *</span></label>
                                    <input type="file" name="file" class="form-control" accept=".jpg, .jpeg, .png"
                                        id="file-input" required>
                                </div>
                            </div>

                            <!-- Tanggal Expired -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Tanggal Expired<span class="text-danger"> *</span></label>
                                    <input type="date" name="validate" class="form-control">
                                </div>
                            </div>
                        </div>

                        <!-- Modal Footer -->
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-light border me-2"
                                data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Save</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>s

    <div class="modal fade" id="ktpScanModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <div class="d-flex align-items-center">
                        <h4 class="modal-title me-2">Scan KTP (OCR)</h4>
                    </div>
                    <button type="button" class="btn-close custom-btn-close" data-bs-dismiss="modal"
                        aria-label="Close">
                        <i class="ti ti-x"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row mb-2">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold mb-1">Metode Scan</label>
                            <select id="ktpMethod" class="form-select">
                                <option value="local">OCR Sekarang (Polygon - di perangkat)</option>
                                <option value="openai">OpenAI Vision (akurasi tinggi)</option>
                            </select>
                        </div>
                    </div>
                    <div id="ktpInfoLocal" class="alert alert-info py-2 fs-12">
                        Unggah foto/scan KTP, sesuaikan kotak polygon agar pas di tiap field, lalu klik
                        <strong>Mulai Scan</strong>. Proses OCR berjalan di perangkat Anda.
                    </div>
                    <div id="ktpInfoOpenai" class="alert alert-warning py-2 fs-12 d-none">
                        Gambar KTP dikirim ke server lalu diteruskan ke OpenAI untuk dibaca. Polygon tidak
                        dipakai pada metode ini. Klik <strong>Mulai Scan</strong> setelah mengunggah gambar.
                    </div>

                    <div class="row">
                        <div class="col-md-7">
                            <div class="mb-2">
                                <input type="file" id="ktpFileInput" class="form-control" accept="image/*">
                            </div>
                            <div id="ktpCanvasWrap" class="border rounded position-relative"
                                style="min-height:180px; background:#f6f7f9; overflow:hidden;">
                                <canvas id="ktpCanvas" style="width:100%; display:block; cursor:crosshair;"></canvas>
                                <div id="ktpCanvasPlaceholder" class="text-center text-muted py-5">
                                    <i class="ti ti-photo fs-1"></i>
                                    <div class="mt-2 fs-13">Belum ada gambar KTP</div>
                                </div>
                            </div>
                            <div id="ktpProgress" class="progress mt-2 d-none" style="height:18px;">
                                <div id="ktpProgressBar" class="progress-bar progress-bar-striped progress-bar-animated"
                                    role="progressbar" style="width:0%">0%</div>
                            </div>
                        </div>
                        <div class="col-md-5" id="ktpPolygonPanel">
                            <label class="form-label fw-semibold mb-1">Field aktif (klik untuk atur polygon)</label>
                            <div id="ktpFieldList" class="list-group mb-2" style="max-height:200px; overflow:auto;"></div>
                            <div class="d-flex gap-2 flex-wrap">
                                <button type="button" id="ktpResetTemplate" class="btn btn-sm btn-outline-secondary">
                                    <i class="ti ti-rotate me-1"></i>Reset Template
                                </button>
                                <button type="button" id="ktpSaveTemplate" class="btn btn-sm btn-outline-success">
                                    <i class="ti ti-device-floppy me-1"></i>Simpan Template
                                </button>
                            </div>
                        </div>
                    </div>

                    <hr>
                    <h6 class="fw-semibold">Hasil OCR</h6>
                    <div class="row g-2" id="ktpResultWrap">
                        <div class="col-md-6"><label class="form-label fs-12 mb-0">NIK</label>
                            <input type="text" id="ktpRes_nik" class="form-control form-control-sm"></div>
                        <div class="col-md-6"><label class="form-label fs-12 mb-0">Nama</label>
                            <input type="text" id="ktpRes_name" class="form-control form-control-sm"></div>
                        <div class="col-md-6"><label class="form-label fs-12 mb-0">Jenis Kelamin</label>
                            <input type="text" id="ktpRes_gender" class="form-control form-control-sm"></div>
                        <div class="col-md-6"><label class="form-label fs-12 mb-0">Tempat Lahir</label>
                            <input type="text" id="ktpRes_birth_place" class="form-control form-control-sm"></div>
                        <div class="col-md-6"><label class="form-label fs-12 mb-0">Tanggal Lahir</label>
                            <input type="text" id="ktpRes_birth_date" class="form-control form-control-sm"
                                placeholder="YYYY-MM-DD"></div>
                        <div class="col-md-6"><label class="form-label fs-12 mb-0">RT/RW</label>
                            <input type="text" id="ktpRes_rt_rw" class="form-control form-control-sm"></div>
                        <div class="col-md-12"><label class="form-label fs-12 mb-0">Alamat</label>
                            <input type="text" id="ktpRes_address" class="form-control form-control-sm"></div>
                        <div class="col-md-6"><label class="form-label fs-12 mb-0">Kel/Desa</label>
                            <input type="text" id="ktpRes_kelurahan" class="form-control form-control-sm"></div>
                        <div class="col-md-6"><label class="form-label fs-12 mb-0">Kecamatan</label>
                            <input type="text" id="ktpRes_kecamatan" class="form-control form-control-sm"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-light border me-2" data-bs-dismiss="modal">Tutup</button>
                    <button type="button" id="ktpScanBtn" class="btn btn-dark" disabled>
                        <i class="ti ti-scan me-1"></i>Mulai Scan
                    </button>
                    <button type="button" id="ktpApplyBtn" class="btn btn-primary" disabled>
                        <i class="ti ti-check me-1"></i>Isi ke Form Profil
                    </button>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('js')
    <script>
        $(document).ready(function() {
            $('#employeeModal').on('shown.bs.modal', function() {
                $(this).find('.select2').select2({
                    dropdownParent: $('#employeeModal')
                });
            });
        });
    </script>
    <script>
        $(document).ready(function() {
            $('#mutationModal').on('shown.bs.modal', function() {
                $(this).find('.select2').select2({
                    dropdownParent: $('#mutationModal')
                });
            });
        });
    </script>
    <script src="/admin/assets/js/ktp-ocr.js"></script>
    <script>
        (function () {
            if (!window.KtpOcr) return;

            var canvas = document.getElementById('ktpCanvas');
            var placeholder = document.getElementById('ktpCanvasPlaceholder');
            var fileInput = document.getElementById('ktpFileInput');
            var fieldList = document.getElementById('ktpFieldList');
            var scanBtn = document.getElementById('ktpScanBtn');
            var applyBtn = document.getElementById('ktpApplyBtn');
            var resetBtn = document.getElementById('ktpResetTemplate');
            var saveBtn = document.getElementById('ktpSaveTemplate');
            var progress = document.getElementById('ktpProgress');
            var progressBar = document.getElementById('ktpProgressBar');
            var methodSel = document.getElementById('ktpMethod');
            var polygonPanel = document.getElementById('ktpPolygonPanel');
            var infoLocal = document.getElementById('ktpInfoLocal');
            var infoOpenai = document.getElementById('ktpInfoOpenai');
            var openaiUrl = '{{ route('ktp-ocr.openai') }}';
            var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            var editor = null;
            var sourceCanvas = null;
            var currentFile = null;
            var lastResult = null;

            function applyMethodUi() {
                var isOpenai = methodSel.value === 'openai';
                polygonPanel.classList.toggle('d-none', isOpenai);
                infoLocal.classList.toggle('d-none', isOpenai);
                infoOpenai.classList.toggle('d-none', !isOpenai);
            }
            methodSel.addEventListener('change', applyMethodUi);
            applyMethodUi();

            function fillResults(m) {
                document.getElementById('ktpRes_nik').value = m.nik || '';
                document.getElementById('ktpRes_name').value = m.name || '';
                document.getElementById('ktpRes_gender').value = m.gender || '';
                document.getElementById('ktpRes_birth_place').value = m.birth_place || '';
                document.getElementById('ktpRes_birth_date').value = m.birth_date || '';
                document.getElementById('ktpRes_address').value = m.address || '';
                document.getElementById('ktpRes_rt_rw').value = m.rt_rw || '';
                document.getElementById('ktpRes_kelurahan').value = m.kelurahan || '';
                document.getElementById('ktpRes_kecamatan').value = m.kecamatan || '';
            }

            function buildFieldList() {
                fieldList.innerHTML = '';
                KtpOcr.FIELDS.forEach(function (f, idx) {
                    var btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = 'list-group-item list-group-item-action d-flex align-items-center' +
                        (idx === 0 ? ' active' : '');
                    btn.dataset.key = f.key;
                    var dot = document.createElement('span');
                    dot.style.cssText = 'display:inline-block;width:12px;height:12px;border-radius:3px;margin-right:8px;background:' +
                        (KtpOcr.FIELD_COLORS[f.key] || '#3498db');
                    btn.appendChild(dot);
                    btn.appendChild(document.createTextNode(f.label));
                    btn.addEventListener('click', function () {
                        fieldList.querySelectorAll('.list-group-item').forEach(function (el) {
                            el.classList.remove('active');
                        });
                        btn.classList.add('active');
                        if (editor) editor.setActiveField(f.key);
                    });
                    fieldList.appendChild(btn);
                });
            }

            function setProgress(pct, text) {
                progress.classList.remove('d-none');
                progressBar.style.width = pct + '%';
                progressBar.textContent = text || (pct + '%');
            }

            fileInput.addEventListener('change', function (e) {
                var file = e.target.files && e.target.files[0];
                if (!file) return;
                currentFile = file;
                KtpOcr.loadImageToCanvas(file).then(function (res) {
                    sourceCanvas = res.canvas;
                    placeholder.style.display = 'none';
                    canvas.style.display = 'block';
                    if (!editor) {
                        editor = new KtpOcr.PolygonEditor(canvas, { template: KtpOcr.loadTemplate() });
                        buildFieldList();
                    }
                    editor.setImage(res.image);
                    scanBtn.disabled = false;
                    applyBtn.disabled = true;
                }).catch(function (err) {
                    alert(err.message || 'Gagal memuat gambar');
                });
            });

            function runLocalScan() {
                if (!sourceCanvas || !editor) return;
                scanBtn.disabled = true;
                applyBtn.disabled = true;
                setProgress(2, 'Memuat OCR...');
                KtpOcr.scan(sourceCanvas, editor.getTemplate(), function (phase, value, label) {
                    if (phase === 'field') {
                        setProgress(Math.round(value * 100), 'Membaca ' + (label || ''));
                    } else if (phase === 'ocr') {
                        setProgress(Math.round(value * 100), 'OCR...');
                    }
                }).then(function (result) {
                    lastResult = result;
                    fillResults(result.mapped);
                    setProgress(100, 'Selesai');
                    scanBtn.disabled = false;
                    applyBtn.disabled = false;
                }).catch(function (err) {
                    setProgress(0, 'Gagal');
                    alert(err.message || 'OCR gagal');
                    scanBtn.disabled = false;
                });
            }

            function runOpenaiScan() {
                if (!currentFile) { alert('Unggah gambar KTP terlebih dahulu.'); return; }
                scanBtn.disabled = true;
                applyBtn.disabled = true;
                setProgress(20, 'Mengirim ke OpenAI...');

                var fd = new FormData();
                fd.append('image', currentFile);

                fetch(openaiUrl, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                    body: fd
                }).then(function (resp) {
                    return resp.json().then(function (json) {
                        if (!resp.ok) throw new Error(json.message || 'Gagal memproses');
                        return json;
                    });
                }).then(function (json) {
                    fillResults(json.mapped || {});
                    setProgress(100, 'Selesai');
                    scanBtn.disabled = false;
                    applyBtn.disabled = false;
                }).catch(function (err) {
                    setProgress(0, 'Gagal');
                    alert(err.message || 'OpenAI gagal');
                    scanBtn.disabled = false;
                });
            }

            scanBtn.addEventListener('click', function () {
                if (methodSel.value === 'openai') runOpenaiScan();
                else runLocalScan();
            });

            function setVal(id, val) {
                var el = document.getElementById(id);
                if (el && val) el.value = val;
            }

            applyBtn.addEventListener('click', function () {
                var m = {
                    nik: document.getElementById('ktpRes_nik').value,
                    name: document.getElementById('ktpRes_name').value,
                    gender: document.getElementById('ktpRes_gender').value,
                    birth_place: document.getElementById('ktpRes_birth_place').value,
                    birth_date: document.getElementById('ktpRes_birth_date').value,
                    address: document.getElementById('ktpRes_address').value,
                    rt_rw: document.getElementById('ktpRes_rt_rw').value,
                    kelurahan: document.getElementById('ktpRes_kelurahan').value,
                    kecamatan: document.getElementById('ktpRes_kecamatan').value
                };

                setVal('account_nik', m.nik);
                setVal('account_name', m.name);
                setVal('profile_birth_place', m.birth_place);
                setVal('profile_birth_date', m.birth_date);
                setVal('profile_address', m.address);
                setVal('profile_rt_rw', m.rt_rw);
                setVal('profile_kelurahan', m.kelurahan);
                setVal('profile_kecamatan', m.kecamatan);

                var genderSel = document.getElementById('profile_gender');
                if (genderSel && m.gender) genderSel.value = m.gender;

                bootstrap.Modal.getInstance(document.getElementById('ktpScanModal')).hide();
                var profileModal = new bootstrap.Modal(document.getElementById('profileModal'));
                profileModal.show();
            });

            resetBtn.addEventListener('click', function () {
                if (editor) editor.resetTemplate();
            });

            saveBtn.addEventListener('click', function () {
                if (!editor) return;
                KtpOcr.saveTemplate(editor.getTemplate());
                saveBtn.innerHTML = '<i class="ti ti-check me-1"></i>Tersimpan';
                setTimeout(function () {
                    saveBtn.innerHTML = '<i class="ti ti-device-floppy me-1"></i>Simpan Template';
                }, 1500);
            });

            window.addEventListener('resize', function () {
                if (editor && sourceCanvas) { editor.resize(); editor.draw(); }
            });
        })();
    </script>
@endpush
