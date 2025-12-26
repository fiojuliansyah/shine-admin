@extends('website.layouts.app')

@section('content')
<div class="page-wrapper">
    <div class="content">

        <!-- Breadcrumb -->
        <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
            <div class="my-auto mb-2">
                <h2 class="mb-1">{{ $career->name }}</h2>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item">
                            <a href="{{ url('/') }}"><i class="ti ti-smart-home"></i></a>
                        </li>
                        <li class="breadcrumb-item">Lowongan Pekerjaan</li>
                        <li class="breadcrumb-item active" aria-current="page">{{ $career->name }}</li>
                    </ol>
                </nav>
            </div>
        </div>
        <!-- /Breadcrumb -->

        <div class="card">
            <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <h5>{{ $career->name }}</h5>
                    <span class="badge bg-success">Open</span> <!-- Status lowongan, bisa diubah -->
                </div>
            </div>
        </div>

        <!-- Detail Career -->
        <div class="row">
            <div class="col-md-8">
                <div class="card mb-3">
                    <div class="card-header">
                        <strong>Deskripsi Lowongan</strong>
                    </div>
                    <div class="card-body">
                        <p>{!! $career->description !!}</p>
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-header">
                        <strong>Persyaratan</strong>
                    </div>
                    <div class="card-body">
                        <ul>
                            <li><strong>Pengalaman:</strong> {{ $career->experience }}</li>
                            <li><strong>Lulusan:</strong> {{ $career->graduate }}</li>
                            <li><strong>Jurusan:</strong> {{ $career->major }}</li>
                            <li><strong>Fungsi Pekerjaan:</strong> {{ $career->workfunction }}</li>
                        </ul>
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-header">
                        <strong>Gaji</strong>
                    </div>
                    <div class="card-body">
                        <p>Rp {{ number_format($career->salary, 0, '.', ',') }}</p>
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-header">
                        <strong>Lokasi</strong>
                    </div>
                    <div class="card-body">
                        <p>{{ $career->location }}</p>
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-header">
                        <strong>Sampai Tanggal</strong>
                    </div>
                    <div class="card-body">
                        <p>{{ \Carbon\Carbon::parse($career->until_date)->format('d M, Y') }}</p>
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-header">
                        <strong>Kandidat yang di Perlukan</strong>
                    </div>
                    <div class="card-body">
                        <p>{{ $career->candidate }} Kandidat</p>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <!-- Side Bar atau Tombol Apply -->
                <div class="card">
                    <div class="card-header">
                        <strong>Apply sekarang</strong>
                    </div>
                    <div class="card-body text-center">
                        <form action="{{ route('web.applicants.career.apply', $career->slug) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-primary">Apply</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
