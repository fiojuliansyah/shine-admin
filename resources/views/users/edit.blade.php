@extends('layouts.main')

@section('content')
<div class="app-main flex-column flex-row-fluid" id="kt_app_main">
    <div class="d-flex flex-column flex-column-fluid">
        <div id="kt_app_content" class="app-content flex-column-fluid">
            <div id="kt_app_content_container" class="app-container container-fluid">
                <div class="card mb-5 mb-xl-10">
                    <div class="card-body pt-9 pb-0">
                        <div class="d-flex flex-wrap flex-sm-nowrap">
                            <div class="me-7 mb-4">
                                <div class="symbol symbol-100px symbol-lg-160px symbol-fixed position-relative">
                                    <img src="{{ $user->profile['avatar_url'] ?? '/assets/media/avatars/blank.png' }}" alt="image" />
                                    <div class="position-absolute translate-middle bottom-0 start-100 mb-6 bg-success rounded-circle border border-4 border-body h-20px w-20px"></div>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-start flex-wrap mb-2">
                                    <div class="d-flex flex-column">
                                        <div class="d-flex align-items-center mb-2">
                                            <a href="#" class="text-gray-900 text-hover-primary fs-2 fw-bold me-1">{{ $user->name }}</a>
                                            <a href="#">
                                                <i class="ki-outline ki-verify fs-1 text-primary"></i>
                                            </a>
                                        </div>
                                        <div class="d-flex flex-wrap fw-semibold fs-6 mb-4 pe-2">
                                            <a href="#" class="d-flex align-items-center text-gray-500 text-hover-primary me-5 mb-2">
                                            <i class="ki-outline ki-profile-circle fs-4 me-1"></i>
                                            @if(!empty($user->getRoleNames()))
                                                @foreach($user->getRoleNames() as $v)
                                                    {{ $v }}
                                                @endforeach
                                            @endif
                                            </a>
                                            <a href="#" class="d-flex align-items-center text-gray-500 text-hover-primary me-5 mb-2">
                                            <i class="ki-outline ki-geolocation fs-4 me-1"></i>{{ $user->site['name'] ?? 'belum ada site' }}</a>
                                            <a href="#" class="d-flex align-items-center text-gray-500 text-hover-primary mb-2">
                                            <i class="ki-outline ki-sms fs-4"></i>{{ $user->email }}</a>
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex flex-wrap flex-stack">
                                    <div class="d-flex flex-column flex-grow-1 pe-8">
                                        <div class="d-flex flex-wrap">
                                            <div class="border border-gray-300 border-dashed rounded min-w-125px py-3 px-4 me-6 mb-3">
                                                <div class="d-flex align-items-center">
                                                    <i class="ki-solid ki-star fs-3 text-warning me-2"></i>
                                                    <div class="fs-2 fw-bold" data-kt-countup="true" data-kt-countup-value="4500">0</div>
                                                </div>
                                                <div class="fw-semibold fs-6 text-gray-500">Rating</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <ul class="nav nav-stretch nav-line-tabs nav-line-tabs-2x border-transparent fs-5 fw-bold">
                            <li class="nav-item">
                                <a class="nav-link active" data-bs-toggle="tab" href="#kt_tab_pane_1">Account</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#kt_tab_pane_2">Profile</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#kt_tab_pane_3">logs</a>
                            </li>
                        </ul>
                    </div>
                </div>
                
                <div class="tab-content" id="myTabContent">
                    <div class="tab-pane fade show active" id="kt_tab_pane_1" role="tabpanel">
                        <div class="card mb-5 mb-xl-10">
                            <div class="card-header border-0 cursor-pointer" role="button" data-bs-toggle="collapse" data-bs-target="#kt_account_profile_details" aria-expanded="true" aria-controls="kt_account_profile_details">
                                <div class="card-title m-0">
                                    <h3 class="fw-bold m-0">Account Details</h3>
                                </div>
                            </div>
                            <div id="kt_account_settings_profile_details" class="collapse show">
                                <form class="form" action="{{ route('users.update',$user->id) }}" method="POST">
                                    @method('PUT')
                                    @csrf
                                    <div class="card-body border-top p-9">
                                        <div class="row mb-6">
                                            <label class="col-lg-4 col-form-label required fw-semibold fs-6">Nama lengkap</label>
                                            <div class="col-lg-8 fv-row">
                                                <input type="text" name="name" class="form-control form-control-lg form-control-solid" value="{{ $user->name }}" />
                                            </div>
                                        </div>
                                        <div class="row mb-6">
                                            <label class="col-lg-4 col-form-label required fw-semibold fs-6">Email</label>
                                            <div class="col-lg-8 fv-row">
                                                <input type="text" name="email" class="form-control form-control-lg form-control-solid" value="{{ $user->email }}" />
                                            </div>
                                        </div>
                                        <div class="row mb-6">
                                            <label class="col-lg-4 col-form-label required fw-semibold fs-6">NIK KTP</label>
                                            <div class="col-lg-8 fv-row">
                                                <input type="text" name="nik" class="form-control form-control-lg form-control-solid" value="{{ $user->nik }}" />
                                            </div>
                                        </div>
                                        <div class="row mb-6">
                                            <label class="col-lg-4 col-form-label fw-semibold fs-6">
                                                <span class="required">No Handphone</span>
                                                <span class="ms-1" data-bs-toggle="tooltip" title="No Handphone yang Whatsappnya Aktif">
                                                    <i class="ki-outline ki-information-5 text-gray-500 fs-6"></i>
                                                </span>
                                            </label>
                                            <div class="col-lg-8 fv-row">
                                                <input type="tel" name="phone" class="form-control form-control-lg form-control-solid" placeholder="Phone number" value="{{ $user->phone }}" />
                                            </div>
                                        </div>
                                        <div class="row mb-6">
                                            <label class="col-lg-4 col-form-label required fw-semibold fs-6">Password</label>
                                            <div class="col-lg-8 fv-row">
                                                <input type="password" name="password" class="form-control form-control-lg form-control-solid" />
                                            </div>
                                        </div>
                                        <div class="row mb-6">
                                            <label class="col-lg-4 col-form-label fw-semibold fs-6">
                                                <span class="required">Pilih Jabatan</span>
                                                <span class="ms-1" data-bs-toggle="tooltip" title="bisa Pilih Lebih dari 1 Jabatan">
                                                    <i class="ki-outline ki-information-5 text-gray-500 fs-6"></i>
                                                </span>
                                            </label>
                                            <div class="col-lg-8 fv-row">
                                                <select class="form-select form-control-lg form-control-solid" name="roles[]" data-placeholder="Select an option" multiple>
                                                    @foreach($roles as $role)
                                                        <option value="{{ $role }}" {{ in_array($role, $user->roles->pluck('name')->toArray()) ? 'selected' : '' }}>
                                                            {{ $role }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-footer d-flex justify-content-end py-6 px-9">
                                        <button type="reset" class="btn btn-light btn-active-light-primary me-2">Discard</button>
                                        <button type="submit" class="btn btn-primary" id="kt_account_profile_details_submit">Save Changes</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="kt_tab_pane_2" role="tabpanel">
                        <form class="form" action="{{ route('personal-data-user',$user->id) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="card mb-5 mb-xl-10">
                                <div class="card-header border-0 cursor-pointer" role="button" data-bs-toggle="collapse" data-bs-target="#kt_account_profile_details" aria-expanded="true" aria-controls="kt_account_profile_details">
                                    <div class="card-title m-0">
                                        <h3 class="fw-bold m-0">Profile Details</h3>
                                    </div>
                                </div>
                                <div id="kt_account_settings_profile_details" class="collapse show">
                                        <div class="card-body border-top p-9">
                                            <div class="row mb-6">
                                                <label class="col-lg-4 col-form-label fw-semibold fs-6">Avatar</label>
                                                <div class="col-lg-8">
                                                    <div class="image-input image-input-outline" data-kt-image-input="true" style="background-image: url('assets/media/svg/avatars/blank.svg')">
                                                        <div class="image-input-wrapper w-125px h-125px" style="background-image: url({{ Auth::user()->profile['avatar_url'] ?? '/assets/media/avatars/blank.png' }})"></div>
                                                        <label class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="change" data-bs-toggle="tooltip" title="Change avatar">
                                                            <i class="ki-outline ki-pencil fs-7"></i>
                                                            <input type="file" name="avatar" accept=".png, .jpg, .jpeg" />
                                                        </label>
                                                        <span class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="cancel" data-bs-toggle="tooltip" title="Cancel avatar">
                                                            <i class="ki-outline ki-cross fs-2"></i>
                                                        </span>
                                                        <span class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="remove" data-bs-toggle="tooltip" title="Remove avatar">
                                                            <i class="ki-outline ki-cross fs-2"></i>
                                                        </span>
                                                    </div>
                                                    <div class="form-text">Allowed file types: png, jpg, jpeg.</div>
                                                </div>
                                            </div>
                                            <div class="row mb-6">
                                                <label class="col-lg-4 col-form-label required fw-semibold fs-6">NIK Karyawan</label>
                                                <div class="col-lg-8 fv-row">
                                                    <input type="text" name="employee_nik" class="form-control form-control-lg form-control-solid" placeholder="NIK Karyawan" value="{{ $user->profile['employee_nik'] ?? '' }}" />
                                                </div>
                                            </div>
                                            <div class="row mb-6">
                                                <label class="col-lg-4 col-form-label required fw-semibold fs-6">Alamat</label>
                                                <div class="col-lg-8 fv-row">
                                                    <textarea name="address" class="form-control form-control-lg form-control-solid" placeholder="Alamat">{{ $user->profile['address'] ?? '' }}</textarea>
                                                </div>
                                            </div>
                                            <div class="row mb-6">
                                                <label class="col-lg-4 col-form-label required fw-semibold fs-6">Jenis Kelamin</label>
                                                <div class="col-lg-8 fv-row">
                                                    <select class="form-select form-control-lg form-control-solid" name="gender" data-placeholder="Select an option">
                                                        <option>Pilih</option>
                                                        @if($user->profile && isset($user->profile['gender']))
                                                        <option value="Laki-Laki" {{ $user->profile['gender'] == 'Laki-Laki' ? 'selected' : '' }}>Laki-Laki</option>
                                                        <option value="Perempuan" {{ $user->profile['gender'] == 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
                                                        @else
                                                        <option value="Laki-Laki">Laki-Laki</option>
                                                        <option value="Perempuan">Perempuan</option>
                                                        @endif
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="row mb-6">
                                                <label class="col-lg-4 col-form-label required fw-semibold fs-6">Status Pernikahan</label>
                                                <div class="col-lg-8 fv-row">
                                                    <select class="form-select form-control-lg form-control-solid" name="marriage_status" data-placeholder="Select an option">
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
                                            <div class="row mb-6">
                                                <label class="col-lg-4 col-form-label required fw-semibold fs-6">Nama Ibu Kandung</label>
                                                <div class="col-lg-8 fv-row">
                                                    <input type="text" name="mother_name" class="form-control form-control-lg form-control-solid" placeholder="Nama Ibu Kandung" value="{{ $user->profile['mother_name'] ?? '' }}" />
                                                </div>
                                            </div>
                                            <div class="row mb-6">
                                                <label class="col-lg-4 col-form-label required fw-semibold fs-6">Tempat Lahir</label>
                                                <div class="col-lg-8 fv-row">
                                                    <input type="text" name="birth_place" class="form-control form-control-lg form-control-solid" placeholder="Tempat Lahir" value="{{ $user->profile['birth_place'] ?? '' }}" />
                                                </div>
                                            </div>
                                            <div class="row mb-6">
                                                <label class="col-lg-4 col-form-label required fw-semibold fs-6">Tanggal Lahir</label>
                                                <div class="col-lg-8 fv-row">
                                                    <input type="date" name="birth_date" class="form-control form-control-lg form-control-solid" placeholder="Tanggal Lahir" value="{{ $user->profile['birth_date'] ?? '' }}" />
                                                </div>
                                            </div>
                                            <div class="row mb-6">
                                                <label class="col-lg-4 col-form-label required fw-semibold fs-6">No NPWP</label>
                                                <div class="col-lg-8 fv-row">
                                                    <input type="text" name="npwp_number" class="form-control form-control-lg form-control-solid" placeholder="No NPWP" value="{{ $user->profile['npwp_number'] ?? '' }}" />
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card mb-5 mb-xl-10">
                                    <div class="card-header border-0 cursor-pointer" role="button" data-bs-toggle="collapse" data-bs-target="#kt_account_profile_details" aria-expanded="true" aria-controls="kt_account_profile_details">
                                        <div class="card-title m-0">
                                            <h3 class="fw-bold m-0">Bank Details</h3>
                                        </div>
                                    </div>
                                    <div id="kt_account_settings_profile_details" class="collapse show">
                                        <div class="card-body border-top p-9">
                                            <div class="row mb-6">
                                                <label class="col-lg-4 col-form-label required fw-semibold fs-6">Nama BANK</label>
                                                <div class="col-lg-8 fv-row">
                                                    <input type="text" name="bank_name" class="form-control form-control-lg form-control-solid" placeholder="Nama BANK" value="{{ $user->profile['bank_name'] ?? '' }}" />
                                                </div>
                                            </div>
                                            <div class="row mb-6">
                                                <label class="col-lg-4 col-form-label required fw-semibold fs-6">Nama Rekening</label>
                                                <div class="col-lg-8 fv-row">
                                                    <input type="text" name="account_name" class="form-control form-control-lg form-control-solid" placeholder="Nama Rekening" value="{{ $user->profile['account_name'] ?? '' }}" />
                                                </div>
                                            </div>
                                            <div class="row mb-6">
                                                <label class="col-lg-4 col-form-label required fw-semibold fs-6">No Rekening</label>
                                                <div class="col-lg-8 fv-row">
                                                    <input type="text" name="account_number" class="form-control form-control-lg form-control-solid" placeholder="No Rekening" value="{{ $user->profile['account_number'] ?? '' }}" />
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-footer d-flex justify-content-end py-6 px-9">
                                        <button type="reset" class="btn btn-light btn-active-light-primary me-2">Discard</button>
                                        <button type="submit" class="btn btn-primary" id="kt_account_profile_details_submit">Save Changes</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="tab-pane fade" id="kt_tab_pane_3" role="tabpanel">
                        <div class="card pt-4">
                            <!--begin::Card header-->
                            <div class="card-header border-0">
                                <!--begin::Card title-->
                                <div class="card-title">
                                    <h2>Logs</h2>
                                </div>
                                <!--end::Card title-->
                                <!--begin::Card toolbar-->
                                <div class="card-toolbar">
                                    <!--begin::Button-->
                                    <button type="button" class="btn btn-sm btn-light-primary">
                                    <i class="ki-outline ki-cloud-download fs-3"></i>Download Report</button>
                                    <!--end::Button-->
                                </div>
                                <!--end::Card toolbar-->
                            </div>
                            <!--end::Card header-->
                            <!--begin::Card body-->
                            <div class="card-body py-0">
                                <!--begin::Table wrapper-->
                                <div class="table-responsive">
                                    <!--begin::Table-->
                                    <table class="table align-middle table-row-dashed fw-semibold text-gray-600 fs-6 gy-5" id="kt_table_customers_logs">
                                        <!--begin::Table body-->
                                        <tbody>
                                            @foreach ($activities as $activity) 
                                                <tr>
                                                    <!--begin::Badge=-->
                                                    <td class="min-w-70px">
                                                        <div class="badge badge-light-warning">{{ $activity->description }}</div>
                                                    </td>
                                                    <!--end::Badge=-->
                                                    <!--begin::Status=-->
                                                    <td>
                                                        <!-- Decode and access attributes -->
                                                        @php
                                                            $attributes = json_decode($activity->properties)->attributes;
                                                            foreach($attributes as $key => $value) {
                                                                echo "$key: $value <br>";
                                                            }
                                                        @endphp
                                                    </td>
                                                    <!-- Decode and access old -->
                                                    <td>
                                                        @php
                                                            $properties = json_decode($activity->properties);
                                                            if ($properties && isset($properties->old)) {
                                                                $old = $properties->old;
                                                                foreach($old as $key => $value) {
                                                                    echo "$key: $value <br>";
                                                                }
                                                            }
                                                        @endphp
                                                    </td>
                                                    <!--end::Status=-->
                                                    <!--begin::Timestamp=-->
                                                    <td class="pe-0 text-end min-w-200px">{{ $activity->created_at->format('d-m-Y') }}</td>
                                                    <!--end::Timestamp=-->
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        <!--end::Table body-->
                                    </table>
                                    <!--end::Table-->
                                </div>
                                <!--end::Table wrapper-->
                            </div>
                            <!--end::Card body-->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection