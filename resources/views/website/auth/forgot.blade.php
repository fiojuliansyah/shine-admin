@extends('website.layouts.guest')

@section('content')
<div class="main-wrapper">
    <div class="container-fuild">
        <div class="w-100 overflow-hidden position-relative flex-wrap d-block vh-100">
            <div class="row">
                <div class="col-lg-5">
                    <div class="d-lg-flex align-items-center justify-content-center d-none flex-wrap vh-100 bg-primary">
                        <div>
                            <img src="/admin/assets/img/system-cover.png" alt="Img">
                        </div>
                    </div>
                </div>
                <div class="col-lg-7 col-md-12 col-sm-12">
                    <div class="row justify-content-center align-items-center vh-100 overflow-auto flex-wrap ">
                        <div class="col-md-7 mx-auto vh-100">
                            <form action="{{ route('applicant-forgot-send') }}" method="POST" class="vh-100">
                                @csrf
                                <div class="vh-100 d-flex flex-column justify-content-between p-4 pb-0">
                                    <div class=" mx-auto mb-5 text-center">
                                        <img src="/admin/assets/img/logo-dark-ciptakarir.svg" class="img-fluid" alt="Logo" width="150">
                                    </div>
                                    <div class="">
                                        @php
                                            $waDown = isset($waConnected) && ! $waConnected;
                                            $channel = old('channel', request('via') === 'email' || $waDown ? 'email' : 'whatsapp');
                                        @endphp
                                        <div class="text-center mb-3">
                                            <h2 class="mb-2">Lupa Password</h2>
                                            <p class="mb-0">Pilih cara menerima kode OTP untuk mereset password Anda.</p>
                                        </div>

                                        <input type="hidden" name="channel" id="otpChannel" value="{{ $channel }}">

                                        <ul class="nav nav-pills nav-justified bg-light rounded p-1 mb-3" role="tablist">
                                            <li class="nav-item" role="presentation">
                                                <button type="button" class="nav-link {{ $channel === 'whatsapp' ? 'active' : '' }}"
                                                    data-bs-toggle="pill" data-bs-target="#otpViaWa" data-channel="whatsapp" role="tab">
                                                    <i class="ti ti-brand-whatsapp me-1"></i>WhatsApp
                                                </button>
                                            </li>
                                            <li class="nav-item" role="presentation">
                                                <button type="button" class="nav-link {{ $channel === 'email' ? 'active' : '' }}"
                                                    data-bs-toggle="pill" data-bs-target="#otpViaEmail" data-channel="email" role="tab">
                                                    <i class="ti ti-mail me-1"></i>Email
                                                </button>
                                            </li>
                                        </ul>

                                        <div class="tab-content">
                                            <div class="tab-pane fade {{ $channel === 'whatsapp' ? 'show active' : '' }}" id="otpViaWa" role="tabpanel">
                                                @if ($waDown)
                                                    <div class="alert alert-warning py-2 small">
                                                        Layanan OTP WhatsApp sedang tidak tersedia. Silakan gunakan <strong>Email</strong>,
                                                        atau hubungi HRD / admin untuk mereset password Anda.
                                                    </div>
                                                @endif
                                                <div class="mb-3">
                                                    <label class="form-label">Nomor WhatsApp</label>
                                                    <div class="input-group">
                                                        <input type="text" name="phone" class="form-control border-end-0" placeholder="0812345678910" value="{{ old('phone') }}">
                                                        <span class="input-group-text border-start-0">
                                                            <i class="ti ti-phone"></i>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="tab-pane fade {{ $channel === 'email' ? 'show active' : '' }}" id="otpViaEmail" role="tabpanel">
                                                <div class="mb-3">
                                                    <label class="form-label">Alamat Email</label>
                                                    <div class="input-group">
                                                        <input type="email" name="email" class="form-control border-end-0" placeholder="nama@email.com" value="{{ old('email') }}">
                                                        <span class="input-group-text border-start-0">
                                                            <i class="ti ti-mail"></i>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <button type="submit" class="btn btn-primary w-100">Kirim Kode OTP</button>
                                        </div>
                                        <div class="text-center">
                                            <h6 class="fw-normal text-dark mb-0">Ingat password Anda?
                                                <a href="{{ route('applicant-login') }}" class="hover-a">Masuk</a>
                                            </h6>
                                        </div>
                                    </div>
                                    <div class="mt-5 pb-4 text-center">
                                        <p class="mb-0 text-gray-9">Copyright &copy; 2026 - Cipta Karir</p>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<script>
    document.addEventListener('DOMContentLoaded', function () {
        var channelInput = document.getElementById('otpChannel');
        var waInput = document.querySelector('input[name="phone"]');
        var emailInput = document.querySelector('input[name="email"]');

        function syncChannel(channel) {
            channelInput.value = channel;
            // Hanya input di tab aktif yang wajib diisi
            waInput.required = channel === 'whatsapp';
            emailInput.required = channel === 'email';
        }

        document.querySelectorAll('[data-channel]').forEach(function (btn) {
            btn.addEventListener('click', function () { syncChannel(btn.dataset.channel); });
        });
        syncChannel(channelInput.value);
    });
</script>
@endsection
