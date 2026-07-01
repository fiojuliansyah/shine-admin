function insertFloatBox(editor) {
    const html =
        '<div class="floating-box" style="position:absolute;left:80px;top:80px;width:220px;min-height:60px;">' +
            '<span class="fb-drag" contenteditable="false">&#x283F; geser</span>' +
            '<div class="fb-body">Teks di sini...</div>' +
            '<span class="fb-resize" contenteditable="false"></span>' +
        '</div>';
    editor.getBody().style.position = 'relative';
    editor.insertContent(html);
}

function insertFloatImage(editor) {
    const input = document.createElement('input');
    input.type = 'file';
    input.accept = 'image/*';
    input.onchange = function () {
        const file = input.files && input.files[0];
        if (!file) return;

        const xhr = new XMLHttpRequest();
        xhr.withCredentials = true;
        xhr.open('POST', '{{ route("letters.upload-image") }}');
        xhr.setRequestHeader('X-CSRF-TOKEN', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
        xhr.setRequestHeader('Accept', 'application/json');

        xhr.onload = function () {
            if (xhr.status < 200 || xhr.status >= 300) {
                editor.notificationManager.open({ text: 'Upload gagal: HTTP ' + xhr.status, type: 'error' });
                return;
            }
            let json;
            try { json = JSON.parse(xhr.responseText); } catch (e) {
                editor.notificationManager.open({ text: 'Response tidak valid dari server.', type: 'error' });
                return;
            }
            const url = json.location || json.url;
            if (!url) { editor.notificationManager.open({ text: 'Response tidak menyertakan URL gambar.', type: 'error' }); return; }

            const html =
                '<div class="floating-box floating-image" style="position:absolute;left:80px;top:80px;width:200px;min-height:auto;padding:0;">' +
                    '<span class="fb-drag" contenteditable="false">&#x283F; geser</span>' +
                    '<div class="fb-body" contenteditable="false" style="min-height:auto;">' +
                        '<img src="' + url + '" style="width:100%;height:auto;display:block;">' +
                    '</div>' +
                    '<span class="fb-resize" contenteditable="false"></span>' +
                '</div>';
            editor.getBody().style.position = 'relative';
            editor.insertContent(html);
        };

        xhr.onerror = function () {
            editor.notificationManager.open({ text: 'Gagal terhubung ke server saat upload.', type: 'error' });
        };

        const formData = new FormData();
        formData.append('file', file, file.name);
        formData.append('image', file, file.name);
        xhr.send(formData);
    };
    input.click();
}

function initFloatingBoxes(editor) {
    const doc = editor.getDoc();
    const body = editor.getBody();
    body.style.position = 'relative';

    let cur = null, mode = null;
    let startX = 0, startY = 0, origLeft = 0, origTop = 0, origW = 0, origH = 0;

    body.addEventListener('mousedown', function (e) {
        const dragH = e.target.closest ? e.target.closest('.fb-drag') : null;
        const resizeH = e.target.closest ? e.target.closest('.fb-resize') : null;
        if (dragH) { cur = dragH.closest('.floating-box'); mode = 'drag'; }
        else if (resizeH) { cur = resizeH.closest('.floating-box'); mode = 'resize'; }
        else { return; }
        e.preventDefault();
        startX = e.clientX; startY = e.clientY;
        origLeft = parseFloat(cur.style.left) || 0;
        origTop = parseFloat(cur.style.top) || 0;
        origW = cur.offsetWidth;
        origH = cur.offsetHeight;
    });

    doc.addEventListener('mousemove', function (e) {
        if (!cur) return;
        const dx = e.clientX - startX, dy = e.clientY - startY;
        if (mode === 'drag') {
            cur.style.left = Math.max(0, origLeft + dx) + 'px';
            cur.style.top = Math.max(0, origTop + dy) + 'px';
        } else if (mode === 'resize') {
            cur.style.width = Math.max(60, origW + dx) + 'px';
            if (!cur.classList.contains('floating-image')) {
                cur.style.minHeight = Math.max(30, origH + dy) + 'px';
            }
        }
        syncMceStyle(cur);
    });

    doc.addEventListener('mouseup', function () {
        if (cur) { syncMceStyle(cur); editor.setDirty(true); }
        cur = null; mode = null;
    });
}

// TinyMCE menyimpan style asli di atribut data-mce-style dan memakainya saat
// getContent(). Perubahan langsung ke element.style tidak ikut tersimpan,
// sehingga posisi floating box "loncat" balik saat disimpan. Fungsi ini
// menyamakan data-mce-style dengan style aktual agar posisi tersimpan benar.
function syncMceStyle(el) {
    if (!el) return;
    el.setAttribute('data-mce-style', el.style.cssText);
}

