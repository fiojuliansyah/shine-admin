{{--
    Crop dokumen sebelum upload (KTP, KK, Ijazah, SKCK, dst.).

    Pemakaian: tambahkan atribut data-doc-crop pada <input type="file">.
    Saat file gambar dipilih, modal crop muncul:
      - 4 sudut dokumen dideteksi otomatis (OpenCV.js, dimuat saat pertama dipakai),
      - sudut bisa digeser manual, gambar bisa diputar 90°,
      - hasil diluruskan (koreksi perspektif) lalu menggantikan file di input.
    Batal = pilihan file dikosongkan (dokumen tidak boleh diunggah tanpa crop).
    Setelah crop, event "change" dikirim ulang ke input sehingga handler halaman tetap jalan.
--}}
@once
<div class="modal fade" id="docCropModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-fullscreen-sm-down">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h5 class="modal-title"><i class="ti ti-crop me-1"></i>Sesuaikan Dokumen</h5>
            </div>
            <div class="modal-body">
                <p class="small text-muted mb-2" id="docCropHint">
                    Geser 4 titik biru tepat di sudut dokumen. Hasil akan diluruskan otomatis.
                </p>
                <div class="position-relative text-center bg-dark rounded" style="touch-action: none; min-height: 260px;">
                    <canvas id="docCropCanvas" style="max-width: 100%; display: block; margin: 0 auto; touch-action: none; cursor: crosshair;"></canvas>
                    <div id="docCropLoading" class="position-absolute top-0 start-0 w-100 h-100 d-flex flex-column align-items-center justify-content-center text-white"
                        style="background: rgba(0,0,0,.55);">
                        <div class="spinner-border mb-2"></div>
                        <div class="small" id="docCropLoadingText">Mendeteksi sudut dokumen...</div>
                    </div>
                </div>
                <div class="d-flex flex-wrap gap-2 mt-2">
                    <button type="button" class="btn btn-sm btn-outline-primary" id="docCropDetect"><i class="ti ti-scan me-1"></i>Deteksi Otomatis</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="docCropFull"><i class="ti ti-maximize me-1"></i>Seluruh Gambar</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="docCropRotate"><i class="ti ti-rotate-clockwise me-1"></i>Putar 90°</button>
                </div>
                <div id="docCropStatus" class="small mt-2"></div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-light" id="docCropCancel">Batal</button>
                <button type="button" class="btn btn-primary" id="docCropApply" disabled><i class="ti ti-check me-1"></i>Gunakan Hasil Crop</button>
            </div>
        </div>
    </div>
</div>

