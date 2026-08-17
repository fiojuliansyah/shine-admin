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
                            <form action="{{ route('applicant-login-store') }}" method="POST" class="vh-100">
                                @csrf
                                <div class="vh-100 d-flex flex-column justify-content-between p-4 pb-0">
                                    <div class=" mx-auto mb-5 text-center">
                                        <img src="/admin/assets/img/logo-dark-ciptakarir.svg" class="img-fluid" alt="Logo" width="150">
                                    </div>
                                    <div class="">
                                        <div class="text-center mb-3">
                                            <h2 class="mb-2">Masuk Pelamar</h2>
                                            <p class="mb-0">Sebelum melamar silahkan login terlebih dahulu!</p>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Email atau No Whatsapp</label>
                                            <div class="input-group">
                                                <input type="text" name="login" class="form-control border-end-0" value="{{ old('login') }}" required>
                                                <span class="input-group-text border-start-0">
                                                    <i class="ti ti-user"></i>
                                                </span>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Password</label>
                                            <div class="pass-group">
                                                <input type="password" name="password" class="pass-input form-control" required>
                                                <span class="ti toggle-password ti-eye-off"></span>
                                            </div>
                                        </div>

                                        <div class="d-flex align-items-center justify-content-between mb-3">
                                            <div class="d-flex align-items-center">
                                                <div class="form-check form-check-md mb-0">
                                                    <input class="form-check-input" name="remember" id="remember_me" type="checkbox">
                                                    <label for="remember_me" class="form-check-label mt-0">Ingat saya</label>
                                                </div>
                                            </div>
                                            <a href="{{ route('applicant-forgot') }}" class="hover-a">Lupa Password?</a>
                                        </div>

                                        <div class="mb-3">
                                            <button type="submit" class="btn btn-primary w-100">Masuk</button>
                                        </div>
                                        <div class="text-center">
                                            <h6 class="fw-normal text-dark mb-0">Belum punya akun?
                                                <a href="{{ route('applicant-register') }}" class="hover-a">Daftar Pelamar</a>
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

<div class="modal fade" id="loginErrorModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger">
                <h5 class="modal-title text-white">Login Gagal</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <ul class="mb-0">
                    @if($errors->any())
                        @foreach ($errors->all() as $error)
                            <li class="text-danger">{{ $error }}</li>
                        @endforeach
                    @endif
                    
                    @if(session('error'))
                        <li class="text-danger">{{ session('error') }}</li>
                    @endif
                </ul>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Coba Lagi</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
    $(document).ready(function() {
        @if($errors->any() || session('error'))
            var errorModal = new bootstrap.Modal(document.getElementById('loginErrorModal'));
            errorModal.show();
        @endif
    });
</script>
@endpush