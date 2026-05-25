@extends('admin.letters.partials.editor-layout')

@section('page-title', 'Edit Template E-Letter')
@section('number_format_value', $letter->number_format ?? '')
@section('number_prefix_value', $letter->number_prefix ?? '')
@section('number_padding_value', $letter->number_padding ?? 3)
@foreach($numberConfigs ?? [] as $cfg)
    @if($letter->letter_number_config_id == $cfg->id)
        @section('number_config_selected_' . $cfg->id, 'selected')
    @endif
@endforeach

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
