<div class="mb-3">
    <label class="form-label fw-bold">Nama Konfigurasi <span class="text-danger">*</span></label>
    <input type="text" name="name" class="form-control" placeholder="Contoh: Format SPK Standar" required value="{{ old('name') }}">
</div>
<div class="mb-3">
    <label class="form-label fw-bold">Format Nomor <span class="text-danger">*</span></label>
    <input type="text" name="format" class="form-control font-monospace" placeholder="{no}/{kode_tipe}/{romawi}/{tahun}" required value="{{ old('format') }}">
    <div class="form-text">Gunakan token di bawah. Contoh: <code>{prefix}/{no}/{kode_company}/{romawi}/{tahun_pendek}</code></div>
</div>
<div class="row g-3 mb-3">
    <div class="col-md-4">
        <label class="form-label fw-bold">Prefix Tetap</label>
        <input type="text" name="prefix" class="form-control" placeholder="Contoh: SPK" value="{{ old('prefix') }}">
        <div class="form-text">Digunakan jika format mengandung <code>{prefix}</code></div>
    </div>
    <div class="col-md-4">
        <label class="form-label fw-bold">Perusahaan</label>
        <select name="company_id" class="form-select">
            <option value="">-- Pilih Perusahaan --</option>
            @foreach($companies as $company)
                <option value="{{ $company->id }}"
                    data-company-code="{{ $company->unique_id ?? strtoupper(substr($company->name, 0, 3)) }}"
                    {{ old('company_id', isset($config) ? $config->company_id : null) == $company->id ? 'selected' : '' }}>
                    {{ $company->name }} ({{ $company->unique_id ?? '-' }})
                </option>
            @endforeach
        </select>
        <div class="form-text">Digunakan jika format mengandung <code>{kode_company}</code></div>
    </div>
    <div class="col-md-4">
        <label class="form-label fw-bold">Digit Nomor Urut <span class="text-danger">*</span></label>
        <input type="number" name="padding" class="form-control" min="1" max="6" value="{{ old('padding', 3) }}" required>
        <div class="form-text">Contoh: 3 = 001, 4 = 0001</div>
    </div>
</div>
<div class="row g-3 mb-3">
    <div class="col-md-4">
        <label class="form-label fw-bold">Mulai Dari Nomor <span class="text-danger">*</span></label>
        <input type="number" name="start_number" class="form-control" min="1" value="{{ old('start_number', 1) }}" required>
        <div class="form-text">Nomor urut pertama saat surat diterbitkan</div>
    </div>
    @if ($isEdit ?? false)
    <div class="col-md-4">
        <label class="form-label fw-bold">Current Number</label>
        <input type="number" name="current_number" class="form-control" min="0" value="{{ old('current_number') }}">
        <div class="form-text">Nomor terakhir yang sudah di-generate. Kosongkan jika tidak ingin mengubah.</div>
    </div>
    @endif
    <div class="{{ ($isEdit ?? false) ? 'col-md-4' : 'col-md-8' }}">
        <label class="form-label fw-bold">Bergandengan Dengan</label>
        <select name="shared_counter_id" class="form-select">
            <option value="">-- Counter Sendiri --</option>
            @foreach($configs ?? [] as $otherConfig)
                @if (!isset($currentConfigId) || $otherConfig->id != $currentConfigId)
                    <option value="{{ $otherConfig->id }}"
                        {{ old('shared_counter_id') == $otherConfig->id ? 'selected' : '' }}>
                        {{ $otherConfig->name }}
                        @if ($otherConfig->company)
                            ({{ $otherConfig->company->name }})
                        @endif
                    </option>
                @endif
            @endforeach
        </select>
        <div class="form-text">Jika dipilih, nomor urut akan mengikuti counter konfigurasi yang dipilih.</div>
    </div>
</div>
<div class="mb-3">
    <label class="form-label fw-bold">Keterangan</label>
    <textarea name="description" class="form-control" rows="2" placeholder="Opsional...">{{ old('description') }}</textarea>
</div>
<div class="mb-1">
    <label class="form-label fw-bold">Preview Nomor</label>
    <div id="{{ isset($isEdit) ? 'editPreview' : 'addPreview' }}" class="form-control bg-light text-primary fw-bold font-monospace" style="min-height:38px;"></div>
</div>
