@extends('website.layouts.app')

@section('content')
    <div class="page-wrapper">
        <div class="content">

            <div class="row">
                <div class="col-xl-4">
                    <div class="card card-bg-1">
                        <div class="card-body p-0">
                            <span class="avatar avatar-xl avatar-rounded border border-2 border-white m-auto d-flex mb-2">
                                <img src="{{ $user->profile['avatar_url'] ?? '/assets/media/avatars/blank.png' }}" class="w-auto h-auto" alt="Img">
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
                                    <a href="#" class="btn btn-icon btn-sm" data-bs-toggle="modal" data-bs-target="#profileModal">
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
                                    <h6 class="d-flex align-items-center fw-medium mt-1">{{ $user->profile->gender ?? '' }}</h6>
                                </div>
                                <div class="col-md-3">
                                    <span class="d-inline-flex align-items-center">
                                        Tempat & Tanggal Lahir
                                    </span>
                                    <h6 class="d-flex align-items-center fw-medium mt-1">{{ $user->profile->birth_place ?? '' }}, {{ $user->profile->birth_date ?? '' }}</h6>
                                </div>
                                <div class="col-md-3">
                                    <span class="d-inline-flex align-items-center">
                                        Nama Ibu Kandung
                                    </span>
                                    <h6 class="d-flex align-items-center fw-medium mt-1">{{ $user->profile->mother_name ?? '' }}</h6>
                                </div>
                                <div class="col-md-3">
                                    <span class="d-inline-flex align-items-center">
                                        No NPWP
                                    </span>
                                    <h6 class="d-flex align-items-center fw-medium mt-1">{{ $user->profile->npwp_number ?? '' }}</h6>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-md-3">
                                    <span class="d-inline-flex align-items-center">
                                        Status Pernikahan
                                    </span>
                                    <h6 class="d-flex align-items-center fw-medium mt-1">{{ $user->profile->marriage_status ?? '' }}</h6>
                                </div>
                                <div class="col-md-3">
                                    <span class="d-inline-flex align-items-center">
                                        Alamat
                                    </span>
                                    <h6 class="d-flex align-items-center fw-medium mt-1">{{ $user->profile->address ?? '' }}</h6>
                                </div>
                            </div>
                        </div>
                    </div>
                
                    <div class="card mt-4">
                        <div class="card-header">
                            <div class="d-flex align-items-center justify-content-between flex-fill">
                                <h5>Informasi BANK</h5>
                                <div class="d-flex">
                                    <a href="#" class="btn btn-icon btn-sm" data-bs-toggle="modal" data-bs-target="#bankModal">
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
                                    <h6 class="d-flex align-items-center fw-medium mt-1">{{ $user->profile->bank_name ?? '' }}</h6>
                                </div>
                                <div class="col-md-4">
                                    <span class="d-inline-flex align-items-center">
                                        Nama Rekening
                                    </span>
                                    <h6 class="d-flex align-items-center fw-medium mt-1">{{ $user->profile->account_name ?? '' }}</h6>
                                </div>
                                <div class="col-md-4">
                                    <span class="d-inline-flex align-items-center">
                                        No Rekening
                                    </span>
                                    <h6 class="d-flex align-items-center fw-medium mt-1">{{ $user->profile->account_number ?? '' }}</h6>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card mt-4">
                        <div class="card-header">
                            <div class="d-flex align-items-center justify-content-between flex-fill">
                                <h5>Dokumen</h5>
                                <div class="d-flex">
                                    <a href="#" class="btn btn-icon btn-sm" data-bs-toggle="modal" data-bs-target="#documentModal">
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
                                                <a href="{{ $document->file_url }}" class="btn btn-primary" target="_blank">
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
                                        <input type="text" name="phone" class="pass-input form-control" value="{{ $user->phone }}">
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
                        <h4 class="modal-title me-2">Edit Profil</h4></span>
                    </div>
                    <button type="button" class="btn-close custom-btn-close" data-bs-dismiss="modal"
                        aria-label="Close">
                        <i class="ti ti-x"></i>
                    </button>
                </div>
                <form class="form" action="{{ route('applicants.profiles.update.profile') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body pb-0 ">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Pas Foto<span class="text-danger"> *</span></label>
                                    <input type="file" name="avatar" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Jenis Kelamin<span class="text-danger"> *</span></label>
                                    <select class="form-select" name="gender">
                                        <option>Pilih</option>
                                        <option value="Laki-Laki" {{ $user->profile?->gender == 'Laki-Laki' ? 'selected' : '' }}>Laki-Laki</option>
                                        <option value="Perempuan" {{ $user->profile?->gender == 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Status Pernikahan<span class="text-danger"> *</span></label>
                                    <select class="form-select" name="marriage_status">
                                        <option>Pilih</option>
                                        @if($user->profile && isset($user->profile['marriage_status']))
                                            <option value="TK-0" {{ $user->profile['marriage_status'] == 'TK-0' ? 'selected' : '' }}>TK-0 : Tidak Kawin (lajang/janda/duda)</option>
                                            <option value="TK-1" {{ $user->profile['marriage_status'] == 'TK-1' ? 'selected' : '' }}>TK-1 : Duda/Janda (punya anak 1)</option>
                                            <option value="TK-2" {{ $user->profile['marriage_status'] == 'TK-2' ? 'selected' : '' }}>TK-2 : Duda/Janda (punya anak 2)</option>
                                            <option value="TK-3" {{ $user->profile['marriage_status'] == 'TK-3' ? 'selected' : '' }}>TK-3 : Duda/Janda (punya anak 3)</option>
                                            <option value="K-0" {{ $user->profile['marriage_status'] == 'K-0' ? 'selected' : '' }}>K-0 : Kawin</option>
                                            <option value="K-1" {{ $user->profile['marriage_status'] == 'K-1' ? 'selected' : '' }}>K-1 : Kawin (punya anak 1)</option>
                                            <option value="K-2" {{ $user->profile['marriage_status'] == 'K-2' ? 'selected' : '' }}>K-2 : Kawin (punya anak 2)</option>
                                            <option value="K-3" {{ $user->profile['marriage_status'] == 'K-3' ? 'selected' : '' }}>K-3 : Kawin (punya anak 3)</option>
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
                                    <input type="text" name="birth_place" class="form-control" value="{{ $user->profile?->birth_place }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Tanggal Lahir<span class="text-danger"> *</span></label>
                                    <input type="date" name="birth_date" class="form-control" value="{{ $user->profile?->birth_date }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Nama Ibu Kandung<span class="text-danger"> *</span></label>
                                    <input type="text" name="mother_name" class="form-control" value="{{ $user->profile?->mother_name }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">No NPWP<span class="text-danger"> *</span></label>
                                    <input type="text" name="npwp_number" class="form-control" value="{{ $user->profile?->npwp_number }}">
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label class="form-label">Alamat<span class="text-danger"> *</span></label>
                                    <input type="text" name="address" class="form-control" value="{{ $user->profile?->address }}">
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
                <form class="form" action="{{ route('applicants.profiles.update.profile') }}" method="POST">
                    @csrf
                    <div class="modal-body pb-0 ">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Nama BANK<span class="text-danger"> *</span></label>
                                    <input type="text" name="bank_name" class="form-control" value="{{ $user->profile?->bank_name }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Nama rekening<span class="text-danger"> *</span></label>
                                    <input type="text" name="account_name" class="form-control" value="{{ $user->profile?->account_name }}">
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label class="form-label">No rekening<span class="text-danger"> *</span></label>
                                    <input type="text" name="account_number" class="form-control" value="{{ $user->profile?->account_number }}">
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
                    <button type="button" class="btn-close custom-btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="ti ti-x"></i>
                    </button>
                </div>
                <div class="text-center mt-4">
                    <p>Hanya menerima hasil <strong>SCAN</strong> <i class="fas fa-check-circle text-success"></i> dan tidak menerima hasil <strong>FOTO</strong> <i class="fas fa-times-circle text-danger"></i></p>
                </div>
                <form class="form" action="{{ route('applicants.profiles.document.store') }}" method="POST" enctype="multipart/form-data">
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
                                    <input type="text" name="description" class="form-control" placeholder="Masukkan deskripsi dokumen" required>
                                </div>
                            </div>

                            <!-- Upload File -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">File Dokumen<span class="text-danger"> *</span></label>
                                    <input type="file" name="file" class="form-control" accept=".jpg, .jpeg, .png" id="file-input" required>
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
                            <button type="button" class="btn btn-outline-light border me-2" data-bs-dismiss="modal">Cancel</button>
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
        $('#employeeModal').on('shown.bs.modal', function () {
            $(this).find('.select2').select2({
                dropdownParent: $('#employeeModal')
            });
        });
    });
</script>
<script>
    $(document).ready(function() {
        $('#mutationModal').on('shown.bs.modal', function () {
            $(this).find('.select2').select2({
                dropdownParent: $('#mutationModal')
            });
        });
    });
</script>
@endpush
