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
                    </div>
                    <div class="d-flex align-items-center flex-wrap mb-1">
                        <a href="{{ route('applicants.profiles.index') }}" class="btn btn-dark btn-md me-2 mb-2">Kelola Profil</a>
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
                                        <p class="fw-medium text-gray-9 mb-1">Selamat datang di SHINE Karir Official !</p>
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
