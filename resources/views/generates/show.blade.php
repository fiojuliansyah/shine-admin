@extends('layouts.main')

@section('content')
<div class="main-content">

    <div class="page-content">

        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-flex align-items-center justify-content-between">
                    <h4 class="page-title mb-0 font-size-18">Generate</h4>

                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="javascript: void(0);">HR</a></li>
                            <li class="breadcrumb-item active">Detail Surat</li>
                        </ol>
                    </div>

                </div>
            </div>
        </div>
        <!-- end page title -->

        
        <div class="card">
            <div class="card-body" style="background: #eceef4; min-height: 100%; padding: 0.5rem;">
                <div id="letter-template" style="background-color: #fff;
                box-sizing: border-box;
                margin: 1rem auto 0;
                max-width: 820px;
                min-height: calc(100vh - 1rem);
                padding: 2rem 6rem 2rem 6rem;">
                    {!! $generate->letter->description !!}
                </div>
            </div>
        </div>
        
        <!-- end row -->

    </div>
</div>
@endsection
