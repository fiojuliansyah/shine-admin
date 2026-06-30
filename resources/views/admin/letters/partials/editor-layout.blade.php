<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('page-title', 'E-Letter Editor') — Cipta Karir</title>

    <link rel="shortcut icon" type="image/x-icon" href="/admin/assets/img/favicon-ciptakarir.png">
    <link rel="stylesheet" href="/admin/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="/admin/assets/plugins/icons/feather/feather.css">
    <link rel="stylesheet" href="/admin/assets/plugins/tabler-icons/tabler-icons.css">
    <link rel="stylesheet" href="/admin/assets/plugins/fontawesome/css/fontawesome.min.css">
    <link rel="stylesheet" href="/admin/assets/plugins/fontawesome/css/all.min.css">

    <style>
        *, *::before, *::after { box-sizing: border-box; }
        html, body { height: 100%; margin: 0; padding: 0; background: #f0f2f5; font-family: Arial, sans-serif; overflow: hidden; }

        #editor-shell { display: flex; flex-direction: column; height: 100vh; }

        /* Top bar */
        #editor-topbar {
            display: flex; align-items: center; gap: 8px; flex-wrap: wrap;
            background: #fff; border-bottom: 1px solid #dee2e6;
            padding: 6px 12px; flex-shrink: 0; z-index: 100; position: relative;
        }
        #editor-topbar .topbar-title {
            font-weight: 700; font-size: 15px; color: #0d6efd; margin-right: 8px; white-space: nowrap;
        }
        #editor-topbar .vr { height: 24px; margin: 0 4px; }
        #editor-topbar select, #editor-topbar input[type=number] { height: 32px; }
        #editor-topbar .btn { height: 32px; padding: 0 10px; font-size: 13px; }
        #editor-topbar input[type=color] { width: 32px; height: 32px; padding: 2px; border: 1px solid #dee2e6; border-radius: 4px; cursor: pointer; }

        /* Body: sidebar + canvas area */
        #editor-body { display: flex; flex: 1; min-height: 0; overflow: hidden; }

        /* Left sidebar: form fields + page thumbs */
        #editor-sidebar {
            width: 260px; flex-shrink: 0; background: #fff;
            border-right: 1px solid #dee2e6; display: flex; flex-direction: column;
            overflow-y: auto; padding: 12px;
        }
        #editor-sidebar .sidebar-section { margin-bottom: 16px; }
        #editor-sidebar .sidebar-section h6 { font-size: 12px; font-weight: 700; text-transform: uppercase; color: #6c757d; margin-bottom: 8px; border-bottom: 1px solid #eee; padding-bottom: 4px; }
        #editor-sidebar label { font-size: 12px; font-weight: 600; margin-bottom: 3px; display: block; }
        #editor-sidebar .form-control, #editor-sidebar .form-select { font-size: 13px; }

        /* Page thumbnails */
        #pageThumbs { display: flex; flex-direction: column; gap: 8px; }
        .page-thumb {
            cursor: pointer; border: 2px solid #dee2e6; border-radius: 4px; padding: 6px;
            background: #f8f9fa; text-align: center; font-size: 11px; color: #555;
            transition: border-color .15s;
        }
        .page-thumb.active { border-color: #0d6efd; background: #e8f0fe; color: #0d6efd; font-weight: 700; }
        .page-thumb-preview { background: #fff; border: 1px solid #eee; width: 100%; height: 80px; display: flex; align-items: center; justify-content: center; font-size: 20px; color: #ccc; margin-bottom: 4px; border-radius: 2px; }

        /* Layers panel */
        #layersList { display: flex; flex-direction: column; gap: 3px; max-height: 320px; overflow-y: auto; padding-right: 2px; }
        .layer-item {
            display: flex; align-items: center; gap: 4px; padding: 5px 6px;
            background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 4px;
            font-size: 11px; cursor: pointer; user-select: none;
            transition: background .1s, border-color .1s;
        }
        .layer-item:hover { background: #eef3ff; border-color: #bfd2ff; }
        .layer-item.active { background: #e8f0fe; border-color: #0d6efd; color: #0d6efd; font-weight: 600; }
        .layer-item.dragging { opacity: 0.45; }
        .layer-item.drag-over { border-top: 2px solid #0d6efd; }
        .layer-item .layer-icon { font-size: 13px; color: #6c757d; flex-shrink: 0; }
        .layer-item.active .layer-icon { color: #0d6efd; }
        .layer-item .layer-name {
            flex: 1; overflow: hidden; white-space: nowrap; text-overflow: ellipsis;
            cursor: text;
        }
        .layer-item .layer-name[contenteditable="true"] {
            background: #fff; border: 1px solid #0d6efd; border-radius: 2px;
            padding: 1px 4px; outline: none;
        }
        .layer-item .layer-btn {
            background: transparent; border: 0; padding: 2px 4px; font-size: 12px;
            color: #6c757d; cursor: pointer; border-radius: 3px; line-height: 1;
        }
        .layer-item .layer-btn:hover { background: #dee2e6; color: #212529; }
        .layer-item .layer-btn.muted { color: #c0c4ca; }
        .layer-handle { cursor: grab; color: #adb5bd; font-size: 14px; }
        .layer-handle:active { cursor: grabbing; }
        #layersEmpty { text-align: center; font-size: 11px; color: #adb5bd; padding: 12px 0; }

        /* Canvas area */
        #editor-canvas-area {
            flex: 1; min-height: 0; overflow: auto; background: #eceef4;
            display: flex; flex-direction: column; align-items: center;
            padding: 24px 24px 48px;
        }
        .canvas-page-wrapper {
            position: relative; margin-bottom: 24px; box-shadow: 0 2px 12px rgba(0,0,0,0.18);
            border: 2px solid transparent; transition: border-color .15s; cursor: pointer;
            display: inline-block;
        }
        .canvas-page-wrapper.active-page { border-color: #0d6efd; }
        .canvas-page-label { font-size: 11px; color: #888; margin-bottom: 4px; text-align: left; }

        /* Ruler visual */
        .canvas-with-ruler {
            display: inline-grid;
            grid-template-columns: 20px auto;
            grid-template-rows: 20px auto;
            margin-bottom: 24px;
        }
        .ruler-corner { background: #d8dde6; grid-column:1; grid-row:1; }
        .ruler-h {
            grid-column: 2; grid-row: 1; height: 20px;
            background: #e8ebf0; border-bottom: 1px solid #bbb;
            position: relative; overflow: hidden;
        }
        .ruler-v {
            grid-column: 1; grid-row: 2; width: 20px;
            background: #e8ebf0; border-right: 1px solid #bbb;
            position: relative; overflow: hidden;
        }
        .ruler-canvas-wrap {
            grid-column: 2; grid-row: 2;
            position: relative; display: inline-block;
            box-shadow: 0 2px 12px rgba(0,0,0,0.18);
            border: 2px solid transparent; transition: border-color .15s; cursor: pointer;
        }
        .ruler-canvas-wrap.active-page { border-color: #0d6efd; }
        .ruler-tick { position: absolute; color: #666; font-size: 8px; line-height: 1; user-select: none; }
        .ruler-tick-line { position: absolute; background: #999; }

        /* Right panel: variable list */
        #editor-vars {
            width: 220px; flex-shrink: 0; background: #fff;
            border-left: 1px solid #dee2e6; overflow-y: auto; padding: 12px;
        }
        #editor-vars h6 { font-size: 12px; font-weight: 700; text-transform: uppercase; color: #6c757d; margin-bottom: 8px; border-bottom: 1px solid #eee; padding-bottom: 4px; }
        .var-btn {
            display: flex; justify-content: space-between; align-items: center;
            width: 100%; text-align: left; background: #f8f9fa; border: 1px solid #e9ecef;
            border-radius: 4px; padding: 5px 8px; margin-bottom: 4px; font-size: 11px;
            cursor: pointer; transition: background .1s;
        }
        .var-btn:hover { background: #e8f0fe; border-color: #0d6efd; }
        .var-btn code { font-size: 10px; color: #0d6efd; }

        /* Bottom action bar */
        #editor-actions {
            background: #fff; border-top: 1px solid #dee2e6;
            padding: 8px 16px; display: flex; align-items: center; justify-content: flex-end;
            gap: 8px; flex-shrink: 0; z-index: 100;
        }
    </style>
</head>
<body>
<div id="editor-shell">

    {{-- TOP TOOLBAR --}}
    <div id="editor-topbar">
        <span class="topbar-title"><i class="ti ti-file-text me-1"></i>@yield('page-title', 'E-Letter Editor')</span>
        <div class="vr"></div>

        <select id="tbFontFamily" class="form-select form-select-sm" style="width:130px">
            <option>Arial</option>
            <option>Times New Roman</option>
            <option>Calibri</option>
            <option>Courier New</option>
            <option>Georgia</option>
            <option>Verdana</option>
        </select>
        <input type="number" id="tbFontSize" class="form-control form-control-sm" style="width:60px" value="14" min="6" max="96">
        <select id="tbLineHeight" class="form-select form-select-sm" style="width:92px" title="Jarak Baris">
            <option value="1">1.0</option>
            <option value="1.16" selected>1.15</option>
            <option value="1.5">1.5</option>
            <option value="2">2.0</option>
            <option value="2.5">2.5</option>
            <option value="3">3.0</option>
        </select>
        <div class="vr"></div>
        <button type="button" id="tbBold" class="btn btn-sm btn-outline-secondary fw-bold" title="Bold">B</button>
        <button type="button" id="tbItalic" class="btn btn-sm btn-outline-secondary fst-italic" title="Italic">I</button>
        <button type="button" id="tbUnderline" class="btn btn-sm btn-outline-secondary text-decoration-underline" title="Underline">U</button>
        <input type="color" id="tbColor" value="#000000" title="Warna Teks">
        <div class="vr"></div>
        <button type="button" id="tbAlignLeft" class="btn btn-sm btn-outline-secondary" title="Align Left"><i class="ti ti-align-left"></i></button>
        <button type="button" id="tbAlignCenter" class="btn btn-sm btn-outline-secondary" title="Align Center"><i class="ti ti-align-center"></i></button>
        <button type="button" id="tbAlignRight" class="btn btn-sm btn-outline-secondary" title="Align Right"><i class="ti ti-align-right"></i></button>
        <button type="button" id="tbAlignJustify" class="btn btn-sm btn-outline-secondary" title="Justify"><i class="ti ti-align-justified"></i></button>
        <div class="vr"></div>
        <button type="button" id="tbAddText" class="btn btn-sm btn-primary" title="Tambah Teks"><i class="ti ti-text-size me-1"></i>Add Text</button>
        <button type="button" id="tbAddImage" class="btn btn-sm btn-outline-primary" title="Tambah Gambar"><i class="ti ti-photo me-1"></i>Gambar</button>
        <input type="file" id="tbImageInput" accept="image/*" class="d-none">
        <button type="button" id="tbDelete" class="btn btn-sm btn-outline-danger" title="Hapus Objek"><i class="ti ti-trash me-1"></i>Hapus</button>
        <div class="vr"></div>
        <button type="button" id="tbUndo" class="btn btn-sm btn-outline-secondary" title="Undo"><i class="ti ti-arrow-back-up"></i></button>
        <button type="button" id="tbRedo" class="btn btn-sm btn-outline-secondary" title="Redo"><i class="ti ti-arrow-forward-up"></i></button>
        <div class="vr"></div>
        <button type="button" id="tbAddPage" class="btn btn-sm btn-success" title="Tambah Halaman"><i class="ti ti-plus me-1"></i>Halaman</button>
        <button type="button" id="tbDeletePage" class="btn btn-sm btn-outline-danger" title="Hapus Halaman"><i class="ti ti-trash me-1"></i>Halaman</button>

        {{-- Custom variable modal trigger --}}
        <div class="vr"></div>
        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#addVariable" title="Variabel Kustom">
            <i class="ti ti-variable me-1"></i>Custom Var
        </button>
    </div>

    {{-- BODY --}}
    <div id="editor-body">

        {{-- LEFT SIDEBAR --}}
        <div id="editor-sidebar">
            <div class="sidebar-section">
                <h6>Info Template</h6>
                @yield('sidebar-fields')
            </div>

            @yield('number-config-section')

            <div class="sidebar-section">
                <h6>Halaman</h6>
                <div id="pageThumbs"></div>
                <div class="d-grid gap-1 mt-2">
                    <button type="button" id="sbAddPage" class="btn btn-sm btn-success"><i class="ti ti-plus me-1"></i>Tambah Halaman</button>
                    <button type="button" id="sbDeletePage" class="btn btn-sm btn-outline-danger"><i class="ti ti-trash me-1"></i>Hapus Halaman</button>
                </div>
            </div>

            <div class="sidebar-section">
                <h6>Layers</h6>
                <div class="d-flex gap-1 mb-2">
                    <button type="button" id="layerMoveUp" class="btn btn-sm btn-outline-secondary flex-fill" title="Naik (atas)"><i class="ti ti-arrow-up"></i></button>
                    <button type="button" id="layerMoveDown" class="btn btn-sm btn-outline-secondary flex-fill" title="Turun (bawah)"><i class="ti ti-arrow-down"></i></button>
                    <button type="button" id="layerToFront" class="btn btn-sm btn-outline-secondary flex-fill" title="Paling depan"><i class="ti ti-stack-push"></i></button>
                    <button type="button" id="layerToBack" class="btn btn-sm btn-outline-secondary flex-fill" title="Paling belakang"><i class="ti ti-stack-pop"></i></button>
                </div>
                <div id="layersList"></div>
                <div id="layersEmpty">Belum ada layer</div>
            </div>
        </div>

        {{-- CANVAS AREA --}}
        <div id="editor-canvas-area">
            <div id="canvasContainer"></div>
        </div>

        {{-- RIGHT PANEL: VARIABLES --}}
        <div id="editor-vars">
            <h6>KOP SURAT</h6>
            <button class="var-btn" onclick="insertVar('[no_surat]')">No Surat <code>[no_surat]</code></button>
            <button class="var-btn" onclick="insertVar('[romawi]')">Bulan Romawi <code>[romawi]</code></button>
            <button class="var-btn" onclick="insertVar('[tahun]')">Tahun <code>[tahun]</code></button>
            <button class="var-btn" onclick="insertVar('[tgl_surat]')">Tgl Terbit <code>[tgl_surat]</code></button>

            <h6 class="mt-3">ISI SURAT</h6>
            <button class="var-btn" onclick="insertVar('[hari]')">Hari <code>[hari]</code></button>
            <button class="var-btn" onclick="insertVar('[mulai]')">Tgl Mulai <code>[mulai]</code></button>
            <button class="var-btn" onclick="insertVar('[selesai]')">Tgl Selesai <code>[selesai]</code></button>
            <button class="var-btn" onclick="insertVar('[sign_2]')">TTD Karyawan <code>[sign_2]</code></button>
            <button class="var-btn" onclick="insertVar('[esign]')">TTD HRD <code>[esign]</code></button>

            <h6 class="mt-3">PEGAWAI</h6>
            <button class="var-btn" onclick="insertVar('[no_karyawan]')">No Karyawan <code>[no_karyawan]</code></button>
            <button class="var-btn" onclick="insertVar('[nama_karyawan]')">Nama <code>[nama_karyawan]</code></button>
            <button class="var-btn" onclick="insertVar('[nik_ktp]')">NIK KTP <code>[nik_ktp]</code></button>
            <button class="var-btn" onclick="insertVar('[jenis_kelamin]')">Jenis Kelamin <code>[jenis_kelamin]</code></button>
            <button class="var-btn" onclick="insertVar('[ttl]')">TTL <code>[ttl]</code></button>
            <button class="var-btn" onclick="insertVar('[alamat]')">Alamat <code>[alamat]</code></button>
            <button class="var-btn" onclick="insertVar('[handphone]')">No HP <code>[handphone]</code></button>
            <button class="var-btn" onclick="insertVar('[jabatan]')">Jabatan <code>[jabatan]</code></button>

            <h6 class="mt-3">LOKASI PROJECT</h6>
            <button class="var-btn" onclick="insertVar('[lokasi_project]')">Lokasi <code>[lokasi_project]</code></button>
            <button class="var-btn" onclick="insertVar('[area]')">Area <code>[area]</code></button>
            <button class="var-btn" onclick="insertVar('[nama_client]')">Nama Client <code>[nama_client]</code></button>
            <button class="var-btn" onclick="insertVar('[jabatan_client]')">Jabatan Client <code>[jabatan_client]</code></button>
            <button class="var-btn" onclick="insertVar('[area_description]')">Deskripsi <code>[area_description]</code></button>

            <h6 class="mt-3">GAJI</h6>
            <button class="var-btn" onclick="insertVar('[gaji]')">Gaji <code>[gaji]</code></button>
            <button class="var-btn" onclick="insertVar('[tunjangan]')">Tunjangan <code>[tunjangan]</code></button>
            <button class="var-btn" onclick="insertVar('[komisi]')">Komisi <code>[komisi]</code></button>
            <button class="var-btn" onclick="insertVar('[potongan]')">Potongan <code>[potongan]</code></button>

            <h6 class="mt-3" id="customVarHeading" style="display:none">KUSTOM</h6>
            <div id="customVarButtons"></div>
        </div>

    </div>

    {{-- BOTTOM ACTION BAR --}}
    <div id="editor-actions">
        <a href="{{ route('letters.index') }}" class="btn btn-light btn-sm px-4" target="_self" data-confirm-leave>
            <i class="ti ti-arrow-left me-1"></i>Kembali
        </a>
        @yield('action-buttons')
    </div>

</div>

{{-- MODAL: ADD CUSTOM VARIABLE --}}
<div class="modal fade" id="addVariable" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary">
                <h5 class="modal-title text-white">Tambah Variabel Kustom</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="form-label fw-bold">Nama Keterangan</label>
                    <input type="text" id="var_name" class="form-control" placeholder="Contoh: No Sertifikat">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Kode Variabel</label>
                    <div class="input-group">
                        <span class="input-group-text">[</span>
                        <input type="text" id="var_code" class="form-control" placeholder="no_sertifikat">
                        <span class="input-group-text">]</span>
                    </div>
                </div>
                <div class="mt-4">
                    <h6 class="fw-bold border-bottom pb-2">Variabel Terpasang:</h6>
                    <ul class="list-group list-group-flush" id="tempVarList">
                        @yield('existing-custom-vars')
                    </ul>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary" onclick="addNewVariable()">Simpan Variabel</button>
            </div>
        </div>
    </div>
</div>

<script src="/admin/assets/js/jquery-3.7.1.min.js"></script>
<script src="/admin/assets/js/bootstrap.bundle.min.js"></script>
<script src="/admin/assets/js/fabric-5.5.2.min.js?v={{ filemtime(public_path('admin/assets/js/fabric-5.5.2.min.js')) }}"></script>
<script src="/admin/assets/js/fabric-letter-editor.js?v={{ filemtime(public_path('admin/assets/js/fabric-letter-editor.js')) }}"></script>
<script>
    const UPLOAD_IMAGE_URL = '{{ route("letters.upload-image") }}';
    const editor = new FabricLetterEditor();

    function insertVar(val) {
        editor.insertVariable(val);
    }

    let varCount = 0;

    function addNewVariable() {
        const name = document.getElementById('var_name').value.trim();
        const codeInput = document.getElementById('var_code').value.trim();
        const code = codeInput.toLowerCase().replace(/[^a-z0-9_]/g, '');
        if (!name || !code) { alert('Nama dan Kode variabel harus diisi!'); return; }
        const fullCode = '[' + code + ']';

        const list = document.getElementById('tempVarList');
        const li = document.createElement('li');
        li.className = 'list-group-item d-flex justify-content-between align-items-center px-0';
        li.innerHTML = '<div><span class="fw-medium">' + name + '</span> <code class="ms-1">' + fullCode + '</code></div>' +
            '<button type="button" class="btn btn-sm btn-outline-danger border-0" onclick="removeTempVar(this,' + varCount + ')"><i class="ti ti-trash"></i></button>';
        list.appendChild(li);

        const heading = document.getElementById('customVarHeading');
        heading.style.display = '';
        const btnContainer = document.getElementById('customVarButtons');
        const btn = document.createElement('button');
        btn.className = 'var-btn';
        btn.id = 'cvbtn-' + varCount;
        btn.setAttribute('onclick', "insertVar('" + fullCode + "')");
        btn.innerHTML = name + ' <code>' + fullCode + '</code>';
        btnContainer.appendChild(btn);

        const container = document.getElementById('customVarsContainer');
        const inputDiv = document.createElement('div');
        inputDiv.id = 'input-group-' + varCount;
        inputDiv.innerHTML = '<input type="hidden" name="custom_vars[' + varCount + '][name]" value="' + name + '">' +
            '<input type="hidden" name="custom_vars[' + varCount + '][variable]" value="' + code + '">';
        container.appendChild(inputDiv);

        document.getElementById('var_name').value = '';
        document.getElementById('var_code').value = '';
        varCount++;
    }

    function removeTempVar(btn, id) {
        btn.closest('li').remove();
        const ig = document.getElementById('input-group-' + id);
        if (ig) ig.remove();
        const cb = document.getElementById('cvbtn-' + id);
        if (cb) cb.remove();
    }

    document.getElementById('sbAddPage').addEventListener('click', () => editor.addPage());
    document.getElementById('sbDeletePage').addEventListener('click', () => editor.deletePage());

    const selectConfig = document.getElementById('selectNumberConfig');
    const manualFields = document.getElementById('manualNumberFields');

    function updateNumberPreview() {
        const sel = selectConfig.value;
        let format, prefix, padding;
        if (sel) {
            const opt = selectConfig.options[selectConfig.selectedIndex];
            format  = opt.dataset.format || '';
            prefix  = opt.dataset.prefix || '';
            padding = parseInt(opt.dataset.padding) || 3;
            manualFields.style.display = 'none';
            document.getElementById('number_format').value  = format;
            document.getElementById('number_prefix').value  = prefix;
            document.getElementById('number_padding').value = padding;
        } else {
            format  = document.getElementById('number_format').value || '{no}/{kode_tipe}/{romawi}/{tahun}';
            prefix  = document.getElementById('number_prefix').value || '';
            padding = parseInt(document.getElementById('number_padding').value) || 3;
            manualFields.style.display = '';
        }
        const no = String(1).padStart(padding, '0');
        const now = new Date();
        const romawi = ['I','II','III','IV','V','VI','VII','VIII','IX','X','XI','XII'][now.getMonth()];
        const tahun = now.getFullYear();
        const tahun_pendek = String(tahun).slice(-2);
        const bulan = String(now.getMonth() + 1).padStart(2, '0');
        const preview = format
            .replace('{no}', no).replace('{romawi}', romawi)
            .replace('{tahun}', tahun).replace('{tahun_pendek}', tahun_pendek)
            .replace('{bulan}', bulan).replace('{kode_site}', 'SITE')
            .replace('{kode_tipe}', 'TIPE').replace('{kode_company}', 'COMP')
            .replace('{kode_jabatan}', 'JAB').replace('{prefix}', prefix);
        document.getElementById('numberPreview').innerText = preview;
    }

    selectConfig.addEventListener('change', updateNumberPreview);
    ['number_format','number_prefix','number_padding'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.addEventListener('input', updateNumberPreview);
    });

    updateNumberPreview();
</script>
@yield('page-scripts')
</body>
</html>
