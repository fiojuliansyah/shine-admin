@extends('admin.layouts.main')

@section('content')
<div class="page-wrapper">
    <div class="content">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-flex align-items-center justify-content-between mb-4">
                    <h4 class="page-title mb-0 font-size-18">WhatsApp Gateway</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="javascript: void(0);">API</a></li>
                            <li class="breadcrumb-item active">Connect</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xl-4 col-md-5">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3 border-bottom-0">
                        <h5 class="card-title mb-0 fw-bold">
                            <i class="mdi mdi-qrcode-scan me-2 text-primary fs-4"></i>Status Koneksi
                        </h5>
                    </div>

                    <div class="card-body text-center" id="wa-container">
                        <div id="wa-loading" class="py-5">
                            <div class="spinner-border text-primary" role="status"></div>
                            <p class="mt-3 text-muted fw-semibold">Mengecek status gateway...</p>
                        </div>

                        <div id="wa-qr-area" class="hidden">
                            <div class="p-3 bg-light rounded-3 mb-3 d-inline-block border">
                                <img id="wa-qr-image" src="" alt="QR Code" class="img-fluid" style="max-width: 220px;">
                            </div>
                            <div class="alert alert-warning py-2 small text-start border-0">
                                <i class="mdi mdi-information-outline me-1"></i> QR Code diperbarui otomatis setiap 15 detik.
                            </div>
                        </div>

                        <div id="wa-success-area" class="hidden py-4">
                            <div class="mb-3">
                                <i class="mdi mdi-check-circle text-success" style="font-size: 4rem;"></i>
                            </div>
                            <h5 class="fw-bold text-success">WhatsApp Terhubung!</h5>
                            <p class="text-muted small px-3">Server sistem siap mengirimkan notifikasi otomatis ke pengguna.</p>
                            <div class="px-4"><hr class="text-muted opacity-25"></div>
                            <button onclick="disconnectWA()" id="btn-disconnect" class="btn btn-outline-danger btn-sm fw-bold px-3">
                                <i class="mdi mdi-link-variant-off me-1"></i> Putuskan Koneksi
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-8 col-md-7">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white py-3 border-bottom-0">
                        <h5 class="card-title mb-0 fw-bold">
                            <i class="mdi mdi-format-list-numbered me-2 text-primary fs-4"></i>Cara Menghubungkan
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="d-flex mb-4">
                                    <div class="flex-shrink-0">
                                        <span class="badge bg-primary-subtle text-primary rounded-circle p-2 fs-6" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">1</span>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h6 class="fw-bold mb-1">Buka WhatsApp</h6>
                                        <p class="text-muted small">Buka aplikasi WhatsApp di ponsel Anda.</p>
                                    </div>
                                </div>
                                <div class="d-flex mb-4">
                                    <div class="flex-shrink-0">
                                        <span class="badge bg-primary-subtle text-primary rounded-circle p-2 fs-6" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">2</span>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h6 class="fw-bold mb-1">Perangkat Tertaut</h6>
                                        <p class="text-muted small">Pilih menu <b>Perangkat Tertaut</b> pada pengaturan WhatsApp.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="d-flex mb-4">
                                    <div class="flex-shrink-0">
                                        <span class="badge bg-primary-subtle text-primary rounded-circle p-2 fs-6" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">3</span>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h6 class="fw-bold mb-1">Tautkan Perangkat</h6>
                                        <p class="text-muted small">Ketuk tombol <b>Tautkan Perangkat</b>.</p>
                                    </div>
                                </div>
                                <div class="d-flex mb-4">
                                    <div class="flex-shrink-0">
                                        <span class="badge bg-primary-subtle text-primary rounded-circle p-2 fs-6" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">4</span>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h6 class="fw-bold mb-1">Scan QR Code</h6>
                                        <p class="text-muted small">Arahkan kamera ke QR Code di samping layar ini.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-light p-3 rounded-3 mt-2 border-start border-primary border-4">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <i class="mdi mdi-lightbulb-on text-warning fs-3"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <p class="mb-0 text-dark small">
                                        <strong>Tips:</strong> Pastikan koneksi internet ponsel Anda stabil agar proses sinkronisasi berjalan lancar.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let pollingInterval;

    function checkWhatsappStatus() {
        const loading = document.getElementById('wa-loading');
        const qrArea = document.getElementById('wa-qr-area');
        const qrImage = document.getElementById('wa-qr-image');
        const successArea = document.getElementById('wa-success-area');

        fetch("{{ route('whatsapp.status') }}", {
            headers: {
                "Accept": "application/json",
                "X-Requested-With": "XMLHttpRequest"
            }
        })
        .then(response => {
            const contentType = response.headers.get("content-type");
            if (!contentType || !contentType.includes("application/json")) {
                throw new TypeError("Server tidak mengembalikan JSON. Cek login atau Route!");
            }
            return response.json();
        })
        .then(data => {
            loading.classList.add('hidden');
            
            if (data.status === true && data.url) {
                qrImage.src = "data:image/png;base64," + data.url;
                qrArea.classList.remove('hidden');
                successArea.classList.add('hidden');
            } else if (data.reason === "device already connect" || data.status === "connected") {
                qrArea.classList.add('hidden');
                successArea.classList.remove('hidden');
            }
        })
        .catch(error => {
            console.error('Status Error:', error);
            // Tetap tampilkan loading atau pesan error di UI jika perlu
        });
    }

    function disconnectWA() {
        if (!confirm('Apakah Anda yakin ingin memutuskan koneksi WhatsApp?')) return;

        const btn = document.getElementById('btn-disconnect');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Processing...';

        fetch("{{ route('whatsapp.disconnect') }}", {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            location.reload();
        })
        .catch(error => {
            console.error('Disconnect Error:', error);
            alert('Gagal memutuskan koneksi.');
            btn.disabled = false;
            btn.innerHTML = '<i class="mdi mdi-link-variant-off me-1"></i> Putuskan Koneksi';
        });
    }

    checkWhatsappStatus();
    pollingInterval = setInterval(checkWhatsappStatus, 15000);
</script>

<style>
    .hidden { display: none !important; }
    .bg-primary-subtle { background-color: rgba(0, 158, 247, 0.1) !important; } /* Sesuaikan dengan warna utama dashboard */
    .card-title i { vertical-align: middle; }
</style>
@endsection