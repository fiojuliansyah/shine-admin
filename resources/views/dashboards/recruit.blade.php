@extends('layouts.main')

@section('content')
<div class="page-wrapper">
    <div class="content">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-flex align-items-center justify-content-between">
                    <h4 class="page-title mb-0 font-size-18">Dashboard</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item active">Welcome to Treework Dashboard</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-xl-3">
                <a href="{{ route('careers.index') }}" class="card bg-light hoverable card-xl-stretch mb-xl-8">
                    <div class="card-body">
                        <i class="mdi mdi-briefcase text-primary fs-2x"></i>
                        <div class="text-gray-900 fw-bold fs-2 mb-2 mt-5">{{ $career }}</div>
                        <div class="fw-semibold text-gray-400">Lowongan Pekerjaan</div>
                    </div>
                </a>
            </div>
            <div class="col-xl-3">
                <a href="{{ route('applicants.index') }}" class="card bg-body hoverable card-xl-stretch mb-xl-8">
                    <div class="card-body">
                        <i class="mdi mdi-bookmark-outline text-primary fs-2x"></i>
                        <div class="text-gray-900 fw-bold fs-2 mb-2 mt-5">{{ $applicant }}</div>
                        <div class="fw-semibold text-gray-400">Pemberkasan</div>
                    </div>
                </a>
            </div>
            @foreach ($statuses as $status)
                <div class="col-xl-3">
                    <a href="{{ route('statuses.show',$status->name) }}" target="_blank" class="card bg-{{ $status->color }} hoverable card-xl-stretch mb-xl-8">
                        <div class="card-body position-relative">
                            <i class="mdi mdi-bookmark text-white fs-2x"></i>
                            @if ($status->unapprovedApplicants() && $status->unapprovedApplicants()->count() > 0)  
                                <div class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">{{ $status->unapprovedApplicants()->count() }}</div>
                            @endif
                            <div class="text-gray-900 fw-bold fs-2 mb-2 mt-5">
                                {{ $applicantCounts[$status->id] }}
                            </div>
                            <div class="fw-semibold text-gray-100">{{ $status->name }}</div>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
