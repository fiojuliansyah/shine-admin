@extends('website.layouts.app')

@section('content')
    <div class="page-wrapper">
        <div class="content">
            <div class="alert bg-danger-transparent alert-dismissible fade show mb-4">
                Lengkapi profil anda sebelum melamar!
                <button type="button" class="btn-close fs-14" data-bs-dismiss="alert" aria-label="Close"><i
                        class="ti ti-x"></i></button>
            </div>

            <div class="welcome-wrap mb-4">
                <div class=" d-flex align-items-center justify-content-between flex-wrap">
                    <div class="mb-3">
                        <h2 class="mb-1 text-white">Welcome Back, {{ Auth::user()->name ?? 'Pelamar' }}</h2>
                        {{-- <p class="text-light">14 New Companies Subscribed Today !!!</p> --}}
                    </div>
                    <div class="d-flex align-items-center flex-wrap mb-1">
                        <a href="{{ route('applicants.profiles.index') }}" class="btn btn-dark btn-md me-2 mb-2">Kelola Profil</a>
                        {{-- <a href="#" class="btn btn-light btn-md mb-2">Pengaturan Akun</a> --}}
                    </div>
                </div>
                <div class="welcome-bg">
                    <img src="/admin/assets/img/bg/welcome-bg-02.svg" alt="img" class="welcome-bg-01">
                    <img src="/admin/assets/img/bg/welcome-bg-03.svg" alt="img" class="welcome-bg-02">
                    <img src="/admin/assets/img/bg/welcome-bg-01.svg" alt="img" class="welcome-bg-03">
                </div>
            </div>

            <div class="row" style="transform: none;">
                <div class="col-xl-4 theiaStickySidebar"
                    style="position: relative; overflow: visible; box-sizing: border-box; min-height: 1px;">

                    <div id="printableCard" class="theiaStickySidebar"
                        style="padding-top: 0px; padding-bottom: 1px; position: static; transform: none; top: 0px; left: 276px;">
                        <div class="card card-bg-1">
                            <div class="card-body p-0">
                                <span class="avatar avatar-xl avatar-rounded border border-2 border-white m-auto d-flex mb-2">
                                    <img src="{{ Auth::user()->profile['avatar_url'] ?? '/assets/media/avatars/blank.png' }}"
                                        class="w-auto h-auto" alt="Img">
                                </span>
                                <div class="text-center px-3 pb-3 border-bottom">
                                    <h5 class="d-flex align-items-center justify-content-center mb-1">
                                        {{ Auth::user()->name ?? 'Pelamar' }}
                                    </h5>
                                    <p class="text-dark mb-1">{{ Auth::user()->email }}</p>
                                    <span class="badge bg-success-transparent">Active</span>
                                </div>
                                <div class="p-3 border-bottom">
                                    <div style="text-align: center; padding-bottom: 20px">
                                        <h6>QR CODE</h6>
                                    </div>
                                    <div style="text-align: center">
                                        @if (auth()->user()->profile_qr)
                                            <div>
                                                {!! auth()->user()->profile_qr !!}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                <div class="p-3" id="print-ignore">
                                    <p><span style="color: red">*</span> Tunjukan QR ini pada saat Interview berlangsung!</p>
                                    <div style="text-align: center">
                                        <button onclick="printDiv('printableCard')" class="align-items-center justify-content-center btn btn-primary">
                                            <i class="ti ti-printer me-2"></i> Cetak Kartu
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        
                        <div dir="ltr" class="resize-sensor"
                            style="pointer-events: none; position: absolute; inset: 0px; overflow: hidden; z-index: -1; visibility: hidden; max-width: 100%;">
                            <div class="resize-sensor-expand"
                                style="pointer-events: none; position: absolute; inset: 0px; overflow: hidden; z-index: -1; visibility: hidden; max-width: 100%;">
                                <div
                                    style="position: absolute; left: 0px; top: 0px; transition: all; width: 392px; height: 982px;">
                                </div>
                            </div>
                            <div class="resize-sensor-shrink"
                                style="pointer-events: none; position: absolute; inset: 0px; overflow: hidden; z-index: -1; visibility: hidden; max-width: 100%;">
                                <div
                                    style="position: absolute; left: 0px; top: 0px; transition: all; width: 200%; height: 200%;">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-8">
                    {{-- <div class="card border-0">
                        <div class="card-header">
                            <div class="d-flex align-items-center justify-content-between">
                                <h5>Notifikasi</h5>
                            </div>
                        </div>
                        <div class="card-body">
                            <span class="badge badge-soft-purple d-inline-flex align-items-center mb-3">
                                <i class="ti ti-calendar me-1"></i>
                                15 Feb 2024
                            </span>
                            <div class="border rounded p-3 mb-3">
                                <div class="d-flex align-items-start">
                                    <span class="avatar avatar-md avatar-rounded flex-shrink-0 bg-skyblue me-2">
                                        <i class="ti ti-bell fs-20"></i>
                                        <span class="notification-status-dot"></span>
                                    </span>
                                    <div>
                                        <h6 class="fw-medium mb-1">You sent 1 Message to the contact.</h6>
                                        <span>10:25 pm</span>
                                    </div>
                                </div>
                            </div>
                            <div class="border rounded p-3">
                                <div class="d-flex align-items-start mb-2">
                                    <span class="avatar avatar-md avatar-rounded flex-shrink-0 bg-purple me-2"><i
                                            class="ti ti-user-circle fs-20"></i></span>
                                    <div>
                                        <h6 class="fw-medium mb-1">
                                            Product Meeting
                                        </h6>
                                        <p class="mb-1">A product team meeting is a gathering of the cross-functional
                                            product team — ideally including
                                            team members from product, engineering, marketing, and customer support.
                                        </p>
                                        <span>Schedueled on 05:00 pm</span>
                                    </div>
                                </div>
                                <div class="bg-light-500 rounded p-3">
                                    <div class="row">
                                        <div class="col-md-4 col-sm-6">
                                            <div>
                                                <h6 class="fs-12 fw-medium mb-2">Reminder</h6>
                                                <div class="dropdown">
                                                    <a href="javascript:void(0);"
                                                        class="dropdown-toggle btn btn-sm btn-white d-inline-flex align-items-center"
                                                        data-bs-toggle="dropdown">
                                                        <i class="clock-hour-3 me-1"></i>
                                                        Reminder
                                                    </a>
                                                    <ul class="dropdown-menu  dropdown-menu-end p-3">
                                                        <li>
                                                            <a href="javascript:void(0);"
                                                                class="dropdown-item rounded-1">Reminder</a>
                                                        </li>
                                                        <li>
                                                            <a href="javascript:void(0);"
                                                                class="dropdown-item rounded-1">1 Hr</a>
                                                        </li>
                                                        <li>
                                                            <a href="javascript:void(0);"
                                                                class="dropdown-item rounded-1">10 Hr</a>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4 col-sm-6">
                                            <div>
                                                <h6 class="fs-12 fw-medium mb-2">Task Priority</h6>
                                                <div class="dropdown">
                                                    <a href="javascript:void(0);"
                                                        class="dropdown-toggle btn-sm btn btn-white d-inline-flex align-items-center"
                                                        data-bs-toggle="dropdown">
                                                        <span
                                                            class="border border-purple rounded-circle bg-soft-danger d-flex justify-content-center align-items-center me-1">
                                                            <i class="ti ti-point-filled text-danger"></i>
                                                        </span>
                                                        High
                                                    </a>
                                                    <ul class="dropdown-menu  dropdown-menu-end p-3">
                                                        <li>
                                                            <a href="javascript:void(0);"
                                                                class="dropdown-item rounded-1">High</a>
                                                        </li>
                                                        <li>
                                                            <a href="javascript:void(0);"
                                                                class="dropdown-item rounded-1">Medium</a>
                                                        </li>
                                                        <li>
                                                            <a href="javascript:void(0);"
                                                                class="dropdown-item rounded-1">Low</a>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4 col-sm-6">
                                            <div>
                                                <h6 class="fs-12 fw-medium mb-2">Assigned to</h6>
                                                <div class="dropdown">
                                                    <a href="javascript:void(0);"
                                                        class="dropdown-toggle btn btn-sm btn-white d-inline-flex align-items-center"
                                                        data-bs-toggle="dropdown">
                                                        <span class="avatar avatar-xs avatar-rounded me-1">
                                                            <img src="assets/img/profiles/avatar-02.jpg" alt="Img">
                                                        </span>
                                                        John
                                                    </a>
                                                    <ul class="dropdown-menu  dropdown-menu-end p-3">
                                                        <li>
                                                            <a href="javascript:void(0);"
                                                                class="dropdown-item rounded-1 d-flex align-items-center">
                                                                <span class="avatar avatar-xs avatar-rounded me-1">
                                                                    <img src="assets/img/profiles/avatar-02.jpg"
                                                                        alt="Img">
                                                                </span>
                                                                John
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a href="javascript:void(0);"
                                                                class="dropdown-item rounded-1 d-flex align-items-center">
                                                                <span class="avatar avatar-xs avatar-rounded me-1">
                                                                    <img src="assets/img/profiles/avatar-01.jpg"
                                                                        alt="Img">
                                                                </span>
                                                                Sophie
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a href="javascript:void(0);"
                                                                class="dropdown-item rounded-1 d-flex align-items-center">
                                                                <span class="avatar avatar-xs avatar-rounded me-1">
                                                                    <img src="assets/img/profiles/avatar-03.jpg"
                                                                        alt="Img">
                                                                </span>
                                                                Estelle
                                                            </a>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div> --}}
                    <div class="card border-0">
                        <div class="card-header">
                            <div class="d-flex align-items-center justify-content-between">
                                <h5>Timeline Lamaran</h5>
                            </div>
                        </div>
                        <div class="card-body">
                            @foreach ($timelines as $timeline)  
                                <div class="d-flex align-items-center">
                                    <div class="d-flex align-items-center active-time">
                                        <span class="timeline-date text-dark">{{ $timeline->created_at->diffForHumans() }}</span>
                                        <span class="timeline-border d-flex align-items-center justify-content-center bg-white">
                                            <i class="ti ti-point-filled text-gray-2 fs-18"></i>
                                        </span>
                                    </div>
                                    <div class="flex-fill ps-3 pb-4 timeline-hrline">
                                        <div class="mt-4">
                                            <p class="fw-medium text-gray-9 mb-1">Selamat kamu masuk ketahap {{ $timeline->status->name ?? 'Review Berkas' }} !</p>
                                            <span>lengkapi profil anda, dan pilih pekerjaan yang anda minati.</span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                            <div class="d-flex align-items-center">
                                <div class="d-flex align-items-center active-time">
                                    <span class="timeline-date text-dark">{{ Auth::user()->created_at->diffForHumans() }}</span>
                                    <span class="timeline-border d-flex align-items-center justify-content-center bg-white">
                                        <i class="ti ti-point-filled text-gray-2 fs-18"></i>
                                    </span>
                                </div>
                                <div class="flex-fill ps-3 pb-4 timeline-hrline">
                                    <div class="mt-4">
                                        <p class="fw-medium text-gray-9 mb-1">Selamat datang di GIS Karir Official !</p>
                                        <span>lengkapi profil anda, dan pilih pekerjaan yang anda minati.</span>
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
<script>
    function printDiv(divId) {
        var originalDiv = document.getElementById(divId).cloneNode(true);
        var elementToRemove = originalDiv.querySelector('#print-ignore');
    
        if (elementToRemove) {
            elementToRemove.remove();
        }
    
        var printFrame = document.createElement('iframe');
        printFrame.name = "print_frame";
        printFrame.style.position = "absolute";
        printFrame.style.top = "-10000px";
        document.body.appendChild(printFrame);
    
        var frameDoc = printFrame.contentWindow ? printFrame.contentWindow : printFrame.contentDocument.document ? printFrame.contentDocument.document : printFrame.contentDocument;
    
        var headContent = document.head.innerHTML;
    
        frameDoc.document.open();
        frameDoc.document.write(`
            <html>
                <head>
                    ${headContent}
                    <style>
                        body { margin: 20px; font-family: Arial, sans-serif; }
                        .card { margin: auto; }
                    </style>
                </head>
                <body>
                    <div class="card">
                        ${originalDiv.innerHTML}
                    </div>
                </body>
            </html>
        `);
        frameDoc.document.close();
    
        setTimeout(function () {
            frameDoc.focus();
            frameDoc.print();
            document.body.removeChild(printFrame);
        }, 500);
    }
    </script>
    
@endpush
