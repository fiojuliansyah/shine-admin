@extends('admin.letters.partials.editor-layout')

@section('page-title', 'Buat Template E-Letter')
@section('number_format_value', '{no}/{kode_tipe}/{romawi}/{tahun}')
@section('number_prefix_value', '')
@section('number_padding_value', 3)

@section('sidebar-fields')
<form id="letterForm" action="{{ route('letters.store') }}" method="POST">
    @csrf
    <div id="customVarsContainer"></div>

    <div class="mb-2">
        <label>Site</label>
        <select class="form-select form-select-sm" name="site_id" required>
            <option value="" disabled selected>Pilih Site</option>
            @foreach ($sites as $site)
                <option value="{{ $site->id }}">{{ $site->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="mb-2">
        <label>Nama Template</label>
        <input type="text" class="form-control form-control-sm" name="title" required placeholder="Nama template...">
    </div>
    <div class="mb-2">
        <label>Tipe Template</label>
        <select class="form-select form-select-sm" name="type_letter_id" required>
            <option value="" disabled selected>Pilih Tipe</option>
            @foreach ($types as $type)
                <option value="{{ $type->id }}">{{ $type->name }}</option>
            @endforeach
        </select>
    </div>
    <input type="hidden" name="description" id="descriptionHidden">
</form>
@endsection

@section('action-buttons')
<button type="button" class="btn btn-primary btn-sm px-4" onclick="submitForm()">
    <i class="ti ti-device-floppy me-1"></i>Simpan Template
</button>
@endsection

@section('page-scripts')
<script>
    editor.init('');

    function submitForm() {
        const hasObjects = editor.pages.some(p => p.canvas.getObjects().length > 0);
        if (!hasObjects) { alert('Konten surat tidak boleh kosong.'); return; }
        document.getElementById('descriptionHidden').value = editor.serializeAll();
        document.getElementById('letterForm').submit();
    }
</script>
@endsection
