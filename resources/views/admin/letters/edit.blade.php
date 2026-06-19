@extends('admin.letters.partials.editor-layout')

@section('page-title', 'Edit Template E-Letter')
@section('number_format_value', $letter->number_format ?? '')
@section('number_prefix_value', $letter->number_prefix ?? '')
@section('number_padding_value', $letter->number_padding ?? 3)
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
                    data-padding="{{ $cfg->padding }}"
                    {{ $letter->letter_number_config_id == $cfg->id ? 'selected' : '' }}>
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
                value="{{ $letter->number_format ?? '{no}/{kode_tipe}/{romawi}/{tahun}' }}">
        </div>
        <div class="mb-2">
            <label>Prefix Tetap</label>
            <input type="text" name="number_prefix" id="number_prefix" form="letterForm" class="form-control form-control-sm"
                placeholder="Contoh: SPK"
                value="{{ $letter->number_prefix ?? '' }}">
        </div>
        <div class="mb-2">
            <label>Digit Nomor Urut</label>
            <input type="number" name="number_padding" id="number_padding" form="letterForm" class="form-control form-control-sm"
                min="1" max="6" value="{{ $letter->number_padding ?? 3 }}">
        </div>
    </div>
    <div class="mb-1">
        <label>Preview Nomor</label>
        <div id="numberPreview" class="form-control form-control-sm bg-light text-primary fw-bold font-monospace" style="min-height:32px;font-size:12px;"></div>
    </div>
</div>
@endsection

@section('sidebar-fields')
<form id="letterForm" action="{{ route('letters.update', $letter->id) }}" method="POST">
    @csrf
    @method('PUT')
    <div id="customVarsContainer"></div>

    <div class="mb-2">
        <label>Site</label>
        <select class="form-select form-select-sm" name="site_id" required>
            @foreach ($sites as $site)
                <option value="{{ $site->id }}" {{ $letter->site_id == $site->id ? 'selected' : '' }}>{{ $site->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="mb-2">
        <label>Nama Template</label>
        <input type="text" class="form-control form-control-sm" name="title" value="{{ $letter->title }}" required placeholder="Nama template...">
    </div>
    <div class="mb-2">
        <label>Tipe Template</label>
        <select class="form-select form-select-sm" name="type_letter_id" required>
            @foreach ($types as $type)
                <option value="{{ $type->id }}" {{ $letter->type_letter_id == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="mb-2">
        <label>Tanda Tangan</label>
        <div class="form-check">
            <input type="hidden" name="require_hrd_signature" value="0">
            <input class="form-check-input" type="checkbox" name="require_hrd_signature" value="1" id="requireHrdSignature" {{ $letter->require_hrd_signature ? 'checked' : '' }}>
            <label class="form-check-label" for="requireHrdSignature" style="font-weight:400;">Perlu Tanda Tangan HRD</label>
        </div>
        <div class="form-check">
            <input type="hidden" name="require_employee_signature" value="0">
            <input class="form-check-input" type="checkbox" name="require_employee_signature" value="1" id="requireEmployeeSignature" {{ $letter->require_employee_signature ? 'checked' : '' }}>
            <label class="form-check-label" for="requireEmployeeSignature" style="font-weight:400;">Perlu Tanda Tangan Employee</label>
        </div>
    </div>
    <input type="hidden" name="description" id="descriptionHidden">
</form>
@endsection

@section('existing-custom-vars')
@if($letter->customVariables)
    @foreach($letter->customVariables as $cv)
        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
            <div><span class="fw-medium">{{ $cv->name }}</span> <code class="ms-1">[{{ $cv->variable }}]</code></div>
            <button type="button" class="btn btn-sm btn-outline-danger border-0" onclick="removeVar(this, '{{ $cv->id }}')"><i class="ti ti-trash"></i></button>
        </li>
    @endforeach
@endif
@endsection

@section('action-buttons')
<button type="button" class="btn btn-primary btn-sm px-4" onclick="submitForm()">
    <i class="ti ti-device-floppy me-1"></i>Update Template
</button>
@endsection

@section('page-scripts')
<script>
    const savedData = @json($letter->description ?? '');
    editor.init(savedData);

    @if($letter->customVariables && $letter->customVariables->count())
        (function() {
            const heading = document.getElementById('customVarHeading');
            heading.style.display = '';
            const btnContainer = document.getElementById('customVarButtons');
            @foreach($letter->customVariables as $cv)
            (function() {
                const btn = document.createElement('button');
                btn.className = 'var-btn';
                btn.setAttribute('onclick', "insertVar('[{{ $cv->variable }}]')");
                btn.innerHTML = '{{ $cv->name }} <code>[{{ $cv->variable }}]</code>';
                btnContainer.appendChild(btn);
            })();
            @endforeach
        })();
    @endif

    function removeVar(btn, id) {
        if (confirm('Hapus variabel ini?')) {
            if (id) {
                const container = document.getElementById('customVarsContainer');
                container.insertAdjacentHTML('beforeend', '<input type="hidden" name="delete_vars[]" value="' + id + '">');
            }
            btn.closest('li').remove();
        }
    }

    function submitForm() {
        const hasObjects = editor.pages.some(p => p.canvas.getObjects().length > 0);
        if (!hasObjects) { alert('Konten surat tidak boleh kosong.'); return; }
        document.getElementById('descriptionHidden').value = editor.serializeAll();
        document.getElementById('letterForm').submit();
    }
</script>
@endsection
