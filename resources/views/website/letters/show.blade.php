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
        <button type="button" class="btn btn-primary btn-lg rounded-circle shadow-lg" 
            style="position: fixed; bottom: 30px; right: 30px; width: 60px; height: 60px; z-index: 1050;"
            data-bs-toggle="modal" data-bs-target="#modalSignature">
            <i class="ti ti-signature fs-24"></i>
        </button>

        <div class="modal fade" id="modalSignature" tabindex="-1" aria-hidden="true">
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
    </div>
</div>
@endsection

@push('css')
<style>
    @media screen {
        .letter-content img {
            max-width: 100%;
            height: auto;
        }
    }

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

    .letter-content {
        color: #000 !important;
    }
    .letter-content table {
        width: 100% !important;
        border-collapse: collapse;
    }
    .letter-content table td, .letter-content table th {
        padding: 8px;
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
    modalEl.addEventListener('shown.bs.modal', function () {
        const ratio = Math.max(window.devicePixelRatio || 1, 1);
        canvas.width = canvas.offsetWidth * ratio;
        canvas.height = canvas.offsetHeight * ratio;
        canvas.getContext("2d").scale(ratio, ratio);
        signaturePad.clear(); // Bersihkan canvas setelah resize
    });

    // Simpan Tanda Tangan
    document.querySelector('#modalSignature form').addEventListener('submit', function(e) {
        if (signaturePad.isEmpty()) {
            e.preventDefault();
            alert('Mohon bubuhkan tanda tangan terlebih dahulu!');
            return;
        }

        // Convert ke base64 image
        const signatureData = signaturePad.toDataURL('image/svg+xml');
        document.getElementById('signatureInput').value = signatureData;
    });

    // Reset Canvas
    document.getElementById('resetSignature').addEventListener('click', function() {
        signaturePad.clear();
        document.getElementById('signatureInput').value = '';
    });
</script>
@endpush