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
                            <form action="{{ route('applicant-reset-store') }}" method="POST" class="vh-100">
                                @csrf
                                <div class="vh-100 d-flex flex-column justify-content-between p-4 pb-0">
                                    <div class=" mx-auto mb-5 text-center">
                                        <img src="/admin/assets/img/logo-dark-ciptakarir.svg" class="img-fluid" alt="Logo" width="150">
                                    </div>
                                    <div class="">
                                        <div class="text-center mb-3">
                                            <h2 class="mb-2">Reset Password</h2>
                                            <p class="mb-0">Silakan buat password baru untuk akun Anda.</p>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Password Baru</label>
                                            <div class="pass-group">
                                                <input type="password" name="password" class="pass-input form-control" required>
                                                <span class="ti toggle-password ti-eye-off"></span>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Konfirmasi Password</label>
                                            <div class="pass-group">
                                                <input type="password" name="password_confirmation" class="pass-inputs form-control" required>
                                                <span class="ti toggle-passwords ti-eye-off"></span>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <button type="submit" class="btn btn-primary w-100">Simpan Password Baru</button>
                                        </div>
                                        <div class="text-center">
                                            <h6 class="fw-normal text-dark mb-0">
                                                <a href="{{ route('applicant-login') }}" class="hover-a">Kembali ke Masuk</a>
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

@include('website.auth.partials.alert-modal')
@endsection
