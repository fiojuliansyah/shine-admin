@extends('admin.layouts.main')

@section('content')
<div class="page-wrapper">
    <div class="content">
        <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
            <div class="my-auto mb-2">
                <h2 class="mb-1">Detail Resume</h2>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item">Kandidat</li>
                        <li class="breadcrumb-item active" aria-current="page">{{ $user->name }}</li>
                    </ol>
                </nav>
            </div>
            <div class="mb-2">
                <a href="{{ url()->previous() }}" class="btn btn-white d-flex align-items-center">
                    <i class="ti ti-arrow-left me-2"></i> Kembali
                </a>
            </div>
        </div>

        <div class="row">
            <div class="col-xl-4">
                <div class="card">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <img src="{{ $user->profile?->avatar_url ?? '/admin/assets/img/profiles/avatar.jpg' }}" 
                                 class="img-fluid rounded-circle border" style="width: 150px; height: 150px; object-fit: cover;">
                        </div>
                        <h4 class="mb-1">{{ $user->name }}</h4>
                        <p class="text-muted">{{ $applicant->career->name ?? 'Lowongan Pekerjaan' }}</p>
                        <hr>
                        <div class="text-start mb-0">
                            <p class="mb-2"><strong>Email:</strong> <br> {{ $user->email }}</p>
                            <p class="mb-2"><strong>No. Telp:</strong> <br> {{ $user->phone_number ?? '-' }}</p>
                            <p class="mb-2"><strong>NIK Pelamar:</strong> <br> {{ $user->employee_nik ?? '-' }}</p>
                            <p class="mb-0"><strong>No. NPWP:</strong> <br> {{ $user->profile?->npwp_number ?? '-' }}</p>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h5>Proses Status Kandidat</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('applicants.update-status-single', $applicant->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="mb-3">
                                <label class="form-label">Status Saat Ini</label>
                                <span class="badge bg-info-transparent d-block p-2 text-info" style="font-size: 14px;">
                                    {{ $applicant->status->name }}
                                </span>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Pindahkan Ke Status</label>
                                <select class="form-select" name="status_id" required>
                                    <option disabled selected>Pilih Status Baru</option>
                                    @foreach($statuses as $st)
                                        <option value="{{ $st->id }}" {{ $applicant->status_id == $st->id ? 'disabled' : '' }}>
                                            {{ $st->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="ti ti-refresh me-1"></i> Update Status
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-xl-8">
                <div class="card">
                    <div class="card-header">
                        <h5>Informasi Profil</h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="text-muted small">Pendidikan Terakhir</label>
                                <p class="fw-bold">{{ $user->profile->last_education ?? '-' }}</p>
                            </div>
                            <div class="col-md-4">
                                <label class="text-muted small">Jenis Kelamin</label>
                                <p class="fw-bold">{{ $user->profile->gender ?? '-' }}</p>
                            </div>
                            <div class="col-md-4">
                                <label class="text-muted small">Status Pernikahan</label>
                                <p class="fw-bold">{{ $user->profile->marriage_status ?? '-' }}</p>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="text-muted small">Tempat & Tanggal Lahir</label>
                                <p class="fw-bold">{{ $user->profile->birth_place ?? '-' }}, {{ $user->profile->birth_date ?? '-' }}</p>
                            </div>
                            <div class="col-md-4">
                                <label class="text-muted small">Tinggal Dengan</label>
                                <p class="fw-bold">
                                    @php
                                        $livingArr = ['Parent' => 'Orang Tua', 'Spouse' => 'Suami/Istri', 'Family' => 'Keluarga', 'Live Alone' => 'Tinggal Sendiri'];
                                    @endphp
                                    {{ $livingArr[$user->profile->living_with] ?? ($user->profile->living_with ?? '-') }}
                                </p>
                            </div>
                            <div class="col-md-4">
                                <label class="text-muted small">Nama Anggota Keluarga (Darurat)</label>
                                <p class="fw-bold">{{ $user->profile->family_name ?? '-' }}</p>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="text-muted small">Nama Ibu Kandung</label>
                                <p class="fw-bold">{{ $user->profile->mother_name ?? '-' }}</p>
                            </div>
                            <div class="col-md-8">
                                <label class="text-muted small">Alamat Lengkap</label>
                                <p class="fw-bold">{{ $user->profile->address ?? '-' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h5>Fisik & Kesehatan</h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label class="text-muted small">Tinggi / Berat</label>
                                <p class="fw-bold">{{ $user->profile->height ?? '-' }} cm / {{ $user->profile->weight ?? '-' }} kg</p>
                            </div>
                            <div class="col-md-3">
                                <label class="text-muted small">Kondisi Mata</label>
                                <p class="fw-bold">{{ $user->profile->eye_condition ?? '-' }}</p>
                            </div>
                            <div class="col-md-3">
                                <label class="text-muted small">Panca Indera</label>
                                <p class="fw-bold">{{ $user->profile->sense ?? '-' }}</p>
                            </div>
                            <div class="col-md-3">
                                <label class="text-muted small">Pendengaran</label>
                                <p class="fw-bold">{{ $user->profile->hearing ?? '-' }}</p>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label class="text-muted small">Tato</label>
                                <p class="fw-bold">{{ $user->profile->tattoo == 'Present' ? 'Ada' : 'Tidak Ada' }}</p>
                            </div>
                            <div class="col-md-3">
                                <label class="text-muted small">Tindik</label>
                                <p class="fw-bold">{{ $user->profile->piercing == 'Present' ? 'Ada' : 'Tidak Ada' }}</p>
                            </div>
                            <div class="col-md-3">
                                <label class="text-muted small">Push Up</label>
                                <p class="fw-bold">{{ $user->profile->push_up ?? '0' }} Kali</p>
                            </div>
                            <div class="col-md-3">
                                <label class="text-muted small">Kemampuan PBB</label>
                                <p class="fw-bold">
                                    <span class="badge {{ $user->profile->pbb ? 'bg-success' : 'bg-danger' }}">
                                        {{ $user->profile->pbb ? 'Normal / Bisa' : 'Abnormal / Tidak Bisa' }}
                                    </span>
                                </p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <label class="text-muted small">Keahlian (Skills)</label>
                                <p class="fw-bold">{{ $user->profile->skills ?? '-' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                @if($user->profile?->gada_pratama != 'no' || $user->profile?->gada_madya != 'no' || $user->profile?->gada_utama != 'no')
                <div class="card">
                    <div class="card-header">
                        <h5>Sertifikasi Gada (Security)</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <label class="text-muted small">Gada Pratama</label>
                                <h6 class="text-uppercase fw-bold text-primary">{{ $user->profile->gada_pratama }}</h6>
                            </div>
                            <div class="col-md-4">
                                <label class="text-muted small">Gada Madya</label>
                                <h6 class="text-uppercase fw-bold text-primary">{{ $user->profile->gada_madya }}</h6>
                            </div>
                            <div class="col-md-4">
                                <label class="text-muted small">Gada Utama</label>
                                <h6 class="text-uppercase fw-bold text-primary">{{ $user->profile->gada_utama }}</h6>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <div class="card">
                    <div class="card-header border-bottom">
                        <h5>Dokumen Terlampir</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-nowrap mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Nama Dokumen</th>
                                        <th>Deskripsi</th>
                                        <th class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($documents as $doc)
                                    <tr>
                                        <td><h6 class="fw-medium">{{ $doc->name }}</h6></td>
                                        <td>{{ Str::limit($doc->description, 50) }}</td>
                                        <td class="text-center">
                                            <a href="{{ $doc->file_url }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                                <i class="ti ti-download me-1"></i> Download
                                            </a>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="3" class="text-center p-4 text-muted">Tidak ada dokumen yang diunggah.</td>
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