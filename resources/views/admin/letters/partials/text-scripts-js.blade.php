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
    btn.type = 'button';
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

function removeVar(btn, id) {
    if (confirm('Hapus variabel ini?')) {
        if (id) {
            const container = document.getElementById('customVarsContainer');
            container.insertAdjacentHTML('beforeend', '<input type="hidden" name="delete_vars[]" value="' + id + '">');
        }
        btn.closest('li').remove();
    }
}

(function () {
    const selectConfig = document.getElementById('selectNumberConfig');
    const manualFields = document.getElementById('manualNumberFields');
    if (!selectConfig) return;

    function updateNumberPreview() {
        const sel = selectConfig.value;
        let format, prefix, padding;
        if (sel) {
            const opt = selectConfig.options[selectConfig.selectedIndex];
            format = opt.dataset.format || '';
            prefix = opt.dataset.prefix || '';
            padding = parseInt(opt.dataset.padding) || 3;
            manualFields.style.display = 'none';
            document.getElementById('number_format').value = format;
            document.getElementById('number_prefix').value = prefix;
            document.getElementById('number_padding').value = padding;
        } else {
            format = document.getElementById('number_format').value || '{no}/{kode_tipe}/{romawi}/{tahun}';
            prefix = document.getElementById('number_prefix').value || '';
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
    ['number_format', 'number_prefix', 'number_padding'].forEach(function (id) {
        const el = document.getElementById(id);
        if (el) el.addEventListener('input', updateNumberPreview);
    });
    updateNumberPreview();
})();
