@extends('website.layouts.app')

@section('content')
    <div class="page-wrapper">
        <div class="content">

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
                                            NIK KTP
                                        </span>
                                        <p class="text-dark text-end">{{ $user->nik ?? '-' }}</p>
                                    </div>
                                    <div class="mt-4">
                                        <a href="#" class="btn btn-dark w-100" data-bs-toggle="modal"
                                            data-bs-target="#employeeModal"><i class="ti ti-edit me-1"></i>Edit Info</a>
                                    </div>
                                    <div class="mt-4">
                                        <a href="{{ route('applicant.profiles.ocr') }}" class="btn btn-primary w-100"><i class="ti ti-scan me-1"></i>OCR Data Diri</a>
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
                                    <span class="d-inline-flex align-items-center">Jenis Kelamin</span>
                                    <h6 class="d-flex align-items-center fw-medium mt-1">{{ $user->profile->gender ?? '-' }}
                                    </h6>
                                </div>
                                <div class="col-md-3">
                                    <span class="d-inline-flex align-items-center">Pendidikan Terakhir</span>
                                    <h6 class="d-flex align-items-center fw-medium mt-1">
                                        {{ $user->profile->last_education ?? '-' }}</h6>
                                </div>
                                <div class="col-md-3">
                                    <span class="d-inline-flex align-items-center">Tempat & Tanggal Lahir</span>
                                    <h6 class="d-flex align-items-center fw-medium mt-1">
                                        {{ $user->profile->birth_place ?? '-' }}, {{ $user->profile->birth_date ?? '-' }}
                                    </h6>
                                </div>
                                <div class="col-md-3">
                                    <span class="d-inline-flex align-items-center">Nama Ibu Kandung</span>
                                    <h6 class="d-flex align-items-center fw-medium mt-1">
                                        {{ $user->profile->mother_name ?? '-' }}</h6>
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-md-3">
                                    <span class="d-inline-flex align-items-center">Status Pernikahan</span>
                                    <h6 class="d-flex align-items-center fw-medium mt-1">
                                        {{ $user->profile->marriage_status ?? '-' }}</h6>
                                </div>
                                <div class="col-md-3">
                                    <span class="d-inline-flex align-items-center">Tinggal Dengan</span>
                                    <h6 class="d-flex align-items-center fw-medium mt-1">
                                        @php
                                            $living = [
                                                'Parent' => 'Orang Tua',
                                                'Spouse' => 'Suami/Istri',
                                                'Family' => 'Keluarga',
                                                'Live Alone' => 'Tinggal Sendiri',
                                            ];
                                            $userLivingWith = $user->profile?->living_with;
                                        @endphp
                                        {{ $living[$userLivingWith] ?? ($userLivingWith ?? '-') }}
                                    </h6>
                                </div>
                                <div class="col-md-3">
                                    <span class="d-inline-flex align-items-center">Anggota Keluarga (Darurat)</span>
                                    <h6 class="d-flex align-items-center fw-medium mt-1">
                                        {{ $user->profile->family_name ?? '-' }}</h6>
                                </div>
                                <div class="col-md-3">
                                    <span class="d-inline-flex align-items-center">No NPWP</span>
                                    <h6 class="d-flex align-items-center fw-medium mt-1">
                                        {{ $user->profile->npwp_number ?? '-' }}</h6>
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-md-12">
                                    <span class="d-inline-flex align-items-center">Alamat KTP</span>
                                    <h6 class="d-flex align-items-center fw-medium mt-1">
                                        {{ $user->profile->address ?? '-' }}</h6>
                                </div>
                                <div class="col-md-12">
                                    <span class="d-inline-flex align-items-center">Alamat Domisili</span>
                                    <h6 class="d-flex align-items-center fw-medium mt-1">
                                        {{ $user->profile->current_address ?? '-' }}</h6>
                                </div>
                            </div>

                            <hr>

                            <div class="row mt-3">
                                <div class="col-md-3">
                                    <span class="d-inline-flex align-items-center">Tinggi & Berat Badan</span>
                                    <h6 class="d-flex align-items-center fw-medium mt-1">
                                        {{ $user->profile->height ?? '-' }} cm / {{ $user->profile->weight ?? '-' }} kg
                                    </h6>
                                </div>
                                <div class="col-md-3">
                                    <span class="d-inline-flex align-items-center">Kondisi Mata</span>
                                    <h6 class="d-flex align-items-center fw-medium mt-1">
                                        {{ $user->profile->eye_condition ?? '-' }}</h6>
                                </div>
                                <div class="col-md-3">
                                    <span class="d-inline-flex align-items-center">Panca Indera</span>
                                    <h6 class="d-flex align-items-center fw-medium mt-1">{{ $user->profile->sense ?? '-' }}
                                    </h6>
                                </div>
                                <div class="col-md-3">
                                    <span class="d-inline-flex align-items-center">Pendengaran</span>
                                    <h6 class="d-flex align-items-center fw-medium mt-1">
                                        {{ $user->profile->hearing ?? '-' }}</h6>
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-md-3">
                                    <span class="d-inline-flex align-items-center">Tato</span>
                                    <h6 class="d-flex align-items-center fw-medium mt-1">
                                        {{ $user->profile->tattoo == 'Present' ? 'Ada' : 'Tidak Ada' }}</h6>
                                </div>
                                <div class="col-md-3">
                                    <span class="d-inline-flex align-items-center">Tindik</span>
                                    <h6 class="d-flex align-items-center fw-medium mt-1">
                                        {{ $user->profile->piercing == 'Present' ? 'Ada' : 'Tidak Ada' }}</h6>
                                </div>
                                <div class="col-md-3">
                                    <span class="d-inline-flex align-items-center">Push Up</span>
                                    <h6 class="d-flex align-items-center fw-medium mt-1">
                                        {{ $user->profile->push_up ?? '0' }} Kali</h6>
                                </div>
                                <div class="col-md-3">
                                    <span class="d-inline-flex align-items-center">Kemampuan PBB</span>
                                    <h6 class="d-flex align-items-center fw-medium mt-1">
                                        <span class="badge {{ $user->profile->pbb ? 'bg-success' : 'bg-danger' }}">
                                            {{ $user->profile->pbb ? 'Normal / Bisa' : 'Abnormal / Tidak Bisa' }}
                                        </span>
                                    </h6>
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-md-4">
                                    <span class="d-inline-flex align-items-center">Gada Pratama</span>
                                    <h6 class="d-flex align-items-center fw-medium mt-1">
                                        <span
                                            class="text-uppercase badge {{ $user->profile->gada_pratama == 'yes' ? 'bg-outline-success' : 'bg-outline-secondary' }} border">
                                            {{ $user->profile->gada_pratama ?? 'no' }}
                                        </span>
                                    </h6>
                                </div>
                                <div class="col-md-4">
                                    <span class="d-inline-flex align-items-center">Gada Madya</span>
                                    <h6 class="d-flex align-items-center fw-medium mt-1">
                                        <span
                                            class="text-uppercase badge {{ $user->profile->gada_madya == 'yes' ? 'bg-outline-success' : 'bg-outline-secondary' }} border">
                                            {{ $user->profile->gada_madya ?? 'no' }}
                                        </span>
                                    </h6>
                                </div>
                                <div class="col-md-4">
                                    <span class="d-inline-flex align-items-center">Gada Utama</span>
                                    <h6 class="d-flex align-items-center fw-medium mt-1">
                                        <span
                                            class="text-uppercase badge {{ $user->profile->gada_utama == 'yes' ? 'bg-outline-success' : 'bg-outline-secondary' }} border">
                                            {{ $user->profile->gada_utama ?? 'no' }}
                                        </span>
                                    </h6>
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-md-12">
                                    <span class="d-inline-flex align-items-center">Keahlian (Skills)</span>
                                    <h6 class="d-flex align-items-center fw-medium mt-1">
                                        {{ $user->profile->skills ?? '-' }}</h6>
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
                                    <span class="d-inline-flex align-items-center">Nama Bank</span>
                                    <h6 class="d-flex align-items-center fw-medium mt-1">
                                        {{ $user->profile->bank_name ?? '-' }}</h6>
                                </div>
                                <div class="col-md-4">
                                    <span class="d-inline-flex align-items-center">Nama Rekening</span>
                                    <h6 class="d-flex align-items-center fw-medium mt-1">
                                        {{ $user->profile->account_name ?? '-' }}</h6>
                                </div>
                                <div class="col-md-4">
                                    <span class="d-inline-flex align-items-center">No Rekening</span>
                                    <h6 class="d-flex align-items-center fw-medium mt-1">
                                        {{ $user->profile->account_number ?? '-' }}</h6>
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
                                        <div class="card shadow-none border">
                                            <div class="card-body p-3">
                                                <h6 class="card-title mb-2">{{ $document->name }}</h6>
                                                <p class="small text-muted mb-3">
                                                    {{ Str::limit($document->description, 50) }}</p>
                                                <a href="{{ $document->file_url }}" class="btn btn-sm btn-primary w-100"
                                                    target="_blank">
                                                    <i class="ti ti-download me-1"></i> Download
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="col-12">
                                        <p class="text-center text-muted">Tidak ada dokumen yang diupload</p>
                                    </div>
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
                <form class="form" action="{{ route('applicants.profiles.update.account') }}" method="POST">
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
                                    <label class="form-label">NIK KTP <span class="text-danger"> *</span></label>
                                    <input type="text" name="nik" class="form-control"
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
                        <h4 class="modal-title me-2">Edit Profil</h4>
                    </div>
                    <button type="button" class="btn-close custom-btn-close" data-bs-dismiss="modal"
                        aria-label="Close">
                        <i class="ti ti-x"></i>
                    </button>
                </div>
                <form class="form" action="{{ route('applicants.profiles.update.profile') }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body pb-0">
                        <div class="row">
                            <div class="col-md-12">
                                <h5>Data Diri</h5>
                            </div>
                            <br>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Pas Foto (Pilih File / Ambil Foto)<span class="text-danger">
                                            *</span></label>
                                    <input type="file" name="avatar" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Jenis Kelamin<span class="text-danger"> *</span></label>
                                    <select class="form-select" name="gender">
                                        <option disabled selected>Pilih</option>
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
                                    <label class="form-label">Pendidikan Terakhir<span class="text-danger">
                                            *</span></label>
                                    <select class="form-select" name="last_education">
                                        <option disabled selected>Pilih Pendidikan</option>
                                        @foreach (['SD', 'SMP', 'SMA/SLTA', 'D3', 'S1', 'S2', 'S3'] as $edu)
                                            <option value="{{ $edu }}"
                                                {{ $user->profile?->last_education == $edu ? 'selected' : '' }}>
                                                {{ $edu }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Status Pernikahan<span class="text-danger"> *</span></label>
                                    <select class="form-select" name="marriage_status">
                                        <option disabled selected>Pilih</option>
                                        <option value="TK-0"
                                            {{ $user->profile?->marriage_status == 'TK-0' ? 'selected' : '' }}>TK-0 : Tidak
                                            Kawin</option>
                                        <option value="TK-1"
                                            {{ $user->profile?->marriage_status == 'TK-1' ? 'selected' : '' }}>TK-1 :
                                            Duda/Janda (Anak 1)</option>
                                        <option value="TK-2"
                                            {{ $user->profile?->marriage_status == 'TK-2' ? 'selected' : '' }}>TK-2 :
                                            Duda/Janda (Anak 2)</option>
                                        <option value="TK-3"
                                            {{ $user->profile?->marriage_status == 'TK-3' ? 'selected' : '' }}>TK-3 :
                                            Duda/Janda (Anak 3)</option>
                                        <option value="K-0"
                                            {{ $user->profile?->marriage_status == 'K-0' ? 'selected' : '' }}>K-0 : Kawin
                                        </option>
                                        <option value="K-1"
                                            {{ $user->profile?->marriage_status == 'K-1' ? 'selected' : '' }}>K-1 : Kawin
                                            (Anak 1)</option>
                                        <option value="K-2"
                                            {{ $user->profile?->marriage_status == 'K-2' ? 'selected' : '' }}>K-2 : Kawin
                                            (Anak 2)</option>
                                        <option value="K-3"
                                            {{ $user->profile?->marriage_status == 'K-3' ? 'selected' : '' }}>K-3 : Kawin
                                            (Anak 3)</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Status Templat Tinggal<span class="text-danger"> *</span></label>
                                    <select class="form-select" name="living_with">
                                        <option value="Parent"
                                            {{ $user->profile?->living_with == 'Parent' ? 'selected' : '' }}>Orang Tua
                                        </option>
                                        <option value="Spouse"
                                            {{ $user->profile?->living_with == 'Spouse' ? 'selected' : '' }}>Suami/Istri
                                        </option>
                                        <option value="Family"
                                            {{ $user->profile?->living_with == 'Family' ? 'selected' : '' }}>Keluarga
                                            Lainnya</option>
                                        <option value="Live Alone"
                                            {{ $user->profile?->living_with == 'Live Alone' ? 'selected' : '' }}>Tinggal
                                            Sendiri</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Tinggi Badan (cm)</label>
                                    <input type="number" step="0.1" name="height" class="form-control"
                                        value="{{ $user->profile?->height }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Berat Badan (kg)</label>
                                    <input type="number" step="0.1" name="weight" class="form-control"
                                        value="{{ $user->profile?->weight }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Kondisi Mata</label>
                                    <select name="eye_condition" class="form-select">
                                        <option value="Normal"
                                            {{ $user->profile?->eye_condition == 'Normal' ? 'selected' : '' }}>Normal
                                        </option>
                                        <option value="Color Blind"
                                            {{ $user->profile?->eye_condition == 'Color Blind' ? 'selected' : '' }}>Buta
                                            Warna</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Bertato?</label>
                                    <select name="tattoo" class="form-select">
                                        <option value="None" {{ $user->profile?->tattoo == 'None' ? 'selected' : '' }}>
                                            Tidak Ada</option>
                                        <option value="Present"
                                            {{ $user->profile?->tattoo == 'Present' ? 'selected' : '' }}>Ada</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Pendengaran</label>
                                    <select name="hearing" class="form-select">
                                        <option value="Normal"
                                            {{ $user->profile?->hearing == 'Normal' ? 'selected' : '' }}>Normal</option>
                                        <option value="Impaired"
                                            {{ $user->profile?->hearing == 'Impaired' ? 'selected' : '' }}>Terganggu
                                        </option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Tindik?</label>
                                    <select name="piercing" class="form-select">
                                        <option value="None"
                                            {{ $user->profile?->piercing == 'None' ? 'selected' : '' }}>Tidak Ada</option>
                                        <option value="Present"
                                            {{ $user->profile?->piercing == 'Present' ? 'selected' : '' }}>Ada</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <h5>Data lainnya</h5>
                            </div>
                            <br>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Nama Ibu Kandung<span class="text-danger"> *</span></label>
                                    <input type="text" name="mother_name" class="form-control"
                                        value="{{ $user->profile?->mother_name }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">No NPWP</label>
                                    <input type="text" name="npwp_number" class="form-control"
                                        value="{{ $user->profile?->npwp_number }}">
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label class="form-label">Alamat KTP<span class="text-danger"> *</span></label>
                                    <input type="text" name="address" class="form-control"
                                        value="{{ $user->profile?->address }}">
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label class="form-label">Alamat Domisili<span class="text-danger"> *</span></label>
                                    <input type="text" name="current_address" class="form-control"
                                        value="{{ $user->profile?->current_address }}">
                                </div>
                            </div>

                            <div class="col-md-12">
                                <h5>Kontak Darurat</h5>
                            </div>
                            <br>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Nama Anggota Keluarga (Darurat)<span class="text-danger"> *</span></label>
                                    <input type="text" name="family_name" class="form-control"
                                        value="{{ $user->profile?->family_name }}"
                                        placeholder="Nama Suami/Istri/Orang Tua" required>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label class="form-label">Apakah Anda Security?<span class="text-danger">
                                            *</span></label>
                                    <select class="form-select" id="is_security" name="is_security">
                                        <option value="no"
                                            {{ $user->profile?->gada_pratama == 'no' && $user->profile?->gada_madya == 'no' && $user->profile?->gada_utama == 'no' ? 'selected' : '' }}>
                                            Bukan Security</option>
                                        <option value="yes"
                                            {{ $user->profile?->gada_pratama != 'no' || $user->profile?->gada_madya != 'no' || $user->profile?->gada_utama != 'no' ? 'selected' : '' }}>
                                            Ya, Saya Security</option>
                                    </select>
                                </div>
                            </div>

                            <div id="security-fields" class="row" style="display: none;">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Push Up (Jumlah)</label>
                                        <input type="number" name="push_up" class="form-control"
                                            value="{{ $user->profile?->push_up }}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Kemampuan PBB</label>
                                        <select name="pbb" class="form-select">
                                            <option value="0" {{ $user->profile?->pbb == 0 ? 'selected' : '' }}>Tidak
                                                Bisa / Abnormal</option>
                                            <option value="1" {{ $user->profile?->pbb == 1 ? 'selected' : '' }}>Bisa /
                                                Normal</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Gada Pratama</label>
                                        <select name="gada_pratama" class="form-select">
                                            <option value="no"
                                                {{ $user->profile?->gada_pratama == 'no' ? 'selected' : '' }}>Tidak Ada
                                            </option>
                                            <option value="yes"
                                                {{ $user->profile?->gada_pratama == 'yes' ? 'selected' : '' }}>Aktif
                                            </option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Gada Madya</label>
                                        <select name="gada_madya" class="form-select">
                                            <option value="no"
                                                {{ $user->profile?->gada_madya == 'no' ? 'selected' : '' }}>Tidak Ada
                                            </option>
                                            <option value="yes"
                                                {{ $user->profile?->gada_madya == 'yes' ? 'selected' : '' }}>Aktif</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Gada Utama</label>
                                        <select name="gada_utama" class="form-select">
                                            <option value="no"
                                                {{ $user->profile?->gada_utama == 'no' ? 'selected' : '' }}>Tidak Ada
                                            </option>
                                            <option value="yes"
                                                {{ $user->profile?->gada_utama == 'yes' ? 'selected' : '' }}>Aktif</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label class="form-label">Keahlian (Skills)</label>
                                    <textarea name="skills" class="form-control" rows="3"
                                        placeholder="Contoh: Bela Diri, Mengemudi, Sertifikasi IT, dll">{{ $user->profile?->skills }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-light border me-2"
                            data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
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
                <form class="form" action="{{ route('applicants.profiles.update.profile') }}" method="POST">
                    @csrf
                    <div class="modal-body pb-0 ">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Nama BANK</label>
                                    <input type="text" name="bank_name" class="form-control"
                                        value="{{ $user->profile?->bank_name }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Nama rekening</label>
                                    <input type="text" name="account_name" class="form-control"
                                        value="{{ $user->profile?->account_name }}">
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label class="form-label">No rekening</label>
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
                <form class="form" action="{{ route('applicants.profiles.document.store') }}" method="POST"
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
                                        <option value="KTP">KTP (wajib)</option>
                                        <option value="SKCK">SKCK (wajib)</option>
                                        <option value="SIM">SIM</option>
                                        <option value="NPWP">NPWP</option>
                                        <option value="IJAZAH">IJAZAH</option>
                                        <option value="KARTU KELUARGA">KARTU KELUARGA (wajib)</option>
                                        <option value="PAKLARING">PAKLARING</option>
                                        <option value="CERTIFICATE">SERTIFIKAT KEAHLIAN/PROFESI</option>
                                        @if ($user->profile?->gada_utama === 'yes')
                                            <option value="GADA PRATAMA">GADA PRATAMA</option> 
                                        @endif
                                        @if ($user->profile?->gada_madya === 'yes')
                                            <option value="GADA MADYA">GADA MADYA</option> 
                                        @endif
                                        @if ($user->profile?->gada_utama === 'yes')
                                            <option value="GADA UTAMA">GADA UTAMA</option> 
                                        @endif
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">File Dokumen<span class="text-danger"> *</span></label>
                                    <input type="file" name="file" class="form-control" accept=".jpg, .jpeg, .png"
                                        id="file-input" required>
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

    <script>
        $(document).ready(function() {
            function toggleSecurityFields() {
                var isSecurity = $('#is_security').val();
                if (isSecurity === 'yes') {
                    $('#security-fields').slideDown();
                } else {
                    $('#security-fields').slideUp();
                    $('#security-fields select').val('no');
                }
            }

            toggleSecurityFields();

            $('#is_security').on('change', function() {
                toggleSecurityFields();
            });
        });
    </script>
@endpush
