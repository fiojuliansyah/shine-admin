<div class="card border-0 shadow-sm">
    <div class="card-header"><h6 class="mb-0">Info Template</h6></div>
    <div class="card-body">
        <div class="mb-2">
            <label class="form-label mb-1">Site</label>
            <select class="form-select form-select-sm" name="site_id" required>
                <option value="" disabled {{ $letter ? '' : 'selected' }}>Pilih Site</option>
                @foreach ($sites as $site)
                    <option value="{{ $site->id }}" {{ $letter && $letter->site_id == $site->id ? 'selected' : '' }}>{{ $site->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-2">
            <label class="form-label mb-1">Nama Template</label>
            <input type="text" class="form-control form-control-sm" name="title" value="{{ $letter->title ?? '' }}" required placeholder="Nama template...">
        </div>
        <div class="mb-2">
            <label class="form-label mb-1">Tipe Template</label>
            <select class="form-select form-select-sm" name="type_letter_id" required>
                <option value="" disabled {{ $letter ? '' : 'selected' }}>Pilih Tipe</option>
                @foreach ($types as $type)
                    <option value="{{ $type->id }}" {{ $letter && $letter->type_letter_id == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-2">
            <label class="form-label mb-1">Tanda Tangan</label>
            <div class="form-check">
                <input type="hidden" name="require_hrd_signature" value="0">
                <input class="form-check-input" type="checkbox" name="require_hrd_signature" value="1" id="requireHrdSignature" {{ !$letter || $letter->require_hrd_signature ? 'checked' : '' }}>
                <label class="form-check-label" for="requireHrdSignature">Perlu Tanda Tangan HRD</label>
            </div>
            <div class="form-check">
                <input type="hidden" name="require_employee_signature" value="0">
                <input class="form-check-input" type="checkbox" name="require_employee_signature" value="1" id="requireEmployeeSignature" {{ !$letter || $letter->require_employee_signature ? 'checked' : '' }}>
                <label class="form-check-label" for="requireEmployeeSignature">Perlu Tanda Tangan Employee</label>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm mt-3">
    <div class="card-header"><h6 class="mb-0">Nomor Surat</h6></div>
    <div class="card-body">
        <div class="mb-2">
            <label class="form-label mb-1">Pilih Konfigurasi</label>
            <select name="letter_number_config_id" id="selectNumberConfig" class="form-select form-select-sm">
                <option value="">-- Manual / Kustom --</option>
                @foreach($numberConfigs ?? [] as $cfg)
                    <option value="{{ $cfg->id }}"
                        data-format="{{ $cfg->format }}"
                        data-prefix="{{ $cfg->prefix }}"
                        data-padding="{{ $cfg->padding }}"
                        {{ $letter && $letter->letter_number_config_id == $cfg->id ? 'selected' : '' }}>
                        {{ $cfg->name }} ({{ $cfg->format }})
                    </option>
                @endforeach
            </select>
        </div>
        <div id="manualNumberFields">
            <div class="mb-2">
                <label class="form-label mb-1">Format Nomor</label>
                <input type="text" name="number_format" id="number_format" class="form-control form-control-sm font-monospace"
                    placeholder="{no}/{kode_tipe}/{romawi}/{tahun}"
                    value="{{ $letter->number_format ?? '{no}/{kode_tipe}/{romawi}/{tahun}' }}">
            </div>
            <div class="mb-2">
                <label class="form-label mb-1">Prefix Tetap</label>
                <input type="text" name="number_prefix" id="number_prefix" class="form-control form-control-sm"
                    placeholder="Contoh: SPK" value="{{ $letter->number_prefix ?? '' }}">
            </div>
            <div class="mb-2">
                <label class="form-label mb-1">Digit Nomor Urut</label>
                <input type="number" name="number_padding" id="number_padding" class="form-control form-control-sm"
                    min="1" max="6" value="{{ $letter->number_padding ?? 3 }}">
            </div>
        </div>
        <div class="mb-1">
            <label class="form-label mb-1">Preview Nomor</label>
            <div id="numberPreview" class="form-control form-control-sm bg-light text-primary fw-bold font-monospace" style="min-height:32px;font-size:12px;"></div>
        </div>
    </div>
</div>
