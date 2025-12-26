@extends('layouts.main')

@section('content')
<div class="app-main flex-column flex-row-fluid" id="kt_app_main">
    <div class="d-flex flex-column flex-column-fluid">
        <div id="kt_app_content" class="app-content flex-column-fluid">
            <div id="kt_app_content_container" class="app-container container-fluid">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-6">
                                <div class="row">
                                    <div class="col-6">
                                        <h1>Regenerate Letter</h1>
                                    </div>
                                    <div class="col-6">
                                        <div class="d-flex align-items-center gap-2 gap-lg-3">
                                            <button class="btn btn-flex btn-primary h-30px fs-7 fw-bold">VARIABLES</a>
                                        </div>
                                    </div>
                                </div>
                                <div class="app-sidebar-separator separator mx-4 mt-7 mb-7"></div>
                                <form class="form" action="{{ route('generates.update', ['generate' => $generate->id]) }}" method="POST" enctype="multipart/form-data">
                                    @method('PUT')
                                    @csrf
                                    <!--begin::Scroll-->
                                    <div class="d-flex flex-column scroll-y px-5 px-lg-10" id="kt_modal_add_user_scroll" data-kt-scroll="true" data-kt-scroll-activate="true" data-kt-scroll-max-height="auto" data-kt-scroll-dependencies="#kt_modal_add_user_header" data-kt-scroll-wrappers="#kt_modal_add_user_scroll" data-kt-scroll-offset="300px">
                                        <div class="fv-row mb-7">
                                            <label class="required fw-semibold fs-6 mb-2">Select Letter</label>
                                            <select class="form-select form-control-lg form-control-solid" data-kt-select2="true" name="letter_id" data-placeholder="Select an option">
                                                <option>Pilih</option>
                                                @foreach($letters as $letter)
                                                <option value="{{ $letter->id }}" {{ $generate->letter_id == $letter->id ? 'selected' : '' }}>{{ $letter->title }}</option>
                                                @endforeach
                                            </select>                                        
                                        </div>
                                        <div class="fv-row mb-7">
                                            <label class="required fw-semibold fs-6 mb-2">Select User</label>
                                            <select class="form-select form-control-lg form-control-solid" data-kt-select2="true" name="user_id" data-placeholder="Select an option">
                                                <option>Pilih</option>
                                                @foreach($users as $user)
                                                <option value="{{ $user->id }}" {{ $generate->user_id == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                                                @endforeach
                                            </select>                                        
                                        </div>
                                        <div class="fv-row mb-7">
                                            <label class="fw-semibold fs-6 mb-2">Description</label>
                                            <textarea rows="10" class="mb-3 d-none" name="description" id="kt_docs_tinymce_basic">{!! $generate->description !!}</textarea>
                                        </div>
                                    </div>
                                    <div class="text-center pt-10">
                                        <button type="submit" class="btn btn-primary">
                                            <span class="indicator-label">Generete</span>
                                            <span class="indicator-progress">Please wait... 
                                            <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                                        </button>
                                    </div>
                                </form>
                            </div>
                            <div class="col-6">
                                <div class="row">
                                    <div class="col-6">
                                        <h1>Preview Letter</h1>
                                    </div>
                                    <div class="col-6">
                                        <div class="d-flex align-items-center gap-2 gap-lg-3">
                                            <button onclick="saveDiv('pdf','{{ $generate->user['name'] }} - {{ $generate->letter['title'] }}')" class="btn btn-flex btn-outline btn-color-gray-700 btn-active-color-primary bg-body h-30px fs-7 fw-bold">DOWNLOAD</a>
                                            <button onclick="printDiv('pdf','{{ $generate->user['name'] }} - {{ $generate->letter['title'] }}')" class="btn btn-flex btn-primary h-30px fs-7 fw-bold">PRINT</a>
                                        </div>
                                    </div>
                                </div>
                                <div class="app-sidebar-separator separator mx-4 mt-7 mb-7"></div>
                                <div>
                                    <div class="card">
                                        <div id="pdf" class="card-body" style="background-color: white;">
                                            {!! $generate->description !!}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@push('js')
<script src="/assets/plugins/custom/tinymce/tinymce.bundle.js"></script>
<script>
    var options = {selector: "#kt_docs_tinymce_basic", height : "480"};

    options["skin"] = "oxide";
    options["content_css"] = "default";

    tinymce.init(options);
</script>
<script>
    var doc = new jsPDF();

    function saveDiv(divId, title) {
    doc.fromHTML(`<html><head><title>${title}</title></head><body>` + document.getElementById(divId).innerHTML + `</body></html>`);
    doc.save('title.pdf');
    }

    function printDiv(divId,
    title) {

    let mywindow = window.open('', 'PRINT', 'height=650,width=900,top=100,left=150');

    mywindow.document.write(`<html><head><title>${title}</title>`);
    mywindow.document.write('</head><body >');
    mywindow.document.write(document.getElementById(divId).innerHTML);
    mywindow.document.write('</body></html>');

    mywindow.document.close(); // necessary for IE >= 10
    mywindow.focus(); // necessary for IE >= 10*/

    mywindow.print();
    mywindow.close();

    return true;
    }
</script>
@endpush