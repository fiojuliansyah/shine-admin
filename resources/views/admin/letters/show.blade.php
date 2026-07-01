@extends('admin.layouts.main')

@section('content')
<div class="page-wrapper">
    <div class="content">
        <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
            <div class="my-auto mb-2">
                <h2 class="mb-1">Detail Template</h2>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('letters.index') }}"><i class="ti ti-smart-home"></i></a></li>
                        <li class="breadcrumb-item">E-Recruitment</li>
                        <li class="breadcrumb-item active">Detail Template</li>
                    </ol>
                </nav>
            </div>
            <div class="d-flex my-xl-auto right-content align-items-center flex-wrap gap-2">
                <a href="{{ route('letters.edit', $letter->id) }}" target="_blank" class="btn btn-outline-primary mb-2">
                    <i class="ti ti-edit me-1"></i>Edit Template
                </a>
                <a href="{{ route('letters.print', $letter->id) }}" target="_blank" class="btn btn-danger mb-2">
                    <i class="ti ti-printer me-1"></i>Print / PDF
                </a>
            </div>
        </div>

        <div class="card border-0 shadow-sm" style="height: calc(100vh - 180px); min-height: 600px;">
            <div class="card-body p-0" style="height:100%;">
                <iframe
                    src="{{ route('letters.print', ['letter' => $letter->id, 'preview' => 1]) }}"
                    style="width:100%; height:100%; border:none; border-radius: 0 0 8px 8px;"
                    title="{{ $letter->title }}"
                ></iframe>
            </div>
        </div>
    </div>
</div>
@endsection
