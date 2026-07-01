@extends('admin.layouts.main')

@section('content')
<div class="page-wrapper">
    <div class="content">
        <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
            <div class="my-auto mb-2">
                <h2 class="mb-1">Buat Template E-Letter (Teks)</h2>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('letters.index') }}"><i class="ti ti-smart-home"></i></a></li>
                        <li class="breadcrumb-item">E-Recruitment</li>
                        <li class="breadcrumb-item active">Buat Template</li>
                    </ol>
                </nav>
            </div>
            <div class="d-flex my-xl-auto right-content align-items-center flex-wrap gap-2">
                <a href="{{ route('letters.index') }}" class="btn btn-light mb-2"><i class="ti ti-arrow-left me-1"></i>Kembali</a>
                <button type="button" class="btn btn-primary mb-2" onclick="submitLetterForm()"><i class="ti ti-device-floppy me-1"></i>Simpan Template</button>
            </div>
        </div>

        <form id="letterForm" action="{{ route('letters.store') }}" method="POST">
            @csrf
            <input type="hidden" name="editor_type" value="text">
            <input type="hidden" name="description" id="descriptionHidden">
            <div id="customVarsContainer"></div>

            <div class="row g-3">
                {{-- LEFT: form fields --}}
                <div class="col-lg-3">
                    @include('admin.letters.partials.text-sidebar', ['letter' => null])
                </div>

                {{-- CENTER: editor --}}
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <textarea id="letterEditor" name="editor_content"></textarea>
                        </div>
                    </div>
                </div>

                {{-- RIGHT: variables --}}
                <div class="col-lg-3">
                    @include('admin.letters.partials.text-variables', ['letter' => null])
                </div>
            </div>
        </form>
    </div>
</div>

{{-- MODAL: ADD CUSTOM VARIABLE --}}
@include('admin.letters.partials.text-custom-var-modal', ['letter' => null])
@endsection

@push('js')
<script src="/admin/assets/libs/tinymce/tinymce.min.js"></script>
<script>
    @include('admin.letters.partials.text-upload-js')

    tinymce.init({
        selector: '#letterEditor',
        height: 1000,
        menubar: 'edit view insert format table',
        plugins: 'advlist autolink lists link image charmap preview anchor searchreplace visualblocks code fullscreen insertdatetime media table help wordcount pagebreak',
        toolbar: 'undo redo | blocks fontfamily fontsizeinput | bold italic underline forecolor backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | table image floatbox floatimage pagebreak | removeformat | code fullscreen',
        toolbar_mode: 'sliding',
        font_family_formats: 'Arial=arial,helvetica,sans-serif; Times New Roman=times new roman,times,serif; Calibri=calibri; Courier New=courier new,courier,monospace; Georgia=georgia,serif; Verdana=verdana,geneva,sans-serif',
        content_style: '*{box-sizing:border-box;} body{font-family:Arial,sans-serif;font-size:14px;line-height:1.6;width:794px;box-sizing:border-box;margin:0 auto;padding:60px 80px;background:#fff;position:relative;} table{border-collapse:collapse;width:100%;} td,th{border:1px solid #ccc;padding:6px 8px;} .floating-box{border:1px solid #0d6efd;background:rgba(13,110,253,0.03);box-sizing:border-box;padding:8px;} .floating-box .fb-drag{position:absolute;top:-20px;left:0;font-size:10px;background:#0d6efd;color:#fff;padding:1px 6px;border-radius:3px 3px 0 0;cursor:move;user-select:none;} .floating-box .fb-body{outline:none;min-height:20px;} .floating-box .fb-resize{position:absolute;right:-4px;bottom:-4px;width:12px;height:12px;background:#0d6efd;border:2px solid #fff;border-radius:2px;cursor:nwse-resize;} .floating-image{border:1px dashed #0d6efd;background:transparent;padding:0;} .floating-image .fb-body{min-height:auto;} .floating-image img{display:block;width:100%;height:auto;}',
        pagebreak_separator: '<div style="page-break-after:always;"></div>',
        extended_valid_elements: 'div[class|style|contenteditable|data-mce-style],span[class|style|contenteditable|data-mce-style],img[class|style|src|alt|width|height]',
        valid_children: '+body[div],+div[div|span|img|p|table|ul|ol|h1|h2|h3|h4|h5|h6]',
        images_upload_handler: letterImageUploadHandler,
        automatic_uploads: true,
        file_picker_types: 'image',
        relative_urls: false,
        remove_script_host: false,
        setup: function (editor) {
            window._letterEditor = editor;
            editor.ui.registry.addButton('floatbox', {
                icon: 'comment-add',
                tooltip: 'Sisipkan Textbox (posisi bebas)',
                onAction: function () { insertFloatBox(editor); },
            });
            editor.ui.registry.addButton('floatimage', {
                icon: 'image',
                tooltip: 'Sisipkan Gambar (posisi bebas)',
                onAction: function () { insertFloatImage(editor); },
            });
            editor.on('init', function () { initFloatingBoxes(editor); });
        },
    });

    function insertVar(val) {
        if (window._letterEditor) window._letterEditor.execCommand('mceInsertContent', false, val);
    }

    function submitLetterForm() {
        const ed = window._letterEditor;
        if (ed) {
            ed.getBody().querySelectorAll('.floating-box').forEach(function (el) {
                el.setAttribute('data-mce-style', el.style.cssText);
            });
        }
        const html = ed ? ed.getContent() : '';
        if (!html || html.trim() === '') { alert('Konten surat tidak boleh kosong.'); return; }
        document.getElementById('descriptionHidden').value = html;
        document.getElementById('letterForm').submit();
    }

    @include('admin.letters.partials.text-scripts-js')
    @include('admin.letters.partials.text-floatbox-js')
</script>
@endpush
