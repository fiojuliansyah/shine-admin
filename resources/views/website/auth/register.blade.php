@extends('website.layouts.guest')


@section('content')
<div class="main-wrapper">

    <div class="container-fuild">
        <div class="w-100 overflow-hidden position-relative flex-wrap d-block vh-100">
            <div class="row">
                <div class="col-lg-5">
                    <div class="d-lg-flex align-items-center justify-content-center d-none flex-wrap vh-100 bg-primary-transparent">
                        <div>
                            <img src="/admin/assets/img/bg/authentication-bg-02.svg" alt="Img">
                        </div>
                    </div>
                </div>
                <div class="col-lg-7 col-md-12 col-sm-12">
                    <div class="row justify-content-center align-items-center vh-100 overflow-auto flex-wrap ">
                        <div class="col-md-7 mx-auto vh-100">
                            <form action="{{ route('applicant-register-store') }}" method="POST" class="vh-100">
                                @csrf
                                <div class="vh-100 d-flex flex-column justify-content-between p-4 pb-0">
                                    <div class=" mx-auto mb-5 text-center">
                                        <img src="/admin/assets/img/logo-dark.svg" class="img-fluid" alt="Logo">
                                    </div>
                                    <div class="">
                                        <div class="text-center mb-3">
                                            <h2 class="mb-2">Daftar Pelamar</h2>
                                            <p class="mb-0">Mohon masukan data yang sebenar-benarnya!</p>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Nama</label>
                                            <div class="input-group">
                                                <input type="text" name="name" class="form-control border-end-0" placeholder="User Pelamar" required>
                                                <span class="input-group-text border-start-0">
                                                    <i class="ti ti-user"></i>
                                                </span>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Alamat Email</label>
                                            <div class="input-group">
                                                <input type="text" name="email" class="form-control border-end-0" placeholder="pelamar@gmail.com" required>
                                                <span class="input-group-text border-start-0">
                                                    <i class="ti ti-mail"></i>
                                                </span>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Nomor Whatsapp/Handphone</label>
                                            <div class="input-group">
                                                <input type="text" name="phone" class="form-control border-end-0" placeholder="0812345678910" required>
                                                <span class="input-group-text border-start-0">
                                                    <i class="ti ti-phone"></i>
                                                </span>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Password</label>
                                            <div class="pass-group">
                                                <input type="password" name="password" class="pass-input form-control">
                                                <span class="ti toggle-password ti-eye-off"></span>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Konfirmasi Password</label>
                                            <div class="pass-group">
                                                <input type="password" name="password_confirmation" class="pass-inputs form-control">
                                                <span class="ti toggle-passwords ti-eye-off"></span>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <button type="submit" class="btn btn-primary w-100">Daftar</button>
                                        </div>
                                        <div class="text-center">
                                            <h6 class="fw-normal text-dark mb-0">Sudah punya akun?
                                                <a href="{{ route('applicant-login') }}" class="hover-a">Masuk</a>
                                            </h6>
                                        </div>
                                        <div class="login-or">
                                            <span class="span-or">Or</span>
                                        </div>
                                        <div class="mt-2">
                                            <div class="d-flex align-items-center justify-content-center flex-wrap">
                                                <div class="text-center me-2 flex-fill">
                                                    <a href="javascript:void(0);"
                                                        class="br-10 p-2 btn btn-outline-light border d-flex align-items-center justify-content-center">
                                                        <img class="img-fluid m-1" src="/admin/assets/img/icons/google-logo.svg" alt="Facebook">
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-5 pb-4 text-center">
                                        <p class="mb-0 text-gray-9">Copyright &copy; 2025 - KARYAX</p>
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
@endsection