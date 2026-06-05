<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label fw-bold">Perusahaan <span class="text-danger">*</span></label>
        <select name="company_id" class="form-select select2-modal" required>
            <option value="">-- Pilih Perusahaan --</option>
            @foreach ($companies as $company)
                <option value="{{ $company->id }}"
                    data-company-code="{{ $company->unique_id ?? '' }}">
                    {{ $company->name }} ({{ $company->unique_id ?? '-' }})
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-md-6">
        <label class="form-label fw-bold">Nama Konfigurasi <span class="text-danger">*</span></label>
        <input type="text" name="name" class="form-control" required placeholder="Contoh: NIK Default">
    </div>

    <div class="col-md-12">
        <label class="form-label fw-bold">Format <span class="text-danger">*</span></label>
        <input type="text" name="format" class="form-control font-monospace nik-format-input"
            required placeholder="{kode_company}{kode_jabatan}{bulan_join}{tahun_join_pendek}{no}">
        <div class="form-text">Token dapat ditemukan di referensi token di bawah.</div>
    </div>

    <div class="col-md-4">
        <label class="form-label fw-bold">Prefix</label>
        <input type="text" name="prefix" class="form-control nik-prefix-input" placeholder="Opsional">
    </div>
    <div class="col-md-4">
        <label class="form-label fw-bold">Digit Nomor Urut <span class="text-danger">*</span></label>
        <input type="number" name="padding" class="form-control nik-padding-input"
            required min="1" max="10" value="5">
    </div>
    <div class="col-md-4">
        <label class="form-label fw-bold">Mulai dari Nomor <span class="text-danger">*</span></label>
        <input type="number" name="start_number" class="form-control nik-start-input"
            required min="1" value="1">
    </div>

    @if ($isEdit ?? false)
    <div class="col-md-4">
        <label class="form-label fw-bold">Current Number</label>
        <input type="number" name="current_number" class="form-control" min="0">
        <div class="form-text">Nomor terakhir yang sudah di-generate. Ubah jika ingin reset urutan.</div>
    </div>
    @endif

    <div class="col-md-12">
        <label class="form-label fw-bold">Keterangan</label>
        <textarea name="description" class="form-control" rows="2" placeholder="Opsional"></textarea>
    </div>

    <div class="col-md-12">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="is_default" value="1" id="is_default_{{ $isEdit ?? 0 ? 'edit' : 'add' }}">
            <label class="form-check-label" for="is_default_{{ $isEdit ?? 0 ? 'edit' : 'add' }}">
                Jadikan default untuk company ini
            </label>
        </div>
    </div>

    <div class="col-md-12">
        <label class="form-label fw-bold">Preview</label>
        <div class="form-control bg-light text-primary fw-bold font-monospace nik-preview"
            style="min-height:38px;">-</div>
    </div>
</div>
