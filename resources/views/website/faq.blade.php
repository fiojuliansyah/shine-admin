@extends('website.layouts.app')

@section('content')
    <div class="page-wrapper">
        <div class="content">

            <div class="welcome-wrap mb-4">
                <div class="d-flex align-items-center justify-content-between flex-wrap">
                    <div class="mb-3">
                        <h2 class="mb-1 text-white">Pusat Bantuan & FAQ</h2>
                        <p class="text-white">Temukan jawaban atas pertanyaan umum seputar KARYAX Karir</p>
                    </div>
                </div>
                <div class="welcome-bg">
                    <img src="/admin/assets/img/bg/welcome-bg-02.svg" alt="img" class="welcome-bg-01">
                    <img src="/admin/assets/img/bg/welcome-bg-03.svg" alt="img" class="welcome-bg-02">
                    <img src="/admin/assets/img/bg/welcome-bg-01.svg" alt="img" class="welcome-bg-03">
                </div>
            </div>

            <div class="row">
                <div class="col-xl-4">
                    <div class="card card-bg-1">
                        <div class="card-body">
                            <div class="text-center mb-4">
                                <div class="avatar avatar-xl bg-primary-transparent mb-3">
                                    <i class="ti ti-help-hexagon fs-30 text-primary"></i>
                                </div>
                                <h5>Butuh Bantuan Lebih?</h5>
                                <p class="text-muted">Jika pertanyaan Anda tidak terjawab di sini, silakan hubungi tim dukungan kami.</p>
                            </div>
                            <div class="d-grid gap-2">
                                <a href="mailto:support@shinekarir.com" class="btn btn-primary d-flex align-items-center justify-content-center">
                                    <i class="ti ti-mail me-2"></i> Hubungi Email
                                </a>
                                <a href="https://wa.me/628123456789" target="_blank" class="btn btn-success d-flex align-items-center justify-content-center text-white">
                                    <i class="ti ti-brand-whatsapp me-2"></i> WhatsApp Support
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-8">
                    <div class="card border-0">
                        <div class="card-header border-bottom">
                            <h5 class="card-title mb-0">Pertanyaan Umum</h5>
                        </div>
                        <div class="card-body">
                            <div class="accordion accordion-borderless" id="faqAccordion">
                                
                                <div class="accordion-item mb-3 border rounded">
                                    <h2 class="accordion-header" id="headingOne">
                                        <button class="accordion-button fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true">
                                            Bagaimana cara melamar pekerjaan di KARYAX Karir?
                                        </button>
                                    </h2>
                                    <div id="collapseOne" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                                        <div class="accordion-body text-gray-6">
                                            Pastikan Anda telah melengkapi profil dan mengunggah berkas yang diperlukan di menu <strong>Kelola Profil</strong>. Setelah profil lengkap, Anda dapat memilih lowongan yang tersedia dan menekan tombol "Lamar Sekarang".
                                        </div>
                                    </div>
                                </div>

                                <div class="accordion-item mb-3 border rounded">
                                    <h2 class="accordion-header" id="headingTwo">
                                        <button class="accordion-button collapsed fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo">
                                            Apa fungsi QR Code pada profil saya?
                                        </button>
                                    </h2>
                                    <div id="collapseTwo" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                        <div class="accordion-body text-gray-6">
                                            QR Code berfungsi sebagai identitas digital Anda. Anda wajib menunjukkan QR Code ini kepada petugas/HRD pada saat proses interview berlangsung untuk verifikasi data secara cepat.
                                        </div>
                                    </div>
                                </div>

                                <div class="accordion-item mb-3 border rounded">
                                    <h2 class="accordion-header" id="headingThree">
                                        <button class="accordion-button collapsed fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree">
                                            Berapa lama proses peninjauan berkas?
                                        </button>
                                    </h2>
                                    <div id="collapseThree" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                        <div class="accordion-body text-gray-6">
                                            Proses peninjauan berkas biasanya memakan waktu 3-7 hari kerja. Anda dapat memantau status lamaran Anda secara berkala pada bagian <strong>Timeline Lamaran</strong> di dashboard.
                                        </div>
                                    </div>
                                </div>

                                <div class="accordion-item mb-0 border rounded">
                                    <h2 class="accordion-header" id="headingFour">
                                        <button class="accordion-button collapsed fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour">
                                            Dapatkah saya melamar lebih dari satu posisi?
                                        </button>
                                    </h2>
                                    <div id="collapseFour" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                        <div class="accordion-body text-gray-6">
                                            Ya, Anda dapat melamar lebih dari satu posisi selama kualifikasi Anda memenuhi kriteria yang dibutuhkan oleh perusahaan.
                                        </div>
                                    </div>
                                </div>

                            </div> </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection

@push('css')
<style>
    .accordion-button:not(.collapsed) {
        background-color: rgba(var(--bs-primary-rgb), 0.05);
        color: var(--bs-primary);
        box-shadow: none;
    }
    .accordion-button:focus {
        box-shadow: none;
        border-color: rgba(0,0,0,.125);
    }
    .accordion-item {
        overflow: hidden;
    }
</style>
@endpush