@extends('website.layouts.app')

@section('content')
<div class="page-wrapper" style="background: #eceef4; min-height: 100vh; padding-bottom: 3rem;">
    <div class="content">

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
                    
                    @if($eletter->esign)
                        <div style="position: absolute; top: 20%; left: 50%; transform: translate(-50%, -50%) rotate(-45deg); font-size: 5rem; color: rgba(40, 167, 69, 0.1); font-weight: bold; pointer-events: none; text-transform: uppercase; border: 10px solid rgba(40, 167, 69, 0.1); padding: 10px; z-index: 1;">
                            SIGNED
                        </div>
                    @endif
                    <div class="letter-content">
                        {!! $eletter->letter->description !!}
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