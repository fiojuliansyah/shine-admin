@extends('layouts.main')

@section('content')
<div class="app-main flex-column flex-row-fluid" id="kt_app_main">
    <div class="d-flex flex-column flex-column-fluid">
        <div id="kt_app_content" class="app-content flex-column-fluid">
            <div id="kt_app_content_container" class="app-container container-fluid">
                <div class="pb-7">
                    <div class="page-title d-flex flex-column justify-content-center gap-1 me-3">
                        <!--begin::Title-->
                        <h1 class="page-heading d-flex flex-column justify-content-center text-gray-900 fw-bold fs-3 m-0">Activity Log</h1>
                        <!--end::Title-->
                        <!--begin::Breadcrumb-->
                        <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0">
                            <!--begin::Item-->
                            <li class="breadcrumb-item text-muted">
                                Managements
                             </li>
                             <li class="breadcrumb-item">
                                 <span class="bullet bg-gray-500 w-5px h-2px"></span>
                             </li>
                            <li class="breadcrumb-item text-muted">
                                <a href="{{ route('activities') }}" class="text-muted text-hover-primary">Activities</a>
                            </li>
                        </ul>
                        <!--end::Breadcrumb-->
                    </div>
                </div>
                <div class="card">
                    @livewire('activityTable')
                </div>
            </div>
        </div>
    </div>
</div>
@endsection