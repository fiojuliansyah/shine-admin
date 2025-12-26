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
                                <h1 class="page-heading d-flex flex-column justify-content-center text-gray-900 fw-bold fs-3 m-0">Generate Banner</h1>
                                <!--end::Title-->
                                <!--begin::Breadcrumb-->
                                <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0">
                                    <!--begin::Item-->
                                    <li class="breadcrumb-item text-muted">
                                        Recruit
                                    </li>
                                    <li class="breadcrumb-item">
                                        <span class="bullet bg-gray-500 w-5px h-2px"></span>
                                    </li>
                                    <li class="breadcrumb-item text-muted">
                                        <a href="{{ route('careers.index') }}" class="text-muted text-hover-primary">Careers</a>
                                    </li>
                                    <li class="breadcrumb-item">
                                        <span class="bullet bg-gray-500 w-5px h-2px"></span>
                                    </li>
                                    <li class="breadcrumb-item text-muted">
                                        Banner {{ $career->name }}
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
                    <div class="card-body" style="height: 1123px;">
                        <div id="kt_app_content_container" class="app-container container-fluid pt-10">
                            <div class="position-relative mb-17">
                                <div class="overlay overlay-show">
                                    <div class="bgi-no-repeat bgi-position-center bgi-size-cover card-rounded min-h-250px" style="background-image:url('/assets/media/stock/1600x800/img-1.jpg')"></div>
                                    <div class="overlay-layer rounded bg-black" style="opacity: 0.4"></div>
                                </div>
                                <div class="position-absolute text-white mb-8 ms-10 bottom-0">
                                    <h3 class="text-white fs-2qx fw-bold mb-3 m">{{ $career->name }}</h3>
                                    <div class="fs-5 fw-semibold">{{ $career->company['name'] }}</div>
                                </div>
                            </div>
                            <div class="d-flex flex-column flex-lg-row mb-17">
                                <div class="flex-lg-row-fluid me-0 me-lg-20">
                                    <div class="mb-10 mb-lg-0">
                                        <div class="m-0">
                                            {!! $career->description !!}
                                        </div>
                                    </div>
                                    <div class="d-flex flex-wrap mb-5">
                                        <div class="border border-dashed rounded min-w-90px py-3 px-4 mx-2 mb-3">
                                            <div class="fw-semibold text-gray-500">Graduate</div>
                                            <div class="fs-6 fw-bold text-gray-900">{{ $career->graduate }}</div>
                                        </div>
                                        <div class="border border-dashed rounded min-w-90px py-3 px-4 mx-2 mb-3">
                                            <div class="fw-semibold text-gray-500">Salary</div>
                                            <div class="fs-6 fw-bold text-success">{{ $career->salary }}</div>
                                        </div>
                                        <div class="border border-dashed rounded min-w-90px py-3 px-4 mx-2 mb-3">
                                            <div class="fw-semibold text-gray-500">Need</div>
                                            <div class="fs-6 fw-bold text-gray-900">{{ $career->candidate }}</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex-lg-row-auto w-100 w-lg-275px w-xxl-350px">
                                    <div class="card bg-light">
                                        <div class="card-body">
                                            <div class="mb-7">
                                                <h2 class="fs-1 text-gray-800 w-bolder mb-6">About Us</h2>
                                            </div>
                                            <div class="mb-8">
                                                <h4 class="text-gray-700 w-bolder mb-0">Location</h4>
                                                <div class="my-2">
                                                    <div class="d-flex align-items-center mb-3">
                                                        <div class="text-gray-600 fw-semibold fs-6">{{ $career->location }}</div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="mb-8">
                                                <h4 class="text-gray-700 w-bolder mb-0">Work Function</h4>
                                                <div class="my-2">
                                                    <div class="d-flex align-items-center mb-3">
                                                        <div class="text-gray-600 fw-semibold fs-6">{{ $career->workfunction }}</div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="mb-8">
                                                <h4 class="text-gray-700 w-bolder mb-0">Major</h4>
                                                <div class="my-2">
                                                    <div class="d-flex align-items-center mb-3">
                                                        <div class="text-gray-600 fw-semibold fs-6">{{ $career->major }}</div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="mb-8">
                                                <h4 class="text-gray-700 w-bolder mb-0">Experience</h4>
                                                <div class="my-2">
                                                    <div class="d-flex align-items-center mb-3">
                                                        <div class="text-gray-600 fw-semibold fs-6">{{ $career->experience }}</div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="mb-8">
                                                <div class="d-flex flex-wrap mb-5">
                                                    <div style="border: 15px solid #ffff; border-radius: 10px;">
                                                        {!! $career->qr_link !!}
                                                    </div>
                                                </div>
                                                <div class="mb-10 mb-lg-0">
                                                    <div class="m-0">
                                                        Scan This For Apply Job
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <!--end::Body-->
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
                saveAs(blob, '{{ $career->name }}.png');
            });
        });
    });
</script>
@endpush
