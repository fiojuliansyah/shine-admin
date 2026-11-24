@extends('website.layouts.guest')

@php
    use App\Http\Controllers\Applicant\OnboardingController as Wizard;
    $p = $profile;
    $isSecurity = old('is_security', ($p?->gada_pratama === 'yes' || $p?->gada_madya === 'yes' || $p?->gada_utama === 'yes') ? 'yes' : 'no');
    $totalSteps = count($steps);
@endphp

@push('js')
<style>
    .onb-step { flex: 1; text-align: center; position: relative; }
    .onb-step:not(:last-child)::after {
        content: ''; position: absolute; top: 20px; left: calc(50% + 24px); right: calc(-50% + 24px);
        height: 2px; background: #E5E7EB;
    }
    .onb-step.done:not(:last-child)::after { background: #3E6CFF; }
    .onb-step .onb-dot {
        width: 40px; height: 40px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center;
        background: #F3F4F6; color: #9CA3AF; font-size: 18px; border: 2px solid #E5E7EB;
    }
    .onb-step.active .onb-dot { background: #3E6CFF; color: #fff; border-color: #3E6CFF; box-shadow: 0 0 0 4px rgba(62,108,255,.15); }
    .onb-step.done .onb-dot { background: #EEF2FF; color: #3E6CFF; border-color: #3E6CFF; }
    .onb-step .onb-label { font-size: 12px; margin-top: 6px; color: #6B7280; }
    .onb-step.active .onb-label { color: #202C4B; font-weight: 600; }
    .onb-step a { text-decoration: none; }
    @media (max-width: 575px) { .onb-step .onb-label { display: none; } }
</style>
@endpush

@section('content')
<div class="main-wrapper bg-light min-vh-100">
    {{-- Header --}}
    <div class="bg-white border-bottom">
        <div class="container py-3 d-flex align-items-center justify-content-between">
            <img src="/admin/assets/img/logo-dark-ciptakarir.svg" alt="Cipta Karir" width="130">
            <div class="d-flex align-items-center gap-3">
                <span class="d-none d-sm-inline text-muted small">Halo, <strong class="text-dark">{{ $user->name }}</strong></span>
                <form action="{{ route('applicant-logout') }}" method="POST" data-loading-text="Keluar...">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-secondary"><i class="ti ti-logout me-1"></i>Keluar</button>
                </form>
            </div>
        </div>
    </div>

    <div class="container py-4" style="max-width: 900px;">
        <div class="text-center mb-4">
            <h3 class="mb-1">Lengkapi Profil Anda</h3>
            <p class="text-muted mb-0">Lengkapi data berikut sebelum mulai melamar pekerjaan. Langkah {{ $step }} dari {{ $totalSteps }}.</p>
        </div>

        {{-- Stepper --}}
        <div class="d-flex mb-4">
            @foreach ($steps as $no => $s)
                @php
                    $state = $no === $step ? 'active' : ($no < $firstIncomplete || ($no < $step) ? 'done' : '');
                    $reachable = $no <= $firstIncomplete;
                @endphp
                <div class="onb-step {{ $state }}">
                    @if ($reachable && $no !== $step)
                        <a href="{{ route('applicant.onboarding', ['step' => $no]) }}">
                    @endif
                        <span class="onb-dot">
                            <i class="ti {{ $state === 'done' ? 'ti-check' : $s['icon'] }}"></i>
                        </span>
                        <div class="onb-label">{{ $s['title'] }}</div>
                    @if ($reachable && $no !== $step)
                        </a>
                    @endif
                </div>
            @endforeach
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="ti {{ $steps[$step]['icon'] }} text-primary me-2"></i>{{ $steps[$step]['title'] }}</h5>
            </div>

            @if ($step === 5)
                {{-- ===== Langkah 5: Dokumen ===== --}}
                <div class="card-body">
                    <div class="alert alert-info small">
                        Unggah dokumen wajib berikut dalam bentuk <strong>gambar (JPG/PNG)</strong> hasil scan yang jelas, maksimal 5 MB per file.
                        Dokumen yang diunggah ulang akan menggantikan dokumen sebelumnya.
                    </div>

                    <div class="list-group">
                        @foreach ($requiredDocuments as $docName)
                            @php $doc = $documents->get($docName); @endphp
                            <div class="list-group-item py-3 onb-doc-row">
                                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                                    <div class="d-flex align-items-center gap-2">
                                        <img class="onb-doc-thumb rounded border d-none" alt="Pratinjau {{ $docName }}"
                                            style="width: 48px; height: 48px; object-fit: cover;">
                                        <div>
                                            <div class="fw-semibold">{{ $docName }} <span class="text-danger">*</span></div>
                                            {{-- Status tersimpan di server --}}
                                            <span class="onb-doc-saved">
                                                @if ($doc)
                                                    <span class="badge bg-success-transparent text-success"><i class="ti ti-circle-check me-1"></i>Sudah diunggah</span>
                                                    @if ($doc->view_url)
                                                        <a href="{{ $doc->view_url }}" target="_blank" class="small ms-1">Lihat</a>
                                                    @endif
                                                @else
                                                    <span class="badge bg-warning-transparent text-warning"><i class="ti ti-alert-circle me-1"></i>Belum diunggah</span>
                                                @endif
                                            </span>
                                            {{-- Status setelah file dipilih (belum dikirim) --}}
                                            <span class="onb-doc-ready badge bg-info-transparent text-info d-none">
                                                <i class="ti ti-file-check me-1"></i>File siap &mdash; klik {{ $doc ? 'Ganti' : 'Unggah' }}
                                            </span>
                                        </div>
                                    </div>
                                    <form action="{{ route('applicant.onboarding.document') }}" method="POST" enctype="multipart/form-data"
                                        class="d-flex gap-2 align-items-center" data-loading-text="Mengunggah {{ $docName }}...">
                                        @csrf
                                        <input type="hidden" name="name" value="{{ $docName }}">
                                        <input type="file" name="file" class="form-control form-control-sm onb-doc-input" accept="image/png,image/jpeg" required style="max-width: 230px;" data-doc-crop data-doc-crop-submit>
                                        <button type="submit" class="btn btn-sm {{ $doc ? 'btn-outline-primary' : 'btn-primary' }} text-nowrap onb-doc-submit">
                                            <i class="ti ti-upload me-1"></i>{{ $doc ? 'Ganti' : 'Unggah' }}
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="card-footer bg-white d-flex justify-content-between">
                    <a href="{{ route('applicant.onboarding', ['step' => 4]) }}" class="btn btn-light"><i class="ti ti-arrow-left me-1"></i>Sebelumnya</a>
                    <form action="{{ route('applicant.onboarding.save', ['step' => 5]) }}" method="POST" data-loading-text="Memeriksa kelengkapan data..." id="onbFinishForm">
                        @csrf
                        <button type="submit" class="btn btn-success"><i class="ti ti-check me-1"></i>Selesai & Masuk Dashboard</button>
                    </form>
                </div>
            @else
                <form action="{{ route('applicant.onboarding.save', ['step' => $step]) }}" method="POST" enctype="multipart/form-data" novalidate
                    id="onbStepForm" data-step="{{ $step }}" data-loading-text="Menyimpan data...">
                    @csrf
                    <div class="card-body">
                        <div class="row">
                            @if ($step === 1)
                                {{-- ===== Langkah 1: Data Diri ===== --}}
                                <div class="col-12 mb-3">
                                    <div class="border rounded p-3 bg-light">
                                        <div class="d-flex flex-wrap align-items-center gap-2">
                                            <div class="me-auto">
                                                <div class="fw-semibold"><i class="ti ti-scan me-1 text-primary"></i>Isi otomatis dari foto KTP</div>
                                                <small class="text-muted">Pilih foto KTP, data langsung terisi otomatis dan foto KTP sekaligus tersimpan ke dokumen.</small>
                                            </div>
                                            <input type="file" id="onbKtpFile" class="form-control form-control-sm" accept="image/png,image/jpeg,image/webp" style="max-width: 230px;">
                                            <button type="button" id="onbKtpScan" class="btn btn-sm btn-dark text-nowrap" disabled>
                                                <i class="ti ti-refresh me-1"></i>Scan Ulang
                                            </button>
                                        </div>
                                        <div id="onbKtpPreviewWrap" class="mt-3 text-center d-none">
                                            <img id="onbKtpPreview" alt="Preview KTP" class="img-fluid rounded border bg-white"
                                                style="max-height: 240px;">
                                        </div>
                                        <div id="onbKtpStatus" class="small mt-2 d-none"></div>
                                    </div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">NIK KTP <span class="text-danger">*</span></label>
                                    <input type="text" name="nik" class="form-control @error('nik') is-invalid @enderror" inputmode="numeric"
                                        maxlength="16" placeholder="16 digit" value="{{ old('nik', $user->nik) }}" required>
                                    @error('nik')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Pas Foto <span class="text-danger">*</span></label>
                                    <div class="d-flex align-items-center gap-2">
                                        @if ($p?->avatar_url)
                                            <img src="{{ $p->avatar_url }}" alt="Pas foto" class="rounded border" style="width:42px;height:42px;object-fit:cover;">
                                        @endif
                                        <input type="file" name="avatar" class="form-control @error('avatar') is-invalid @enderror" accept="image/png,image/jpeg">
                                    </div>
                                    @error('avatar')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                    @if ($p?->avatar_url)<small class="text-success"><i class="ti ti-circle-check me-1"></i>Pas foto sudah tersimpan. Kosongkan bila tidak ingin mengganti.</small>@endif
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Jenis Kelamin <span class="text-danger">*</span></label>
                                    <select name="gender" class="form-select @error('gender') is-invalid @enderror" required>
                                        <option value="">Pilih</option>
                                        @foreach (['Laki-Laki', 'Perempuan'] as $g)
                                            <option value="{{ $g }}" @selected(old('gender', $p?->gender) === $g)>{{ $g }}</option>
                                        @endforeach
                                    </select>
                                    @error('gender')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Agama <span class="text-danger">*</span></label>
                                    <select name="religion" class="form-select @error('religion') is-invalid @enderror" required>
                                        <option value="">Pilih Agama</option>
                                        @foreach (Wizard::RELIGIONS as $rel)
                                            <option value="{{ $rel }}" @selected(old('religion', $p?->religion) === $rel)>{{ $rel }}</option>
                                        @endforeach
                                    </select>
                                    @error('religion')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Tempat Lahir <span class="text-danger">*</span></label>
                                    <input type="text" name="birth_place" class="form-control @error('birth_place') is-invalid @enderror"
                                        value="{{ old('birth_place', $p?->birth_place) }}" required>
                                    @error('birth_place')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Tanggal Lahir <span class="text-danger">*</span></label>
                                    <input type="date" name="birth_date" id="onbBirthDate" class="form-control @error('birth_date') is-invalid @enderror"
                                        value="{{ old('birth_date', $p?->birth_date) }}" required>
                                    @error('birth_date')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Nama Ibu Kandung <span class="text-danger">*</span></label>
                                    <input type="text" name="mother_name" class="form-control @error('mother_name') is-invalid @enderror"
                                        value="{{ old('mother_name', $p?->mother_name) }}" required>
                                    @error('mother_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Pendidikan Terakhir <span class="text-danger">*</span></label>
                                    <select name="last_education" class="form-select @error('last_education') is-invalid @enderror" required>
                                        <option value="">Pilih Pendidikan</option>
                                        @foreach (Wizard::EDUCATIONS as $edu)
                                            <option value="{{ $edu }}" @selected(old('last_education', $p?->last_education) === $edu)>{{ $edu }}</option>
                                        @endforeach
                                    </select>
                                    @error('last_education')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Status Pernikahan <span class="text-danger">*</span></label>
                                    <select name="marriage_status" class="form-select @error('marriage_status') is-invalid @enderror" required>
                                        <option value="">Pilih</option>
                                        @foreach (Wizard::MARRIAGE_STATUSES as $val => $label)
                                            <option value="{{ $val }}" @selected(old('marriage_status', $p?->marriage_status) === $val)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    @error('marriage_status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">No Kartu Keluarga (KK) <span class="text-danger">*</span></label>
                                    <input type="text" name="no_kk" class="form-control @error('no_kk') is-invalid @enderror" inputmode="numeric"
                                        maxlength="16" placeholder="16 digit" value="{{ old('no_kk', $p?->no_kk) }}" required>
                                    @error('no_kk')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">No NPWP</label>
                                    <input type="text" name="npwp_number" class="form-control @error('npwp_number') is-invalid @enderror"
                                        value="{{ old('npwp_number', $p?->npwp_number) }}">
                                    @error('npwp_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                            @elseif ($step === 2)
                                {{-- ===== Langkah 2: Alamat ===== --}}
                                <div class="col-12 mb-3">
                                    <label class="form-label">Alamat KTP <span class="text-danger">*</span></label>
                                    <textarea name="address" id="onbAddress" rows="2" class="form-control @error('address') is-invalid @enderror" required>{{ old('address', $p?->address) }}</textarea>
                                    @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">RT/RW</label>
                                    <input type="text" name="rt_rw" class="form-control" placeholder="001/002" value="{{ old('rt_rw', $p?->rt_rw) }}">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Kelurahan/Desa</label>
                                    <input type="text" name="kelurahan" class="form-control" value="{{ old('kelurahan', $p?->kelurahan) }}">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Kecamatan</label>
                                    <input type="text" name="kecamatan" class="form-control" value="{{ old('kecamatan', $p?->kecamatan) }}">
                                </div>
                                <div class="col-12 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="onbSameAddress">
                                        <label class="form-check-label" for="onbSameAddress">Alamat domisili sama dengan alamat KTP</label>
                                    </div>
                                </div>
                                <div class="col-12 mb-3">
                                    <label class="form-label">Alamat Domisili <span class="text-danger">*</span></label>
                                    <textarea name="current_address" id="onbCurrentAddress" rows="2" class="form-control @error('current_address') is-invalid @enderror" required>{{ old('current_address', $p?->current_address) }}</textarea>
                                    @error('current_address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Status Tempat Tinggal <span class="text-danger">*</span></label>
                                    <select name="living_with" class="form-select @error('living_with') is-invalid @enderror" required>
                                        <option value="">Pilih</option>
                                        @foreach (Wizard::LIVING_WITH as $val => $label)
                                            <option value="{{ $val }}" @selected(old('living_with', $p?->living_with) === $val)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    @error('living_with')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                            @elseif ($step === 3)
                                {{-- ===== Langkah 3: Fisik & Keahlian ===== --}}
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Tinggi Badan (cm) <span class="text-danger">*</span></label>
                                    <input type="number" step="0.1" name="height" class="form-control @error('height') is-invalid @enderror"
                                        value="{{ old('height', $p?->height) }}" required>
                                    @error('height')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Berat Badan (kg) <span class="text-danger">*</span></label>
                                    <input type="number" step="0.1" name="weight" class="form-control @error('weight') is-invalid @enderror"
                                        value="{{ old('weight', $p?->weight) }}" required>
                                    @error('weight')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                @foreach ([
                                    'eye_condition' => ['Kondisi Mata', ['Normal' => 'Normal', 'Color Blind' => 'Buta Warna']],
                                    'hearing' => ['Pendengaran', ['Normal' => 'Normal', 'Impaired' => 'Terganggu']],
                                    'tattoo' => ['Bertato?', ['None' => 'Tidak Ada', 'Present' => 'Ada']],
                                    'piercing' => ['Tindik?', ['None' => 'Tidak Ada', 'Present' => 'Ada']],
                                ] as $field => [$label, $options])
                                    <div class="col-md-3 col-6 mb-3">
                                        <label class="form-label">{{ $label }} <span class="text-danger">*</span></label>
                                        <select name="{{ $field }}" class="form-select" required>
                                            @foreach ($options as $val => $text)
                                                <option value="{{ $val }}" @selected(old($field, $p?->{$field}) === $val)>{{ $text }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                @endforeach
                                <div class="col-12 mb-3">
                                    <label class="form-label">Keahlian (Skills)</label>
                                    <textarea name="skills" class="form-control" rows="2"
                                        placeholder="Contoh: Bela Diri, Mengemudi, Sertifikasi IT, dll">{{ old('skills', $p?->skills) }}</textarea>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Melamar Sebagai Security? <span class="text-danger">*</span></label>
                                    <select name="is_security" id="onbIsSecurity" class="form-select" required>
                                        <option value="no" @selected($isSecurity === 'no')>Bukan Security</option>
                                        <option value="yes" @selected($isSecurity === 'yes')>Ya, Saya Security</option>
                                    </select>
                                </div>
                                <div id="onbSecurityFields" class="row mx-0 px-0 {{ $isSecurity === 'yes' ? '' : 'd-none' }}">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Push Up (Jumlah)</label>
                                        <input type="number" name="push_up" class="form-control" value="{{ old('push_up', $p?->push_up) }}">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Kemampuan PBB</label>
                                        <select name="pbb" class="form-select">
                                            <option value="0" @selected((string) old('pbb', $p?->pbb) === '0')>Tidak Bisa / Abnormal</option>
                                            <option value="1" @selected((string) old('pbb', $p?->pbb) === '1')>Bisa / Normal</option>
                                        </select>
                                    </div>
                                    @foreach (['gada_pratama' => 'Gada Pratama', 'gada_madya' => 'Gada Madya', 'gada_utama' => 'Gada Utama'] as $field => $label)
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">{{ $label }}</label>
                                            <select name="{{ $field }}" class="form-select">
                                                <option value="no" @selected(old($field, $p?->{$field}) !== 'yes')>Tidak Ada</option>
                                                <option value="yes" @selected(old($field, $p?->{$field}) === 'yes')>Aktif</option>
                                            </select>
                                        </div>
                                    @endforeach
                                    <div class="col-12">
                                        <small class="text-muted">Sertifikat/KTA GADA yang aktif wajib diunggah di langkah Dokumen.</small>
                                    </div>
                                </div>

                            @elseif ($step === 4)
                                {{-- ===== Langkah 4: Kontak Darurat & Rekening ===== --}}
                                <div class="col-12"><h6 class="mb-3">Kontak Darurat</h6></div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Nama <span class="text-danger">*</span></label>
                                    <input type="text" name="family_name" class="form-control @error('family_name') is-invalid @enderror"
                                        placeholder="Nama suami/istri/orang tua" value="{{ old('family_name', $p?->family_name) }}" required>
                                    @error('family_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Hubungan <span class="text-danger">*</span></label>
                                    <input type="text" name="family_relation" class="form-control @error('family_relation') is-invalid @enderror"
                                        placeholder="Contoh: Istri, Ayah" value="{{ old('family_relation', $p?->family_relation) }}" required>
                                    @error('family_relation')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">No. Telepon <span class="text-danger">*</span></label>
                                    <input type="text" name="family_phone" class="form-control @error('family_phone') is-invalid @enderror"
                                        inputmode="tel" value="{{ old('family_phone', $p?->family_phone) }}" required>
                                    @error('family_phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-12"><hr><h6 class="mb-3">Rekening Bank <small class="text-muted fw-normal">(untuk penggajian)</small></h6></div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Nama Bank <span class="text-danger">*</span></label>
                                    <input type="text" name="bank_name" class="form-control @error('bank_name') is-invalid @enderror"
                                        placeholder="Contoh: BCA" value="{{ old('bank_name', $p?->bank_name) }}" required>
                                    @error('bank_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Nama Pemilik Rekening <span class="text-danger">*</span></label>
                                    <input type="text" name="account_name" class="form-control @error('account_name') is-invalid @enderror"
                                        value="{{ old('account_name', $p?->account_name) }}" required>
                                    @error('account_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">No Rekening <span class="text-danger">*</span></label>
                                    <input type="text" name="account_number" class="form-control @error('account_number') is-invalid @enderror"
                                        inputmode="numeric" value="{{ old('account_number', $p?->account_number) }}" required>
                                    @error('account_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="card-footer bg-white d-flex justify-content-between">
                        @if ($step > 1)
                            <a href="{{ route('applicant.onboarding', ['step' => $step - 1]) }}" class="btn btn-light"><i class="ti ti-arrow-left me-1"></i>Sebelumnya</a>
                        @else
                            <span></span>
                        @endif
                        <button type="submit" class="btn btn-primary">Simpan & Lanjut<i class="ti ti-arrow-right ms-1"></i></button>
                    </div>
                </form>
            @endif
        </div>

        <p class="text-center text-muted small mt-4 mb-0">Copyright &copy; {{ date('Y') }} - Cipta Karir</p>
    </div>
</div>

@include('website.auth.partials.alert-modal')
@include('partials.document-crop')
@endsection

@push('js')
<script>
    (function () {
        // ===== Draf isian per langkah (sessionStorage) =====
        // Isian yang belum disimpan tidak hilang saat klik "Sebelumnya", pindah langkah, atau muat ulang.
        // Bila validasi server gagal, nilai dari server (old input) yang dipakai.
        var stepForm = document.getElementById('onbStepForm');
        if (stepForm) {
            var draftKey = 'onbDraft:{{ $user->id }}:' + stepForm.dataset.step;
            var hasOldInput = @json(session()->hasOldInput());

            function fields() {
                return Array.prototype.filter.call(stepForm.elements, function (el) {
                    return el.name && el.name !== '_token' && el.type !== 'file' && el.type !== 'hidden' && el.type !== 'submit';
                });
            }
            function saveDraft() {
                var data = {};
                fields().forEach(function (el) { data[el.name] = el.type === 'checkbox' ? el.checked : el.value; });
                try { sessionStorage.setItem(draftKey, JSON.stringify(data)); } catch (e) {}
            }

            if (!hasOldInput) {
                try {
                    var draft = JSON.parse(sessionStorage.getItem(draftKey) || 'null');
                    if (draft) {
                        fields().forEach(function (el) {
                            if (!(el.name in draft)) return;
                            if (el.type === 'checkbox') el.checked = !!draft[el.name];
                            else if (el._flatpickr) el._flatpickr.setDate(draft[el.name] || null, false);
                            else el.value = draft[el.name];
                            el.dispatchEvent(new Event('change', { bubbles: true }));
                        });
                    }
                } catch (e) {}
            }

            stepForm.addEventListener('input', saveDraft);
            stepForm.addEventListener('change', saveDraft);
            // Tetap simpan draf saat submit; dihapus setelah langkah ini tersimpan (lihat di bawah)
            stepForm.addEventListener('submit', saveDraft);
        }

        // Draf langkah sebelumnya sudah tersimpan ke server -> hapus
        @if (! $errors->any())
            for (var i = 1; i < {{ $step }}; i++) {
                try { sessionStorage.removeItem('onbDraft:{{ $user->id }}:' + i); } catch (e) {}
            }
        @endif

        // Langkah 2: alamat domisili sama dengan KTP
        var same = document.getElementById('onbSameAddress');
        if (same) {
            var ktp = document.getElementById('onbAddress');
            var cur = document.getElementById('onbCurrentAddress');
            same.addEventListener('change', function () {
                if (same.checked) cur.value = ktp.value;
                cur.readOnly = same.checked;
            });
            ktp.addEventListener('input', function () { if (same.checked) cur.value = ktp.value; });

            // Isi alamat dari hasil scan KTP di langkah 1 (hanya kolom yang masih kosong)
            try {
                var ocr = JSON.parse(sessionStorage.getItem('onbKtpOcr') || 'null');
                if (ocr) {
                    [['address', 'onbAddress'], ['rt_rw', 'rt_rw'], ['kelurahan', 'kelurahan'], ['kecamatan', 'kecamatan']].forEach(function (pair) {
                        var el = document.getElementById(pair[1]) || document.querySelector('[name="' + pair[1] + '"]');
                        if (el && !el.value && ocr[pair[0]]) el.value = ocr[pair[0]];
                    });
                }
            } catch (e) {}
        }

        // Langkah 5: tandai baris dokumen yang file-nya sudah dipilih (hasil crop) tapi belum diunggah
        document.querySelectorAll('.onb-doc-input').forEach(function (input) {
            input.addEventListener('change', function () {
                var row = input.closest('.onb-doc-row');
                var file = input.files && input.files[0];
                var thumb = row.querySelector('.onb-doc-thumb');
                var btn = row.querySelector('.onb-doc-submit');

                row.querySelector('.onb-doc-saved').classList.toggle('d-none', !!file);
                row.querySelector('.onb-doc-ready').classList.toggle('d-none', !file);
                row.classList.toggle('list-group-item-info', !!file);

                if (thumb.src) URL.revokeObjectURL(thumb.src);
                if (file) {
                    thumb.src = URL.createObjectURL(file);
                    thumb.classList.remove('d-none');
                    btn.classList.remove('btn-outline-primary');
                    btn.classList.add('btn-primary');
                } else {
                    thumb.removeAttribute('src');
                    thumb.classList.add('d-none');
                }
            });
        });

        // Langkah 5: ingatkan bila ada file yang sudah dipilih tapi belum diunggah
        var finishForm = document.getElementById('onbFinishForm');
        if (finishForm) {
            finishForm.addEventListener('submit', function (e) {
                var pendingDocs = Array.prototype.filter.call(document.querySelectorAll('.onb-doc-input'), function (i) {
                    return i.files && i.files.length;
                }).map(function (i) { return i.form.querySelector('[name="name"]').value; });

                if (pendingDocs.length && !confirm('File ' + pendingDocs.join(', ') + ' sudah dipilih tapi BELUM diunggah. Lanjutkan tanpa mengunggahnya?')) {
                    e.preventDefault();
                }
            });
        }

        // Langkah 3: tampilkan isian security
        var sec = document.getElementById('onbIsSecurity');
        if (sec) {
            sec.addEventListener('change', function () {
                document.getElementById('onbSecurityFields').classList.toggle('d-none', sec.value !== 'yes');
            });
        }

        // Langkah 1: scan KTP (OCR) untuk isi otomatis
        var fileInput = document.getElementById('onbKtpFile');
        if (fileInput) {
            var scanBtn = document.getElementById('onbKtpScan');
            var status = document.getElementById('onbKtpStatus');
            var csrf = document.querySelector('input[name="_token"]').value;

            function setStatus(cls, html) {
                status.className = 'small mt-2 ' + cls;
                status.innerHTML = html;
            }
            function setVal(name, value) {
                var el = document.querySelector('[name="' + name + '"]');
                if (!el || !value) return;
                if (el._flatpickr) el._flatpickr.setDate(value, true);
                else el.value = value;
            }

            var previewWrap = document.getElementById('onbKtpPreviewWrap');
            var preview = document.getElementById('onbKtpPreview');

            fileInput.addEventListener('change', function () {
                var file = fileInput.files[0];
                scanBtn.disabled = !file;
                status.className = 'small mt-2 d-none';

                // Preview foto KTP sebelum di-scan
                if (preview.src) URL.revokeObjectURL(preview.src);
                if (file) {
                    preview.src = URL.createObjectURL(file);
                    previewWrap.classList.remove('d-none');
                } else {
                    preview.removeAttribute('src');
                    previewWrap.classList.add('d-none');
                }

                // Langsung scan begitu file dipilih
                if (file) runScan();
            });

            scanBtn.addEventListener('click', runScan);

            function runScan() {
                if (!fileInput.files.length) return;
                scanBtn.disabled = true;
                fileInput.disabled = true; // cegah ganti file saat scan berjalan
                setStatus('text-muted', '<span class="spinner-border spinner-border-sm me-1"></span>Membaca KTP...');
                if (window.PageLoading) window.PageLoading.show('Membaca KTP...');

                var fd = new FormData();
                fd.append('image', fileInput.files[0]);

                fetch(@json(route('applicant.onboarding.ocr-scan')), {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                    body: fd
                }).then(function (resp) {
                    return resp.json().catch(function () { return {}; }).then(function (json) {
                        if (!resp.ok) throw new Error(json.message || 'Gagal membaca KTP');
                        return json;
                    });
                }).then(function (json) {
                    var m = json.mapped || {};
                    setVal('nik', m.nik);
                    setVal('gender', m.gender);
                    setVal('birth_place', m.birth_place);
                    setVal('birth_date', m.birth_date);
                    sessionStorage.setItem('onbKtpOcr', JSON.stringify(m));

                    var msg = '<i class="ti ti-circle-check me-1"></i>Data KTP terisi. Periksa kembali sebelum lanjut.';
                    if (json.document) msg += ' Foto KTP tersimpan ke dokumen.';
                    if (json.document_error) msg += ' <span class="text-danger">' + json.document_error + '</span>';
                    setStatus('text-success', msg);
                }).catch(function (err) {
                    setStatus('text-danger', err.message || 'Gagal membaca KTP');
                }).finally(function () {
                    if (window.PageLoading) window.PageLoading.hide();
                    scanBtn.disabled = false;
                    fileInput.disabled = false;
                });
            }
        }
    })();
</script>
@endpush
