@extends('website.layouts.app')

@section('content')
<div class="page-wrapper" style="background: #eceef4; min-height: 100vh; padding-bottom: 3rem;">
    <div class="content">

        <div class="d-flex justify-content-center mb-3 d-print-none">
            <button type="button" class="btn btn-white shadow-sm border d-inline-flex align-items-center" onclick="window.print();">
                <i class="ti ti-printer me-2"></i> Cetak / Simpan PDF
            </button>
        </div>

        <div class="card mx-auto shadow-lg border-0 card-print-reset" style="max-width: 850px;">
            <div class="card-body p-0">
                <div id="printableArea">
                    
                    @if($eletter->esign && $eletter->esign != 'no-need')
                        <div class="signed-watermark">
                            SIGNED
                        </div>
                    @endif

                    <div class="letter-content">
                        @php
                            $content = $eletter->letter->description;
                            /** * Menggunakan Regex untuk menangkap * meski ada spasi tambahan di dalamnya 
                             */
                            $content = preg_replace('//i', '<div class="page-break"></div>', $content);
                        @endphp
                        {!! $content !!}
                    </div>

                </div>
            </div>
        </div>

        @if(!$eletter->second_party_esign)
            @if ($eletter->esign != 'no-need') 
                <button type="button" class="btn btn-primary btn-lg rounded-circle shadow-lg d-print-none" 
                    style="position: fixed; bottom: 30px; right: 30px; width: 60px; height: 60px; z-index: 1050;"
                    data-bs-toggle="modal" data-bs-target="#modalSignature">
                    <i class="ti ti-signature fs-24"></i>
                </button>
            @endif

            <div class="modal fade d-print-none" id="modalSignature" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Tanda Tangan Digital</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form action="{{ route('web.applicants.letter.sign', $eletter->id) }}" method="POST">
                            @csrf
                            <div class="modal-body text-center">
                                <p class="text-muted fs-12">Gunakan mouse atau jari Anda untuk menandatangani dokumen ini.</p>
                                <div class="border rounded bg-light">
                                    <canvas id="signatureCanvas" style="width: 100%; height: 200px; cursor: crosshair;"></canvas>
                                </div>
                                <input type="hidden" name="second_party_esign" id="signatureInput">
                            </div>
                            <div class="modal-footer">
                                <button type="button" id="resetSignature" class="btn btn-light">Hapus</button>
                                <button type="submit" id="saveSignature" class="btn btn-primary">Simpan Tanda Tangan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @else
            <div class="card mx-auto mt-4 shadow-sm d-print-none" style="max-width: 850px; background: #d1e7dd; border-color: #badbcc;">
                <div class="card-body text-center text-success">
                    <i class="ti ti-circle-check fs-32 mb-2"></i>
                    <h4 class="fw-bold">Dokumen Selesai Ditandatangani</h4>
                    <p class="mb-0">Terima kasih, Anda telah membubuhkan tanda tangan digital pada dokumen ini.</p>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

@push('css')
<style>
    #printableArea {
        background-color: #fff; 
        padding: 5rem 4rem; 
        min-height: 297mm; 
        font-family: 'Times New Roman', Times, serif; 
        color: #000; 
        line-height: 1.6;
        font-size: 12pt;
        position: relative;
    }

    .signed-watermark {
        position: absolute;
        top: 20%;
        left: 50%;
        transform: translate(-50%, -50%) rotate(-45deg);
        font-size: 5rem;
        color: rgba(40, 167, 69, 0.1);
        font-weight: bold;
        pointer-events: none;
        text-transform: uppercase;
        border: 10px solid rgba(40, 167, 69, 0.1);
        padding: 10px;
        z-index: 1;
    }

    @media print {
        /* Memaksa semua elemen pembungkus menjadi blok statis */
        html, body, .main-wrapper, .page-wrapper, .content, .card, .card-body {
            display: block !important;
            overflow: visible !important;
            height: auto !important;
            min-height: auto !important;
            margin: 0 !important;
            padding: 0 !important;
            border: none !important;
            box-shadow: none !important;
            background: #fff !important;
        }

        /* Sembunyikan elemen UI */
        .d-print-none, header, footer, .navbar, .sidebar {
            display: none !important;
        }

        #printableArea {
            display: block !important;
            width: 100% !important;
            padding: 0 !important;
            margin: 0 !important;
            position: static !important;
        }

        /* Rule Utama Page Break */
        .page-break {
            display: block !important;
            clear: both !important;
            page-break-before: always !important;
            break-before: page !important;
            height: 1px !important; /* Memberi sedikit tinggi agar terbaca browser */
            visibility: hidden !important;
        }

        /* Memaksa Paragraf yang membungkus page-break agar tidak memblokir */
        p, span {
            orphans: 3;
            widows: 3;
        }

        /* Reset MsoNormal dan pembungkus break */
        .MsoNormal:has(.page-break), 
        span:has(.page-break),
        p:has(.page-break) {
            display: block !important;
            height: 0 !important;
            margin: 0 !important;
            padding: 0 !important;
            line-height: 0 !important;
        }

        @page {
            size: A4;
            margin: 2cm;
        }
    }

    .letter-content table {
        width: 100% !important;
        border-collapse: collapse;
    }
    
    .letter-content table td, 
    .letter-content table th {
        padding: 8px;
        border: 1px solid #000;
    }
</style>
@endpush

@push('js')
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
<script>
    const canvas = document.getElementById('signatureCanvas');
    const signaturePad = new SignaturePad(canvas, {
        backgroundColor: 'rgba(255, 255, 255, 0)',
        penColor: 'rgb(0, 0, 0)'
    });

    const modalEl = document.getElementById('modalSignature');
    if (modalEl) {
        modalEl.addEventListener('shown.bs.modal', function () {
            const ratio = Math.max(window.devicePixelRatio || 1, 1);
            canvas.width = canvas.offsetWidth * ratio;
            canvas.height = canvas.offsetHeight * ratio;
            canvas.getContext("2d").scale(ratio, ratio);
            signaturePad.clear();
        });

        document.querySelector('#modalSignature form').addEventListener('submit', function(e) {
            if (signaturePad.isEmpty()) {
                e.preventDefault();
                alert('Mohon bubuhkan tanda tangan terlebih dahulu!');
                return;
            }
            const signatureData = signaturePad.toDataURL('image/png');
            document.getElementById('signatureInput').value = signatureData;
        });

        document.getElementById('resetSignature').addEventListener('click', function() {
            signaturePad.clear();
            document.getElementById('signatureInput').value = '';
        });
    }
</script>
@endpush