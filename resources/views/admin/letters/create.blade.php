@extends('admin.letters.partials.editor-layout')

@section('page-title', 'Buat Template E-Letter')
@section('number-config-section')
<div class="sidebar-section">
    <h6>Nomor Surat</h6>
    <div class="mb-2">
        <label>Pilih Konfigurasi</label>
        <select name="letter_number_config_id" id="selectNumberConfig" form="letterForm" class="form-select form-select-sm">
            <option value="">-- Manual / Kustom --</option>
            @foreach($numberConfigs ?? [] as $cfg)
                <option value="{{ $cfg->id }}"
                    data-format="{{ $cfg->format }}"
                    data-prefix="{{ $cfg->prefix }}"
                    data-padding="{{ $cfg->padding }}">
                    {{ $cfg->name }} <small>({{ $cfg->format }})</small>
                </option>
            @endforeach
        </select>
    </div>
    <div id="manualNumberFields">
        <div class="mb-2">
            <label>Format Nomor</label>
            <input type="text" name="number_format" id="number_format" form="letterForm" class="form-control form-control-sm font-monospace"
                placeholder="{no}/{kode_tipe}/{romawi}/{tahun}"
                value="@yield('number_format_value')">
        </div>
        <div class="mb-2">
            <label>Prefix Tetap</label>
            <input type="text" name="number_prefix" id="number_prefix" form="letterForm" class="form-control form-control-sm"
                placeholder="Contoh: SPK"
                value="@yield('number_prefix_value')">
        </div>
        <div class="mb-2">
            <label>Digit Nomor Urut</label>
            <input type="number" name="number_padding" id="number_padding" form="letterForm" class="form-control form-control-sm"
                min="1" max="6" value="@yield('number_padding_value', 3)">
        </div>
    </div>
    <div class="mb-1">
        <label>Preview Nomor</label>
        <div id="numberPreview" class="form-control form-control-sm bg-light text-primary fw-bold font-monospace" style="min-height:32px;font-size:12px;"></div>
    </div>
</div>
@endsection

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
    <div class="mb-2">
        <label>Tanda Tangan</label>
        <div class="form-check">
            <input type="hidden" name="require_hrd_signature" value="0">
            <input class="form-check-input" type="checkbox" name="require_hrd_signature" value="1" id="requireHrdSignature" checked>
            <label class="form-check-label" for="requireHrdSignature" style="font-weight:400;">Perlu Tanda Tangan HRD</label>
        </div>
        <div class="form-check">
            <input type="hidden" name="require_employee_signature" value="0">
            <input class="form-check-input" type="checkbox" name="require_employee_signature" value="1" id="requireEmployeeSignature" checked>
            <label class="form-check-label" for="requireEmployeeSignature" style="font-weight:400;">Perlu Tanda Tangan Employee</label>
        </div>
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
