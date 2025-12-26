@extends('layouts.main')

@section('content')
<div class="app-main flex-column flex-row-fluid" id="kt_app_main">
    <div class="d-flex flex-column flex-column-fluid">
        <div id="kt_app_content" class="app-content flex-column-fluid">
            <div id="kt_app_content_container" class="app-container container-fluid">
                <div id="kt_app_toolbar" class="app-toolbar pt-6 pb-10">
                    <!--begin::Toolbar container-->
                    <div id="kt_app_toolbar_container" class="app-container container-fluid d-flex align-items-stretch">
                        <!--begin::Toolbar wrapper-->
                        <div class="app-toolbar-wrapper d-flex flex-stack flex-wrap gap-4 w-100">
                            <!--begin::Page title-->
                            <div class="page-title d-flex flex-column justify-content-center gap-1 me-3">
                                <h1 class="page-heading d-flex flex-column justify-content-center text-gray-900 fw-bold fs-3 m-0">Generate Resume</h1>
                                <!--end::Title-->
                                <!--begin::Breadcrumb-->
                                <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0">
                                    <!--begin::Item-->
                                    <li class="breadcrumb-item text-muted">
                                        User
                                    </li>
                                    <li class="breadcrumb-item">
                                        <span class="bullet bg-gray-500 w-5px h-2px"></span>
                                    </li>
                                    <li class="breadcrumb-item text-muted">
                                        {{ $user->name }}
                                    </li>
                                    <li class="breadcrumb-item">
                                        <span class="bullet bg-gray-500 w-5px h-2px"></span>
                                    </li>
                                    <li class="breadcrumb-item text-muted">
                                        Resume
                                    </li>
                                </ul>
                            </div>
                            <!--end::Page title-->
                            <!--begin::Actions-->
                            <div class="d-flex align-items-center gap-2 gap-lg-3">
                                <button type="button" class="btn btn-flex btn-primary h-40px fs-7 fw-bold" id="save-png">Save PNG</button>
                            </div>
                            <!--end::Actions-->
                        </div>
                        <!--end::Toolbar wrapper-->
                    </div>
                    <!--end::Toolbar container-->
                </div>
                <div class="card specific">
                    <div class="card-body">
                        <div id="kt_app_content_container" class="app-container container-fluid pt-10">
                            <div class="card mb-5 mb-xl-10">
                                <div class="card-body pt-9 pb-0">
                                    <!--begin::Details-->
                                    <div class="d-flex flex-wrap flex-sm-nowrap">
                                        <!--begin: Pic-->
                                        <div class="me-7 mb-4">
                                            <div class="symbol symbol-100px symbol-lg-160px symbol-fixed position-relative">
                                                <img src="{{ $user->profile['avatar_url'] ?? '/assets/media/avatars/blank.png' }}" alt="image" />
                                                <div class="position-absolute translate-middle bottom-0 start-100 mb-6 bg-success rounded-circle border border-4 border-body h-20px w-20px"></div>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1 mb-5">
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
                                                            <i class="ki-outline ki-user fs-4 me-1"></i>{{ $user->profile['gender'] ?? '' }}</a>
                                                        <a href="#" class="d-flex align-items-center text-gray-500 text-hover-primary me-5 mb-2">
                                                        <i class="ki-outline ki-phone fs-4 me-1"></i>{{ $user->phone ?? '' }}</a>
                                                        <a href="#" class="d-flex align-items-center text-gray-500 text-hover-primary mb-2">
                                                        <i class="ki-outline ki-sms fs-4"></i>{{ $user->email ?? '' }}</a>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="d-flex flex-wrap flex-stack">
                                                <div class="d-flex flex-column flex-grow-1 pe-8">
                                                    <div class="d-flex flex-wrap">
                                                        <p>{{ $user->profile['address'] ?? '' }}</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card mb-5 mb-xl-10">
                                <div class="card-header cursor-pointer">
                                    <div class="card-title m-0">
                                        <h3 class="fw-bold m-0">Profile Details</h3>
                                    </div>
                                </div>
                                <div class="card-body p-9">
                                    <div class="row mb-7">
                                        <label class="col-lg-4 fw-semibold text-muted">Full Name</label>
                                        <div class="col-lg-8">
                                            <span class="fw-bold fs-6 text-gray-800">{{ $user->name ?? '' }}</span>
                                        </div>
                                    </div>
                                    <div class="row mb-7">
                                        <label class="col-lg-4 fw-semibold text-muted">Tempat, Tanggal lahir</label>
                                        <div class="col-lg-8 fv-row">
                                            <span class="fw-semibold text-gray-800 fs-6">{{ $user->profile['birth_place'] ?? '' }}, {{ $user->profile['birth_date'] ?? '' }}</span>
                                        </div>
                                    </div>
                                    <div class="row mb-7">
                                        <!--begin::Label-->
                                        <label class="col-lg-4 fw-semibold text-muted">NPWP Number</label>
                                        <div class="col-lg-8">
                                            <a href="#" class="fw-semibold fs-6 text-gray-800 text-hover-primary">{{ $user->profile['npwp_number'] ?? '' }}</a>
                                        </div>
                                    </div>
                                    <div class="card-title m-0 mt-10">
                                        <h3 class="fw-bold m-0">Bank Details</h3>
                                    </div>
                                    <div class="card-body p-9">
                                        <div class="row mb-7">
                                            <label class="col-lg-4 fw-semibold text-muted">bank Name</label>
                                            <div class="col-lg-8">
                                                <span class="fw-bold fs-6 text-gray-800">{{ $user->profile['bank_name'] ?? '' }}</span>
                                            </div>
                                        </div>
                                        <div class="row mb-7">
                                            <label class="col-lg-4 fw-semibold text-muted">Account Name</label>
                                            <div class="col-lg-8 fv-row">
                                                <span class="fw-semibold text-gray-800 fs-6">{{ $user->profile['account_name'] ?? '' }}</span>
                                            </div>
                                        </div>
                                        <div class="row mb-7">
                                            <!--begin::Label-->
                                            <label class="col-lg-4 fw-semibold text-muted">Account Number</label>
                                            <div class="col-lg-8">
                                                <a href="#" class="fw-semibold fs-6 text-gray-800 text-hover-primary">{{ $user->profile['account_number'] ?? '' }}</a>
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
</div>
@endsection
@push('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/canvas-toBlob@1.0.1/canvas-toBlob.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/file-saver@2.0.5/dist/FileSaver.min.js"></script>
<script>
    document.getElementById('save-png').addEventListener('click', function() {
        html2canvas(document.querySelector('.specific')).then(canvas => {
            canvas.toBlob(function(blob) {
                saveAs(blob, '{{ $user->name }}.png');
            });
        });
    });
</script>
@endpush
