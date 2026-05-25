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
    <div class="col-md-6">
        <label class="form-label fw-bold">Prefix Tetap</label>
        <input type="text" name="prefix" class="form-control" placeholder="Contoh: SPK" value="{{ old('prefix') }}">
        <div class="form-text">Digunakan jika format mengandung <code>{prefix}</code></div>
    </div>
    <div class="col-md-6">
        <label class="form-label fw-bold">Digit Nomor Urut <span class="text-danger">*</span></label>
        <input type="number" name="padding" class="form-control" min="1" max="6" value="{{ old('padding', 3) }}" required>
        <div class="form-text">Contoh: 3 = 001, 4 = 0001</div>
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
