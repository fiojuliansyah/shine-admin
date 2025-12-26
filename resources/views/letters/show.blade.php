@extends('layouts.main')

@section('content')
<div class="page-wrapper" style="background: #eceef4; padding: 2rem;">
    <div class="content">
        <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
            <div class="my-auto mb-2">
                <h2 class="mb-1">Detail Template</h2>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item">
                            <a href="{{ route('letters.index') }}"><i class="ti ti-smart-home"></i></a>
                        </li>
                        <li class="breadcrumb-item">E-Recruitment</li>
                        <li class="breadcrumb-item active" aria-current="page">Detail Template</li>
                    </ol>
                </nav>
            </div>
            <div class="d-flex my-xl-auto right-content align-items-center flex-wrap">
                <div class="mb-2">
                    <a href="{{ route('letters.edit', $letter->id) }}" class="btn btn-primary">Edit Template</a>
                </div>
            </div>
        </div>

        <!-- Word-style Page -->
        <div class="card mx-auto" style="max-width: 820px; box-shadow: 0 0 10px rgba(0,0,0,0.15);">
            <div class="card-body" style="background-color: #fff; padding: 2rem 3rem; min-height: 90vh; font-family: 'Calibri', sans-serif; line-height: 1.5;">
                
                <!-- Header -->
                <div class="header" style="display:flex; justify-content: space-between; margin-bottom: 2rem; color: #666; font-size: 0.85rem;">
                    <div class="left-text">Company Name</div>
                    <div class="right-text">{{ date('d F Y') }}</div>
                </div>

                <!-- Content Info -->
                {{-- <div class="mb-4">
                    <h3 style="margin-bottom: 0.5rem;">{{ $letter->title }}</h3>
                    <p style="margin:0.2rem 0;"><strong>Site:</strong> {{ $letter->site->name ?? '-' }}</p>
                    <p style="margin:0.2rem 0;"><strong>Tipe Template:</strong> {{ $letter->type->name ?? '-' }}</p>
                </div> --}}

                {{-- <hr style="border: 1px solid #ddd; margin-bottom: 2rem;"> --}}

                <!-- Description -->
                <div style="min-height: 400px; font-size: 1rem;">
                    {!! $letter->description !!}
                </div>

                <!-- Footer -->
                {{-- <div class="footer" style="margin-top: 3rem; text-align:center; font-size:0.8rem; color:#999;">
                    &copy; {{ date('Y') }} Company Name. All rights reserved.
                </div> --}}

            </div>
        </div>

    </div>
</div>
@endsection

@push('js')
<script src="/admin/assets/libs/tinymce/tinymce.min.js"></script>
<script>
    tinymce.init({
        selector: '#description-show',
        readonly: true,
        menubar: false,
        toolbar: false,
        height: 400
    });
</script>
@endpush
