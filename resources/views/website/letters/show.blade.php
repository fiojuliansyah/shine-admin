@extends('website.layouts.app')

@section('content')
<div class="page-wrapper" style="background: #eceef4; min-height: 100vh; padding-bottom: 3rem;">
    <div class="content">
        <div class="d-md-flex d-block align-items-center justify-content-between mb-4">
            <div class="my-auto mb-2">
                <h2 class="mb-1 text-dark">Surat Digital (PKWT)</h2>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('web.applicants.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Surat Digital</li>
                    </ol>
                </nav>
            </div>
            <div class="d-flex my-xl-auto right-content align-items-center flex-wrap">
                <div class="mb-2">
                    <button onclick="window.print()" class="btn btn-white border me-2">
                        <i class="ti ti-printer me-2"></i>Cetak PDF
                    </button>
                    {{-- Tombol E-Sign bisa ditambahkan di sini nanti --}}
                    <button class="btn btn-primary">
                        <i class="ti ti-signature me-2"></i>Tanda Tangan Digital
                    </button>
                </div>
            </div>
        </div>

        <div class="card mx-auto shadow-lg border-0" style="max-width: 850px;">
            <div class="card-body p-0">
                <div id="printableArea" style="
                    background-color: #fff; 
                    padding: 5rem 4rem; 
                    min-height: 297mm; 
                    font-family: 'Times New Roman', Times, serif; 
                    color: #000; 
                    line-height: 1.6;
                    font-size: 12pt;
                    position: relative;
                ">
                    
                    @if($letter->esign)
                        <div style="position: absolute; top: 20%; left: 50%; transform: translate(-50%, -50%) rotate(-45deg); font-size: 5rem; color: rgba(40, 167, 69, 0.1); font-weight: bold; pointer-events: none; text-transform: uppercase; border: 10px solid rgba(40, 167, 69, 0.1); padding: 10px; z-index: 1;">
                            SIGNED
                        </div>
                    @endif

                    <div class="text-center mb-5">
                        <img src="/admin/assets/img/logo-dark.svg" alt="Logo" style="max-height: 60px; margin-bottom: 1rem;">
                        <h4 class="mb-0 fw-bold" style="text-transform: uppercase;">SHINE Karir Official</h4>
                        <p class="mb-0" style="font-size: 10pt;">Alamat Kantor Pusat, No. Telp, Website</p>
                        <hr style="border: 1.5px solid #000; margin-top: 10px;">
                    </div>

                    <div class="letter-content">
                        {!! $eletter->letter->description !!}
                    </div>

                    <div class="mt-5 pt-5">
                        <div class="row">
                            <div class="col-6 text-center">
                                <p class="mb-5">Pihak Pertama,</p>
                                <br><br>
                                <p class="mb-0 fw-bold">( HRD MANAGER )</p>
                            </div>
                            <div class="col-6 text-center">
                                <p class="mb-5">Pihak Kedua,</p>
                                @if($letter->second_party_esign)
                                     <img src="{{ $letter->second_party_esign }}" alt="E-Sign" style="max-height: 80px;">
                                @else
                                    <br><br>
                                @endif
                                <p class="mb-0 fw-bold">( {{ Auth::user()->name }} )</p>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@push('css')
<style>
    /* Styling agar tampilan di layar menyerupai kertas asli */
    @media screen {
        .letter-content img {
            max-width: 100%;
            height: auto;
        }
    }

    /* Styling Khusus Cetak/Print */
    @media print {
        body * {
            visibility: hidden;
        }
        #printableArea, #printableArea * {
            visibility: visible;
        }
        #printableArea {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            padding: 0 !important;
            box-shadow: none !important;
        }
        .page-wrapper {
            background: #fff !important;
            padding: 0 !important;
        }
    }

    /* Menghilangkan border tinymce readonly */
    .letter-content {
        color: #000 !important;
    }
    .letter-content table {
        width: 100% !important;
        border-collapse: collapse;
    }
    .letter-content table td, .letter-content table th {
        border: 1px solid #000;
        padding: 8px;
    }
</style>
@endpush