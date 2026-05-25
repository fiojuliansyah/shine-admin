@extends('website.layouts.app')

@section('content')
<div class="page-wrapper">
    <div class="content">

        <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
            <div class="my-auto mb-2">
                <h2 class="mb-1">Detail Surat</h2>
            </div>
            <div class="d-flex my-xl-auto right-content align-items-center flex-wrap gap-2">
                <a href="{{ route('web.applicants.letter.print', $eletter->id) }}" target="_blank" class="btn btn-danger mb-2">
                    <i class="ti ti-printer me-1"></i>Print / PDF
                </a>
                @if(!$eletter->second_party_esign && $eletter->esign != 'no-need')
                    <button type="button" class="btn btn-primary mb-2" data-bs-toggle="modal" data-bs-target="#modalSignature">
                        <i class="ti ti-signature me-1"></i>Tanda Tangan
                    </button>
                @endif
            </div>
        </div>

        @if($eletter->second_party_esign)
            <div class="alert alert-success d-flex align-items-center mb-3">
                <i class="ti ti-circle-check fs-20 me-2"></i>
                <div><strong>Dokumen Selesai Ditandatangani.</strong> Anda telah membubuhkan tanda tangan digital pada dokumen ini.</div>
            </div>
        @endif

        <div class="card border-0 shadow-sm" style="height: calc(100vh - 200px); min-height: 600px;">
            <div class="card-body p-0" style="height:100%;">
                <iframe
                    src="{{ route('web.applicants.letter.print', $eletter->id) }}"
                    style="width:100%; height:100%; border:none; border-radius: 0 0 8px 8px;"
                    title="{{ $eletter->letter->title ?? 'Surat' }}"
                ></iframe>
            </div>
        </div>

    </div>
</div>

{{-- MODAL TANDA TANGAN --}}
@if(!$eletter->second_party_esign && $eletter->esign != 'no-need')
<div class="modal fade" id="modalSignature" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tanda Tangan Digital</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
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
@endif
@endsection

@push('js')
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
<script>
    const canvas = document.getElementById('signatureCanvas');
    if (canvas) {
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
                canvas.getContext('2d').scale(ratio, ratio);
                signaturePad.clear();
            });
            modalEl.querySelector('form').addEventListener('submit', function(e) {
                if (signaturePad.isEmpty()) { e.preventDefault(); alert('Mohon bubuhkan tanda tangan terlebih dahulu!'); return; }
                document.getElementById('signatureInput').value = signaturePad.toDataURL('image/png');
            });
            document.getElementById('resetSignature').addEventListener('click', function() {
                signaturePad.clear();
                document.getElementById('signatureInput').value = '';
            });
        }
    }
</script>
@endpush