@push('js')
<script>
(function () {
    var OPENCV_URL = 'https://docs.opencv.org/4.10.0/opencv.js';
    var MAX_WORK_SIDE = 2400;   // sisi terpanjang gambar yang diproses / hasil
    var DETECT_SIDE = 700;      // ukuran kecil untuk deteksi tepi (cepat)
    var HANDLE_RADIUS = 14;

    var modalEl = document.getElementById('docCropModal');
    var modal = bootstrap.Modal.getOrCreateInstance(modalEl);
    var canvas = document.getElementById('docCropCanvas');
    var ctx = canvas.getContext('2d');
    var loadingEl = document.getElementById('docCropLoading');
    var loadingText = document.getElementById('docCropLoadingText');
    var statusEl = document.getElementById('docCropStatus');
    var applyBtn = document.getElementById('docCropApply');

    var state = { input: null, file: null, source: null, corners: null, scale: 1, drag: -1, applied: false };
    var cvReady = null;

    // ---------- OpenCV (dimuat sekali, saat pertama dibutuhkan) ----------
    function loadOpenCv() {
        if (cvReady) return cvReady;
        cvReady = new Promise(function (resolve, reject) {
            function done() {
                var c = window.cv;
                if (c && typeof c.then === 'function') {
                    c.then(function (ready) { window.cv = ready; resolve(ready); });
                } else if (c && c.Mat) {
                    resolve(c);
                } else if (c) {
                    c.onRuntimeInitialized = function () { resolve(window.cv); };
                } else {
                    reject(new Error('OpenCV tidak tersedia'));
                }
            }
            if (window.cv) return done();
            var s = document.createElement('script');
            s.src = OPENCV_URL;
            s.async = true;
            s.onload = done;
            s.onerror = function () { cvReady = null; reject(new Error('Gagal memuat OpenCV')); };
            document.head.appendChild(s);
        });
        return cvReady;
    }

    // ---------- Util ----------
    function setLoading(on, text) {
        loadingEl.classList.toggle('d-none', !on);
        loadingEl.classList.toggle('d-flex', on);
        if (text) loadingText.textContent = text;
    }
    function setStatus(cls, text) { statusEl.className = 'small mt-2 ' + cls; statusEl.textContent = text; }

    function fullCorners(w, h, inset) {
        var dx = w * (inset || 0), dy = h * (inset || 0);
        return [{ x: dx, y: dy }, { x: w - dx, y: dy }, { x: w - dx, y: h - dy }, { x: dx, y: h - dy }];
    }

    // Urutkan titik: kiri-atas, kanan-atas, kanan-bawah, kiri-bawah
    function orderCorners(pts) {
        var bySum = pts.slice().sort(function (a, b) { return (a.x + a.y) - (b.x + b.y); });
        var byDiff = pts.slice().sort(function (a, b) { return (a.y - a.x) - (b.y - b.x); });
        return [bySum[0], byDiff[0], bySum[3], byDiff[3]];
    }

    function dist(a, b) { return Math.hypot(a.x - b.x, a.y - b.y); }

    // Muat file ke canvas sumber (dibatasi MAX_WORK_SIDE, orientasi EXIF dihormati browser)
    function loadSource(file) {
        return new Promise(function (resolve, reject) {
            var url = URL.createObjectURL(file);
            var img = new Image();
            img.onload = function () {
                var ratio = Math.min(1, MAX_WORK_SIDE / Math.max(img.naturalWidth, img.naturalHeight));
                var c = document.createElement('canvas');
                c.width = Math.round(img.naturalWidth * ratio);
                c.height = Math.round(img.naturalHeight * ratio);
                c.getContext('2d').drawImage(img, 0, 0, c.width, c.height);
                URL.revokeObjectURL(url);
                resolve(c);
            };
            img.onerror = function () { URL.revokeObjectURL(url); reject(new Error('File bukan gambar yang valid.')); };
            img.src = url;
        });
    }

    // ---------- Tampilan & seret sudut ----------
    function layout() {
        var src = state.source;
        var bodyW = modalEl.querySelector('.modal-body').clientWidth;
        var maxW = Math.min(bodyW > 50 ? bodyW - 2 : Math.min(window.innerWidth - 40, 760), 900);
        var maxH = Math.max(window.innerHeight * 0.6, 260);
        state.scale = Math.min(maxW / src.width, maxH / src.height, 1);
        canvas.width = Math.round(src.width * state.scale);
        canvas.height = Math.round(src.height * state.scale);
        draw();
    }

    function draw() {
        if (!state.source) return;
        var s = state.scale, c = state.corners;
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        ctx.drawImage(state.source, 0, 0, canvas.width, canvas.height);
        if (!c) return;

        // Gelapkan area di luar dokumen
        ctx.save();
        ctx.fillStyle = 'rgba(0,0,0,.45)';
        ctx.beginPath();
        ctx.rect(0, 0, canvas.width, canvas.height);
        ctx.moveTo(c[0].x * s, c[0].y * s);
        for (var i = 3; i >= 0; i--) ctx.lineTo(c[i].x * s, c[i].y * s);
        ctx.closePath();
        ctx.fill('evenodd');
        ctx.restore();

        ctx.strokeStyle = '#3E6CFF';
        ctx.lineWidth = 2;
        ctx.beginPath();
        c.forEach(function (p, i) { i ? ctx.lineTo(p.x * s, p.y * s) : ctx.moveTo(p.x * s, p.y * s); });
        ctx.closePath();
        ctx.stroke();

        c.forEach(function (p) {
            ctx.beginPath();
            ctx.arc(p.x * s, p.y * s, HANDLE_RADIUS * 0.6, 0, Math.PI * 2);
            ctx.fillStyle = '#fff';
            ctx.fill();
            ctx.lineWidth = 3;
            ctx.strokeStyle = '#3E6CFF';
            ctx.stroke();
        });
    }

    function pointer(e) {
        var r = canvas.getBoundingClientRect();
        var t = e.touches ? e.touches[0] : e;
        return {
            x: (t.clientX - r.left) * (canvas.width / r.width) / state.scale,
            y: (t.clientY - r.top) * (canvas.height / r.height) / state.scale
        };
    }

    function onDown(e) {
        if (!state.corners) return;
        var p = pointer(e), best = -1, bestD = (HANDLE_RADIUS * 2) / state.scale;
        state.corners.forEach(function (c, i) { var d = dist(c, p); if (d < bestD) { bestD = d; best = i; } });
        state.drag = best;
        if (best >= 0) e.preventDefault();
    }
    function onMove(e) {
        if (state.drag < 0) return;
        e.preventDefault();
        var p = pointer(e), src = state.source;
        state.corners[state.drag] = {
            x: Math.max(0, Math.min(src.width, p.x)),
            y: Math.max(0, Math.min(src.height, p.y))
        };
        draw();
    }
    function onUp() { state.drag = -1; }

    canvas.addEventListener('mousedown', onDown);
    canvas.addEventListener('touchstart', onDown, { passive: false });
    window.addEventListener('mousemove', onMove);
    window.addEventListener('touchmove', onMove, { passive: false });
    window.addEventListener('mouseup', onUp);
    window.addEventListener('touchend', onUp);
    // Modal bertumpuk (mis. dibuka dari modal Upload Dokumen): pastikan modal di bawahnya tetap aktif
    modalEl.addEventListener('shown.bs.modal', function () {
        var backdrops = document.querySelectorAll('.modal-backdrop');
        if (backdrops.length > 1) backdrops[backdrops.length - 1].style.zIndex = 1060;
        modalEl.style.zIndex = 1065;
    });
    modalEl.addEventListener('hidden.bs.modal', function () {
        if (document.querySelector('.modal.show')) document.body.classList.add('modal-open');
    });

    window.addEventListener('resize', function () { if (modalEl.classList.contains('show') && state.source) layout(); });

    // ---------- Deteksi sudut otomatis ----------
    function detectCorners(cv, src) {
        var ratio = Math.min(1, DETECT_SIDE / Math.max(src.width, src.height));
        var small = document.createElement('canvas');
        small.width = Math.round(src.width * ratio);
        small.height = Math.round(src.height * ratio);
        small.getContext('2d').drawImage(src, 0, 0, small.width, small.height);

        var mat = cv.imread(small), gray = new cv.Mat(), edges = new cv.Mat();
        var contours = new cv.MatVector(), hierarchy = new cv.Mat();
        var kernel = cv.getStructuringElement(cv.MORPH_RECT, new cv.Size(5, 5));
        var found = null;

        try {
            cv.cvtColor(mat, gray, cv.COLOR_RGBA2GRAY);
            cv.GaussianBlur(gray, gray, new cv.Size(5, 5), 0);
            cv.Canny(gray, edges, 50, 150);
            cv.dilate(edges, edges, kernel);
            cv.findContours(edges, contours, hierarchy, cv.RETR_LIST, cv.CHAIN_APPROX_SIMPLE);

            var minArea = small.width * small.height * 0.15, bestArea = 0;
            for (var i = 0; i < contours.size(); i++) {
                var cnt = contours.get(i);
                var area = cv.contourArea(cnt);
                if (area > minArea && area > bestArea) {
                    var approx = new cv.Mat();
                    cv.approxPolyDP(cnt, approx, 0.02 * cv.arcLength(cnt, true), true);
                    if (approx.rows === 4 && cv.isContourConvex(approx)) {
                        var pts = [];
                        for (var j = 0; j < 4; j++) {
                            pts.push({ x: approx.data32S[j * 2] / ratio, y: approx.data32S[j * 2 + 1] / ratio });
                        }
                        found = orderCorners(pts);
                        bestArea = area;
                    }
                    approx.delete();
                }
                cnt.delete();
            }
        } finally {
            mat.delete(); gray.delete(); edges.delete(); contours.delete(); hierarchy.delete(); kernel.delete();
        }
        return found;
    }

    function runDetect() {
        setLoading(true, 'Mendeteksi sudut dokumen...');
        return loadOpenCv().then(function (cv) {
            var corners = detectCorners(cv, state.source);
            if (corners) {
                state.corners = corners;
                setStatus('text-success', 'Sudut dokumen terdeteksi. Periksa dan geser titik bila perlu.');
            } else {
                state.corners = fullCorners(state.source.width, state.source.height, 0.04);
                setStatus('text-warning', 'Sudut tidak terdeteksi otomatis. Geser 4 titik ke sudut dokumen.');
            }
        }).catch(function () {
            state.corners = state.corners || fullCorners(state.source.width, state.source.height, 0.04);
            setStatus('text-warning', 'Deteksi otomatis tidak tersedia (koneksi). Geser 4 titik secara manual.');
        }).finally(function () {
            setLoading(false);
            applyBtn.disabled = false;
            draw();
        });
    }

    // ---------- Hasil crop (koreksi perspektif) ----------
    function warp() {
        var c = orderCorners(state.corners), src = state.source; // rapikan bila titik diseret bersilangan
        var outW = Math.round(Math.max(dist(c[0], c[1]), dist(c[3], c[2])));
        var outH = Math.round(Math.max(dist(c[0], c[3]), dist(c[1], c[2])));
        var ratio = Math.min(1, MAX_WORK_SIDE / Math.max(outW, outH));
        outW = Math.max(1, Math.round(outW * ratio));
        outH = Math.max(1, Math.round(outH * ratio));

        var out = document.createElement('canvas');
        out.width = outW;
        out.height = outH;

        return loadOpenCv().then(function (cv) {
            var mat = cv.imread(src), dst = new cv.Mat();
            var from = cv.matFromArray(4, 1, cv.CV_32FC2, [c[0].x, c[0].y, c[1].x, c[1].y, c[2].x, c[2].y, c[3].x, c[3].y]);
            var to = cv.matFromArray(4, 1, cv.CV_32FC2, [0, 0, outW, 0, outW, outH, 0, outH]);
            var M = cv.getPerspectiveTransform(from, to);
            try {
                cv.warpPerspective(mat, dst, M, new cv.Size(outW, outH), cv.INTER_LINEAR, cv.BORDER_REPLICATE);
                cv.imshow(out, dst);
            } finally {
                mat.delete(); dst.delete(); from.delete(); to.delete(); M.delete();
            }
            return out;
        }).catch(function () {
            // Tanpa OpenCV: potong kotak terluar dari 4 titik (tanpa koreksi perspektif)
            var xs = c.map(function (p) { return p.x; }), ys = c.map(function (p) { return p.y; });
            var x = Math.min.apply(null, xs), y = Math.min.apply(null, ys);
            var w = Math.max.apply(null, xs) - x, h = Math.max.apply(null, ys) - y;
            out.width = Math.round(w);
            out.height = Math.round(h);
            out.getContext('2d').drawImage(src, x, y, w, h, 0, 0, out.width, out.height);
            return out;
        });
    }

    // ---------- Alur modal ----------
    function open(input, file) {
        state.input = input;
        state.file = file;
        state.applied = false;
        state.corners = null;
        applyBtn.disabled = true;
        setStatus('text-muted', '');
        setLoading(true, 'Memuat gambar...');
        modal.show();

        loadSource(file).then(function (srcCanvas) {
            state.source = srcCanvas;
            layout();
            return runDetect();
        }).catch(function (err) {
            modal.hide();
            input.value = '';
            alert(err.message || 'Gagal membuka gambar.');
        });
    }

    document.getElementById('docCropDetect').addEventListener('click', function () { if (state.source) runDetect(); });
    document.getElementById('docCropFull').addEventListener('click', function () {
        if (!state.source) return;
        state.corners = fullCorners(state.source.width, state.source.height, 0);
        setStatus('text-muted', 'Memakai seluruh gambar.');
        draw();
    });
    document.getElementById('docCropRotate').addEventListener('click', function () {
        if (!state.source) return;
        var src = state.source, r = document.createElement('canvas');
        r.width = src.height;
        r.height = src.width;
        var rctx = r.getContext('2d');
        rctx.translate(r.width, 0);
        rctx.rotate(Math.PI / 2);
        rctx.drawImage(src, 0, 0);
        // Putar titik sudut ikut gambar: (x, y) -> (H - y, x)
        if (state.corners) {
            state.corners = orderCorners(state.corners.map(function (p) { return { x: src.height - p.y, y: p.x }; }));
        }
        state.source = r;
        layout();
    });

    document.getElementById('docCropCancel').addEventListener('click', function () {
        if (state.input) state.input.value = ''; // tidak boleh upload tanpa crop
        modal.hide();
    });

    applyBtn.addEventListener('click', function () {
        if (!state.corners || !state.input) return;
        applyBtn.disabled = true;
        setLoading(true, 'Meluruskan dokumen...');

        warp().then(function (out) {
            return new Promise(function (resolve) { out.toBlob(resolve, 'image/jpeg', 0.9); });
        }).then(function (blob) {
            if (!blob) throw new Error('Gagal membuat gambar hasil crop.');
            var baseName = (state.file.name || 'dokumen').replace(/\.[^.]+$/, '');
            var cropped = new File([blob], baseName + '-crop.jpg', { type: 'image/jpeg', lastModified: Date.now() });
            var dt = new DataTransfer();
            dt.items.add(cropped);

            var input = state.input;
            input.files = dt.files;
            input._docCropDone = true; // lewati crop untuk event change berikut
            state.applied = true;
            modal.hide();
            input.dispatchEvent(new Event('change', { bubbles: true }));
        }).catch(function (err) {
            alert(err.message || 'Gagal memproses dokumen.');
        }).finally(function () {
            setLoading(false);
            applyBtn.disabled = false;
        });
    });

    // Tangkap pemilihan file pada input[data-doc-crop] (fase capture: sebelum handler halaman)
    document.addEventListener('change', function (e) {
        var input = e.target;
        if (!input.matches || !input.matches('input[type="file"][data-doc-crop]')) return;

        if (input._docCropDone) { input._docCropDone = false; return; } // hasil crop -> teruskan
        var file = input.files && input.files[0];
        if (!file || !/^image\//.test(file.type)) return;                // PDF / kosong -> biarkan

        e.stopImmediatePropagation();
        open(input, file);
    }, true);
})();
</script>
@endpush
@endonce
