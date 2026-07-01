function letterImageUploadHandler(blobInfo, success, failure, progress) {
    const xhr = new XMLHttpRequest();
    xhr.withCredentials = true;
    xhr.open('POST', '{{ route("letters.upload-image") }}');
    xhr.setRequestHeader('X-CSRF-TOKEN', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
    xhr.setRequestHeader('Accept', 'application/json');

    xhr.upload.onprogress = function (e) {
        if (progress && e.total) progress((e.loaded / e.total) * 100);
    };

    xhr.onload = function () {
        if (xhr.status === 403) { failure('Tidak diizinkan mengunggah.', { remove: true }); return; }
        if (xhr.status < 200 || xhr.status >= 300) {
            failure('Upload gagal: HTTP ' + xhr.status, { remove: true });
            return;
        }
        let json;
        try { json = JSON.parse(xhr.responseText); } catch (e) {
            failure('Response tidak valid dari server.', { remove: true });
            return;
        }
        const url = json.location || json.url;
        if (!url) { failure('Response tidak menyertakan URL gambar.', { remove: true }); return; }
        success(url);
    };

    xhr.onerror = function () {
        failure('Gagal terhubung ke server saat upload.', { remove: true });
    };

    const formData = new FormData();
    formData.append('file', blobInfo.blob(), blobInfo.filename());
    formData.append('image', blobInfo.blob(), blobInfo.filename());
    xhr.send(formData);
}
