@extends('website.layouts.guest')


@section('content')
<div class="main-wrapper">

    <div class="container-fuild">
        <div class="w-100 overflow-hidden position-relative flex-wrap d-block vh-100">
            <div class="row">
                <div class="col-lg-5">
                    <div class="d-lg-flex align-items-center justify-content-center d-none flex-wrap vh-100 bg-primary-transparent">
                        <div>
                            <img src="/admin/assets/img/bg/authentication-bg-03.svg" alt="Img">
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
                                        <img src="/admin/assets/img/logo-dark.svg" class="img-fluid" alt="Logo">
                                    </div>
                                    <div class="">
                                        <div class="text-center mb-3">
                                            <h2 class="mb-2">Masuk Pelamar</h2>
                                            <p class="mb-0">Sebelum melamar silahkan login terlebih dahulu!</p>
                                        </div>

                                        @if(session('error'))
                                            <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
                                                <p class="mb-0 fs-12">{{ session('error') }}</p>
                                                <button type="button" class="btn-close custom-btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                            </div>
                                        @endif

                                        <div class="mb-3">
                                            <label class="form-label">Email atau No Whatsapp</label>
                                            @error('login')
                                                <div class="text-danger fs-12 mb-1">{{ $message }}</div>
                                            @enderror
                                            <div class="input-group">
                                                <input type="text" name="login" class="form-control border-end-0 @error('login') is-invalid @enderror" value="{{ old('login') }}">
                                                <span class="input-group-text border-start-0 @error('login') border-danger @enderror">
                                                    <i class="ti ti-user"></i>
                                                </span>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Password</label>
                                            @error('password')
                                                <div class="text-danger fs-12 mb-1">{{ $message }}</div>
                                            @enderror
                                            <div class="pass-group">
                                                <input type="password" name="password" class="pass-input form-control @error('password') is-invalid @enderror">
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
                                            <div class="text-end">
                                                <a href="forgot-password-2.html" class="link-danger">Lupa Password?</a>
                                            </div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <button type="submit" class="btn btn-primary w-100">Masuk</button>
                                        </div>
                                        </div>
                                    <div class="mt-5 pb-4 text-center">
                                        <p class="mb-0 text-gray-9">Copyright &copy; 2025 - SHINE</p>
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