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
                                        {{ $user->profile->birth_place ?? '' }}, {{ format_tanggal($user->profile?->birth_date, '') }}
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
                                        Alamat KTP
                                    </span>
                                    <h6 class="d-flex align-items-center fw-medium mt-1">
                                        {{ $user->profile->address ?? '' }}</h6>
                                </div>
                                <div class="col-md-3">
                                    <span class="d-inline-flex align-items-center">
                                        Alamat Domisili
                                    </span>
                                    <h6 class="d-flex align-items-center fw-medium mt-1">
                                        {{ $user->profile->current_address ?? '' }}</h6>
                                </div>
                                <div class="col-md-3">
                                    <span class="d-inline-flex align-items-center">
                                        No Kartu Keluarga
                                    </span>
                                    <h6 class="d-flex align-items-center fw-medium mt-1">
                                        {{ $user->profile->no_kk ?? '' }}</h6>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-md-3">
                                    <span class="d-inline-flex align-items-center">
                                        Tanggal Join
                                    </span>
                                    <h6 class="d-flex align-items-center fw-medium mt-1">
                                        {{ format_tanggal($user->profile?->join_date, '') }}</h6>
                                </div>
                                <div class="col-md-3">
                                    <span class="d-inline-flex align-items-center">
                                        Akhir Kontrak
                                    </span>
                                    <h6 class="d-flex align-items-center fw-medium mt-1">
                                        {{ format_tanggal($user->profile?->end_date, '') }}</h6>
                                </div>
                                <div class="col-md-3">
                                    <span class="d-inline-flex align-items-center">
                                        Tanggal Resign
                                    </span>
                                    <h6 class="d-flex align-items-center fw-medium mt-1">
                                        {{ format_tanggal($user->profile?->resign_date, '') }}</h6>
                                </div>
                                <div class="col-md-3">
                                    <span class="d-inline-flex align-items-center">
                                        Status
                                    </span>
                                    <h6 class="d-flex align-items-center fw-medium mt-1">
                                        @if ($user->profile->isResigned())
                                            <span class="badge bg-danger">Resign</span>
                                        @else
                                            <span class="badge bg-success">Aktif</span>
                                        @endif
                                    </h6>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card mt-4">
                        <div class="card-header">
                            <div class="d-flex align-items-center justify-content-between flex-fill">
                                <h5>Kontak Darurat</h5>
                                <div class="d-flex">
                                    <a href="#" class="btn btn-icon btn-sm" data-bs-toggle="modal"
                                        data-bs-target="#emergencyModal">
                                        <i class="ti ti-edit"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <span class="d-inline-flex align-items-center">Nama</span>
                                    <h6 class="d-flex align-items-center fw-medium mt-1">{{ $user->profile->emergency_name ?? '' }}</h6>
                                </div>
                                <div class="col-md-3">
                                    <span class="d-inline-flex align-items-center">No. Telepon</span>
                                    <h6 class="d-flex align-items-center fw-medium mt-1">{{ $user->profile->emergency_phone ?? '' }}</h6>
                                </div>
                                <div class="col-md-3">
                                    <span class="d-inline-flex align-items-center">Hubungan</span>
                                    <h6 class="d-flex align-items-center fw-medium mt-1">{{ $user->profile->emergency_relation ?? '' }}</h6>
                                </div>
                                <div class="col-md-3">
                                    <span class="d-inline-flex align-items-center">Alamat</span>
                                    <h6 class="d-flex align-items-center fw-medium mt-1">{{ $user->profile->emergency_address ?? '' }}</h6>
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
                                                <a href="{{ $document->previewUrl() }}" class="btn btn-primary"
                                                    data-doc-preview data-kind="{{ $document->file_kind }}" data-title="{{ $document->name }}">
                                                    <i class="ti ti-eye"></i> Lihat
                                                </a>
                                                <a href="{{ $document->previewUrl(true, true) }}" class="btn btn-outline-primary" title="Unduh">
                                                    <i class="ti ti-download"></i>
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

                    <div class="card mt-4">
                        <div class="card-body">
                            <div class="contact-grids-tab p-0 mb-3">
                                <ul class="nav nav-underline" id="myTab" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link active" id="info-tab2" data-bs-toggle="tab"
                                            data-bs-target="#basic-info2" type="button" role="tab"
                                            aria-selected="true">Riwayat Karier</button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="address-tab2" data-bs-toggle="tab"
                                            data-bs-target="#address2" type="button" role="tab"
                                            aria-selected="false">Aktifitas</button>
                                    </li>
                                </ul>
                            </div>
                            <div class="tab-content" id="myTabContent3">
                                <div class="tab-pane fade show active" id="basic-info2" role="tabpanel"
                                    aria-labelledby="info-tab2" tabindex="0">
                                    @php $isResigned = $user->profile->isResigned(); @endphp
                                    <div class="d-flex flex-wrap gap-2 justify-content-end mb-3">
                                        <button type="button" class="btn btn-sm btn-info movement-btn" data-type="mutasi"
                                            data-bs-toggle="modal" data-bs-target="#movementModal" @disabled($isResigned)>
                                            <i class="ti ti-arrows-exchange me-1"></i>Mutasi
                                        </button>
                                        <button type="button" class="btn btn-sm btn-success movement-btn" data-type="promosi"
                                            data-bs-toggle="modal" data-bs-target="#movementModal" @disabled($isResigned)>
                                            <i class="ti ti-trending-up me-1"></i>Promosi
                                        </button>
                                        <button type="button" class="btn btn-sm btn-warning movement-btn" data-type="demosi"
                                            data-bs-toggle="modal" data-bs-target="#movementModal" @disabled($isResigned)>
                                            <i class="ti ti-trending-down me-1"></i>Demosi
                                        </button>
                                        @if ($isResigned)
                                            <button type="button" class="btn btn-sm btn-primary movement-btn" data-type="aktif"
                                                data-bs-toggle="modal" data-bs-target="#movementModal">
                                                <i class="ti ti-user-check me-1"></i>Aktifkan Kembali
                                            </button>
                                        @else
                                            <button type="button" class="btn btn-sm btn-danger movement-btn" data-type="resign"
                                                data-bs-toggle="modal" data-bs-target="#movementModal">
                                                <i class="ti ti-logout me-1"></i>Resign
                                            </button>
                                        @endif
                                        <button type="button" class="btn btn-sm btn-outline-dark"
                                            data-bs-toggle="modal" data-bs-target="#contractModal">
                                            <i class="ti ti-file-pencil me-1"></i>Revisi Periode Kontrak
                                        </button>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th>Jenis</th>
                                                    <th>Tanggal</th>
                                                    <th>Dari</th>
                                                    <th>Ke</th>
                                                    <th>Keterangan</th>
                                                    <th>Surat</th>
                                                    <th>Oleh</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($mutations as $mutation)
                                                    <tr>
                                                        <td><span class="badge {{ $mutation->badge_class }}">{{ $mutation->type_label }}</span></td>
                                                        <td>{{ format_tanggal($mutation->date) }}</td>
                                                        <td>{{ $mutation->from_label }}</td>
                                                        <td>{{ $mutation->to_label }}</td>
                                                        <td>{{ $mutation->description ?: '-' }}</td>
                                                        <td>
                                                            @if ($mutation->generate)
                                                                <a href="{{ route('generates.show', $mutation->generate) }}" target="_blank" class="text-nowrap">
                                                                    <i class="ti ti-file-text me-1"></i>{{ $mutation->generate->formatted_letter_number ?? $mutation->generate->letter_number }}
                                                                </a>
                                                            @else
                                                                -
                                                            @endif
                                                        </td>
                                                        <td>{{ $mutation->creator?->name ?? '-' }}</td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="7" class="text-center text-muted">Belum ada riwayat karier.</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="address2" role="tabpanel" aria-labelledby="address-tab2"
                                    tabindex="0">
                                    <div class="row">
                                        <div class="table-responsive">
                                            <table class="table">
                                                <tbody>
                                                    @foreach ($activities as $activity)
                                                        @php
                                                            $properties = json_decode($activity->properties);
                                                            $attributes = (array) ($properties->attributes ?? []);
                                                            $old = (array) ($properties->old ?? []);

                                                            $changedKeys = array_diff_assoc($attributes, $old);
                                                        @endphp

                                                        @if (!empty($changedKeys))
                                                            <tr>
                                                                <td>
                                                                    @if ($activity->description === 'updated')
                                                                        <div class="badge badge-light-warning">
                                                                            {{ $activity->description }}</div>
                                                                    @elseif($activity->description === 'created')
                                                                        <div class="badge badge-light-success">
                                                                            {{ $activity->description }}</div>
                                                                    @elseif($activity->description === 'deleted')
                                                                        <div class="badge badge-light-danger">
                                                                            {{ $activity->description }}</div>
                                                                    @endif
                                                                </td>
                                                                <td>
                                                                    @foreach ($changedKeys as $key => $newValue)
                                                                        {{ $key }}: {{ $newValue }} <br>
                                                                    @endforeach
                                                                </td>
                                                                <td>
                                                                    @if (!empty($old))
                                                                        @foreach ($changedKeys as $key => $newValue)
                                                                            @if (array_key_exists($key, $old))
                                                                                {{ $key }}: {{ $old[$key] }}
                                                                                <br>
                                                                            @endif
                                                                        @endforeach
                                                                    @endif
                                                                </td>
                                                                <td>{{ $activity->created_at->format('d-m-Y') }}</td>
                                                            </tr>
                                                        @endif
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
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
                                    <input type="text" name="name" class="form-control"
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
                                    <input type="text" name="nik" class="form-control" id="account_nik"
                                        inputmode="numeric" pattern="\d{16}" minlength="16" maxlength="16"
                                        title="NIK KTP wajib 16 digit angka" {{ $user->is_employee ? 'required' : '' }}
                                        value="{{ old('nik', $user->nik) }}">
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
                                    <select class="form-select" name="gender">
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
                                    <input type="text" name="birth_place" class="form-control"
                                        value="{{ $user->profile?->birth_place }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Tanggal Lahir<span class="text-danger"> *</span></label>
                                    <input type="date" name="birth_date" class="form-control"
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
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">No Kartu Keluarga (KK)</label>
                                    <input type="text" name="no_kk" class="form-control"
                                        inputmode="numeric" pattern="\d{16}" minlength="16" maxlength="16"
                                        title="No KK wajib 16 digit angka" value="{{ old('no_kk', $user->profile?->no_kk) }}">
                                </div>
                            </div>
                            <div class="col-md-12 d-none" id="ocrAccountNotice">
                                <div class="alert alert-warning py-2 small mb-3">
                                    NIK / Nama hasil Scan KTP sudah diisikan ke form <strong>Edit Info</strong>.
                                    Simpan juga form tersebut agar NIK / Nama ikut tersimpan.
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label class="form-label">Alamat<span class="text-danger"> *</span></label>
                                    <input type="text" name="address" class="form-control"
                                        value="{{ $user->profile?->address }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">RT/RW</label>
                                    <input type="text" name="rt_rw" class="form-control" value="{{ $user->profile?->rt_rw }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Kelurahan/Desa</label>
                                    <input type="text" name="kelurahan" class="form-control" value="{{ $user->profile?->kelurahan }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Kecamatan</label>
                                    <input type="text" name="kecamatan" class="form-control" value="{{ $user->profile?->kecamatan }}">
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
                                    <input type="text" name="bank_name" class="form-control" required
                                        value="{{ old('bank_name', $user->profile?->bank_name) }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Nama rekening<span class="text-danger"> *</span></label>
                                    <input type="text" name="account_name" class="form-control" required
                                        value="{{ old('account_name', $user->profile?->account_name) }}">
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label class="form-label">No rekening<span class="text-danger"> *</span></label>
                                    <input type="text" name="account_number" class="form-control" required
                                        inputmode="numeric" pattern="[0-9 .\-]{5,25}" title="No rekening hanya angka"
                                        value="{{ old('account_number', $user->profile?->account_number) }}">
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
    <div class="modal fade" id="emergencyModal">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <div class="d-flex align-items-center">
                        <h4 class="modal-title me-2">Edit Kontak Darurat</h4>
                    </div>
                    <button type="button" class="btn-close custom-btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="ti ti-x"></i>
                    </button>
                </div>
                <form class="form" action="{{ route('users.update.profile', $user->id) }}" method="POST">
                    @csrf
                    <div class="modal-body pb-0">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Nama Kontak Darurat</label>
                                    <input type="text" name="emergency_name" class="form-control"
                                        value="{{ $user->profile?->emergency_name }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">No. Telepon</label>
                                    <input type="text" name="emergency_phone" class="form-control"
                                        value="{{ $user->profile?->emergency_phone }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Hubungan</label>
                                    <input type="text" name="emergency_relation" class="form-control"
                                        value="{{ $user->profile?->emergency_relation }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Alamat</label>
                                    <input type="text" name="emergency_address" class="form-control"
                                        value="{{ $user->profile?->emergency_address }}">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-light border me-2" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save</button>
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
                                        <option value="SKCK">SKCK</option>
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
    </div>
    <div class="modal fade" id="movementModal">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="movementTitle">Proses Karier</h4>
                    <button type="button" class="btn-close custom-btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="ti ti-x"></i>
                    </button>
                </div>
                <form class="form" action="{{ route('users.mutation.store', $user->id) }}" method="POST">
                    @csrf
                    <input type="hidden" name="type" id="movement_type" value="{{ old('type', 'mutasi') }}">
                    <div class="modal-body pb-0">
                        <div class="row">
                            <div class="col-md-6 movement-group" data-for="mutasi">
                                <div class="mb-3">
                                    <label class="form-label">Site Saat Ini</label>
                                    <input type="text" class="form-control" value="{{ $user->site->name ?? '-' }}" readonly>
                                </div>
                            </div>
                            <div class="col-md-6 movement-group" data-for="mutasi">
                                <div class="mb-3">
                                    <label class="form-label">Site Tujuan<span class="text-danger"> *</span></label>
                                    <select class="form-control select2" name="to">
                                        <option value="">Pilih site tujuan</option>
                                        @foreach ($sites as $site)
                                            @continue($site->id == $user->site_id)
                                            <option value="{{ $site->id }}" @selected(old('to') == $site->id)>{{ $site->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6 movement-group" data-for="promosi demosi">
                                <div class="mb-3">
                                    <label class="form-label">Jabatan Saat Ini</label>
                                    @php $currentRoles = $user->getRoleNames()->reject(fn ($r) => $r === 'App Administrator')->values(); @endphp
                                    @if ($currentRoles->count() > 1)
                                        <select class="form-select" name="from_role">
                                            @foreach ($currentRoles as $role)
                                                <option value="{{ $role }}">{{ $role }}</option>
                                            @endforeach
                                        </select>
                                        <small class="text-muted">Pilih jabatan yang digantikan.</small>
                                    @else
                                        <input type="text" class="form-control" value="{{ $currentRoles->first() ?? '-' }}" readonly>
                                        <input type="hidden" name="from_role" value="{{ $currentRoles->first() }}">
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6 movement-group" data-for="promosi demosi">
                                <div class="mb-3">
                                    <label class="form-label">Jabatan Baru<span class="text-danger"> *</span></label>
                                    <select class="form-control select2" name="to_role">
                                        <option value="">Pilih jabatan baru</option>
                                        @foreach ($roleOptions as $role)
                                            <option value="{{ $role }}" @selected(old('to_role') === $role)>{{ $role }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-12 movement-group" data-for="resign">
                                <div class="alert alert-warning py-2">
                                    Karyawan akan berstatus <strong>Resign</strong> per tanggal efektif dan tidak lagi muncul di daftar karyawan aktif.
                                </div>
                            </div>
                            <div class="col-md-12 movement-group" data-for="aktif">
                                <div class="alert alert-info py-2">
                                    Tanggal resign ({{ format_tanggal($user->profile?->resign_date) }}) akan dihapus dan karyawan kembali berstatus <strong>Aktif</strong>.
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Tanggal Efektif<span class="text-danger"> *</span></label>
                                    <input type="date" name="date" class="form-control" value="{{ old('date', now()->toDateString()) }}" required>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label class="form-label">Keterangan / Alasan</label>
                                    <textarea name="description" class="form-control" rows="2">{{ old('description') }}</textarea>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <hr class="mt-0">
                                <h6 class="mb-2"><i class="ti ti-file-text me-1 text-primary"></i>Surat Terbit</h6>
                                <div class="mb-3">
                                    <label class="form-label">Template Surat<span class="text-danger" id="movement_letter_required"> *</span></label>
                                    <select class="form-control select2" name="letter_id" id="movement_letter">
                                        <option value="">Pilih template surat</option>
                                        @foreach ($movementLetters as $letterOption)
                                            <option value="{{ $letterOption->id }}" @selected(old('letter_id') == $letterOption->id)>
                                                {{ $letterOption->title }}{{ $letterOption->type ? ' - ' . $letterOption->type->name : '' }}{{ $letterOption->site ? ' (' . $letterOption->site->name . ')' : '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted" id="movement_letter_hint">Surat langsung terbit (masuk Surat Terbit & portal karyawan) dengan nomor otomatis.</small>
                                </div>
                                <div id="movement_custom_vars" class="row"></div>
                                <div class="alert alert-light border small mb-3">
                                    <div class="fw-semibold mb-1">Variabel otomatis untuk template surat:</div>
                                    Tambahkan variabel kustom dengan nama berikut di template, nilainya terisi otomatis dari proses ini:
                                    @foreach (\App\Http\Controllers\EmployeeMovementController::AUTO_VARIABLES as $key => $label)
                                        <code title="{{ $label }}">[{{ $key }}]</code>{{ $loop->last ? '.' : ',' }}
                                    @endforeach
                                    Variabel bawaan seperti <code>[area]</code> dan <code>[jabatan]</code> memakai data setelah proses, <code>[mulai]</code> = tanggal efektif.
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-light border me-2" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="contractModal">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Revisi Periode Kontrak</h4>
                    <button type="button" class="btn-close custom-btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="ti ti-x"></i>
                    </button>
                </div>
                @if ($contracts->isEmpty())
                    <div class="modal-body">
                        <div class="alert alert-info mb-0">
                            {{ $user->name }} belum memiliki surat kontrak (surat dengan periode tanggal). Buat surat PKWT terlebih dahulu
                            melalui proses Offering / Generate Surat.
                        </div>
                    </div>
                @else
                    <form class="form" action="{{ route('users.contract.revise', $user->id) }}" method="POST">
                        @csrf
                        <div class="modal-body pb-0">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <label class="form-label">Surat Kontrak<span class="text-danger"> *</span></label>
                                        <select class="form-select" name="generate_id" id="contract_generate" required>
                                            @foreach ($contracts as $contract)
                                                <option value="{{ $contract->id }}"
                                                    data-start="{{ $contract->start_date ? \Illuminate\Support\Carbon::parse($contract->start_date)->toDateString() : '' }}"
                                                    data-end="{{ $contract->end_date ? \Illuminate\Support\Carbon::parse($contract->end_date)->toDateString() : '' }}">
                                                    {{ $contract->formatted_letter_number ?? '-' }} &mdash; {{ $contract->letter->title ?? 'Surat' }}
                                                    ({{ format_tanggal($contract->start_date) }} s/d {{ format_tanggal($contract->end_date, 'selesai') }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Mulai Kontrak<span class="text-danger"> *</span></label>
                                        <input type="date" name="start_date" id="contract_start" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Akhir Kontrak</label>
                                        <input type="date" name="end_date" id="contract_end" class="form-control">
                                        <small class="text-muted">Kosongkan bila tanpa batas waktu.</small>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <label class="form-label">Keterangan / Alasan Revisi</label>
                                        <textarea name="description" class="form-control" rows="2"></textarea>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="alert alert-light border py-2 small">
                                        Surat PKWT akan otomatis memakai periode baru (nomor surat & tanda tangan tetap). Tanggal akhir kontrak di profil karyawan ikut diperbarui.
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-light border me-2" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary">Simpan Revisi</button>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </div>

    @include('admin.partials.ktp-ocr', [
        'ocrTargets' => [
            'nik' => '#account_nik',
            'name' => '#employeeModal [name="name"]',
            'gender' => '#profileModal [name="gender"]',
            'birth_place' => '#profileModal [name="birth_place"]',
            'birth_date' => '#profileModal [name="birth_date"]',
            'address' => '#profileModal [name="address"]',
            'rt_rw' => '#profileModal [name="rt_rw"]',
            'kelurahan' => '#profileModal [name="kelurahan"]',
            'kecamatan' => '#profileModal [name="kecamatan"]',
        ],
        'ocrOpenModal' => 'profileModal',
        'ocrUserId' => $user->id,
    ])

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
            var movementTitles = {
                mutasi: 'Mutasi Karyawan',
                promosi: 'Promosi Jabatan',
                demosi: 'Demosi Jabatan',
                resign: 'Proses Resign',
                aktif: 'Aktifkan Kembali Karyawan'
            };

            var letterRequired = @json(\App\Http\Controllers\EmployeeMovementController::LETTER_REQUIRED);
            var autoVariables = @json(array_keys(\App\Http\Controllers\EmployeeMovementController::AUTO_VARIABLES));
            var oldCustomValues = {!! json_encode((object) old('custom_values', []), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!};
            var customVarsUrl = {!! json_encode(route('get.custom.variables', ['letterId' => '__ID__']), JSON_HEX_TAG | JSON_UNESCAPED_SLASHES) !!};

            // Variabel kustom template surat terpilih: kosongkan untuk variabel otomatis
            function loadMovementCustomVars() {
                var letterId = $('#movement_letter').val();
                var $wrap = $('#movement_custom_vars').empty();
                if (!letterId) return;

                $.getJSON(customVarsUrl.replace('__ID__', letterId), function (vars) {
                    (vars || []).forEach(function (v) {
                        var isAuto = autoVariables.indexOf(v.variable) !== -1;
                        var $col = $('<div class="col-md-6 mb-3"></div>');
                        $('<label class="form-label"></label>').text(v.name || v.variable).appendTo($col);
                        $('<input type="text" class="form-control">')
                            .attr('name', 'custom_values[' + v.id + ']')
                            .attr('placeholder', isAuto ? 'Otomatis dari data proses' : '[' + v.variable + ']')
                            .val(oldCustomValues[v.id] || '')
                            .appendTo($col);
                        if (isAuto) $('<small class="text-muted">Kosongkan untuk mengisi otomatis.</small>').appendTo($col);
                        $col.appendTo($wrap);
                    });
                });
            }
            $('#movement_letter').on('change', loadMovementCustomVars);

            function setMovementType(type) {
                $('#movement_type').val(type);
                $('#movementTitle').text(movementTitles[type] || 'Proses Karier');
                var required = letterRequired.indexOf(type) !== -1;
                $('#movement_letter_required').toggle(required);
                $('#movement_letter_hint').text(required
                    ? 'Wajib: surat langsung terbit (masuk Surat Terbit & portal karyawan) dengan nomor otomatis.'
                    : 'Opsional untuk proses ini.');
                $('#movementModal .movement-group').each(function() {
                    var show = String($(this).data('for')).split(' ').indexOf(type) !== -1;
                    $(this).toggle(show).find('input, select, textarea').prop('disabled', !show);
                });
            }

            $('.movement-btn').on('click', function() {
                setMovementType($(this).data('type'));
            });

            $('#movementModal').on('shown.bs.modal', function() {
                $(this).find('select.select2').select2({
                    dropdownParent: $('#movementModal')
                });
            });

            function fillContractDates() {
                var $opt = $('#contract_generate option:selected');
                $('#contract_start').val($opt.data('start') || '').trigger('change');
                $('#contract_end').val($opt.data('end') || '').trigger('change');
            }
            $('#contract_generate').on('change', fillContractDates);
            $('#contractModal').on('show.bs.modal', fillContractDates);

            setMovementType($('#movement_type').val() || 'mutasi');
            if ($('#movement_letter').val()) loadMovementCustomVars();

            // Scan KTP: NIK & Nama ada di form Edit Info, beri pengingat bila berubah
            document.addEventListener('ktp-ocr:applied', function(e) {
                var changed = (e.detail.nik && e.detail.nik !== @json($user->nik)) ||
                    (e.detail.name && e.detail.name !== @json($user->name));
                $('#ocrAccountNotice').toggleClass('d-none', !changed);
            });

            @if ($errors->any() && old('type'))
                new bootstrap.Modal(document.getElementById('movementModal')).show();
            @endif
        });
    </script>
@endpush
