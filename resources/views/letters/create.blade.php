@extends('layouts.main')

@section('content')
<div class="page-wrapper">
    <div class="content">

        <!-- Breadcrumb -->
        <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
            <div class="my-auto mb-2">
                <h2 class="mb-1">List Template</h2>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item">
                            <a href="index.html"><i class="ti ti-smart-home"></i></a>
                        </li>
                        <li class="breadcrumb-item">E-Recruitment</li>
                        <li class="breadcrumb-item active" aria-current="page">Template</li>
                    </ol>
                </nav>
            </div>
            <div class="d-flex my-xl-auto right-content align-items-center flex-wrap">
                <div class="mb-2">
                    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#lihatVariable">List Variable</button>
                </div>
            </div>
        </div>
        <!-- /Breadcrumb -->

        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
                <h5>Buat E-Letter</h5>
            </div>
            <div class="card-body p-0">
                <form id="letterForm" action="{{ route('letters.store') }}" method="POST">
                    @csrf
                    <div class="row p-3">
                        <div class="col-md-4">
                            <label class="form-label">Site</label>
                            <select class="select2 form-select" name="site_id" required>
                                @foreach ($sites as $site)
                                    <option value="{{ $site->id }}">{{ $site->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Nama</label>
                            <input type="text" class="form-control" name="title" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Tipe Template</label>
                            <select class="form-select" name="type_letter_id" required>
                                @foreach ($types as $type)
                                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="p-3">
                        <textarea class="form-control" id="description" name="description" required></textarea>
                    </div>
                    <div class="p-3">
                        <button type="submit" class="btn btn-primary">Buat Letter</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal List Variable -->
<div class="modal fade" id="lihatVariable" tabindex="-1" aria-labelledby="lihatVariableLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="lihatVariableLabel">Variable List</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                @include('letters.partials.variable-list')
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script src="/admin/assets/libs/tinymce/tinymce.min.js"></script>
<script>
    tinymce.init({
        selector: "#description",
        plugins: "anchor autolink autosave charmap codesample directionality emoticons help image insertdatetime link lists media nonbreaking pagebreak searchreplace table visualblocks visualchars wordcount",
        toolbar: "undo redo spellcheckdialog | aidialog aishortcuts | blocks fontfamily fontsizeinput | bold italic underline forecolor backcolor | link image addcomment showcomments  | align lineheight checklist bullist numlist | indent outdent | inserttemplate | removeformat typography math",
        editable_root: false,
        height: '700px',
        toolbar_sticky: true,
        autosave_restore_when_empty: true,
        content_style: `
            body {
            background: #fff;
            }
            .editable-section:focus-visible {
            outline: none !important;
            }
            .header,
            .footer {
            font-size: 0.8rem;
            color: #ddd;
            }
            .header {
            display: flex;
            justify-content: space-between;
            padding: 0 0 1rem 0;
            }
            .header .right-text {
            text-align: right;
            }
            .footer {
            padding: 2rem 0 0 0;
            text-align: center;
            }
            @media (min-width: 840px) {
            html {
                background: #eceef4;
                min-height: 100%;
                padding: 0.5rem;
            }
            body {
                background-color: #fff;
                box-shadow: 0 0 4px rgba(0, 0, 0, .15);
                box-sizing: border-box;
                margin: 1rem auto 0;
                max-width: 820px;
                min-height: calc(100vh - 1rem);
                padding: 2rem 6rem 2rem 6rem;
            }
            }
            @media print {
            .pagebreak {
                page-break-before: always;
            }
            }
        `,
        setup: function (editor) {
            editor.on('change', function () {
                editor.save();
            });
        }
    });

    document.getElementById('letterForm').addEventListener('submit', function(event) {
        var description = tinymce.get('description').getContent();
        if (description.trim() === '') {
            alert('Deskripsi tidak boleh kosong.');
            event.preventDefault();
        }
    });
</script>
@endpush