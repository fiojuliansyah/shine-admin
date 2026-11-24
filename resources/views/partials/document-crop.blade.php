{{--
    Crop dokumen sebelum upload (KTP, KK, Ijazah, SKCK, dst.).

    Pemakaian: tambahkan atribut data-doc-crop pada <input type="file">.
    Tambahkan juga data-doc-crop-submit agar form langsung dikirim setelah crop selesai.
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
    // Bisa diarahkan ke file di server sendiri lewat OPENCV_JS_URL (lebih cepat dari CDN luar negeri)
    var OPENCV_URL = @json(config('services.opencv.url'));
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

    var state = { input: null, file: null, source: null, corners: null, scale: 1, drag: -1, applied: false, session: 0, userMoved: false };
    // ---------- OpenCV di Web Worker ----------
    // OpenCV.js (~9 MB, berisi WebAssembly) dimuat & dijalankan di thread terpisah agar
    // halaman TIDAK membeku saat file di-parse/di-compile. Thread utama hanya kirim gambar
    // kecil dan menerima 4 titik sudut.
    var WORKER_SOURCE = [
        'var ready = null;',
        'function init(url) {',
        '  if (ready) return ready;',
        '  ready = new Promise(function (resolve, reject) {',
        '    try { importScripts(url); } catch (e) { reject(e); return; }',
        '    var c = self.cv;',
        '    if (c && typeof c.then === "function") { c.then(function (r) { self.cv = r; resolve(r); }); }',
        '    else if (c && c.Mat) { resolve(c); }',
        '    else if (c) { c.onRuntimeInitialized = function () { resolve(self.cv); }; }',
        '    else { reject(new Error("OpenCV tidak tersedia")); }',
        '  });',
        '  return ready;',
        '}',
        'function order(p) {',
        '  var s = p.slice().sort(function (a, b) { return (a.x + a.y) - (b.x + b.y); });',
        '  var d = p.slice().sort(function (a, b) { return (a.y - a.x) - (b.y - b.x); });',
        '  return [s[0], d[0], s[3], d[3]];',
        '}',
        'function detect(cv, imageData) {',
        '  var mat = cv.matFromImageData(imageData), gray = new cv.Mat(), edges = new cv.Mat();',
        '  var contours = new cv.MatVector(), hierarchy = new cv.Mat();',
        '  var kernel = cv.getStructuringElement(cv.MORPH_RECT, new cv.Size(5, 5));',
        '  var found = null, bestArea = 0, minArea = imageData.width * imageData.height * 0.15;',
        '  try {',
        '    cv.cvtColor(mat, gray, cv.COLOR_RGBA2GRAY);',
        '    cv.GaussianBlur(gray, gray, new cv.Size(5, 5), 0);',
        '    cv.Canny(gray, edges, 50, 150);',
        '    cv.dilate(edges, edges, kernel);',
        '    cv.findContours(edges, contours, hierarchy, cv.RETR_LIST, cv.CHAIN_APPROX_SIMPLE);',
        '    for (var i = 0; i < contours.size(); i++) {',
        '      var cnt = contours.get(i), area = cv.contourArea(cnt);',
        '      if (area > minArea && area > bestArea) {',
        '        var approx = new cv.Mat();',
        '        cv.approxPolyDP(cnt, approx, 0.02 * cv.arcLength(cnt, true), true);',
        '        if (approx.rows === 4 && cv.isContourConvex(approx)) {',
        '          var pts = [];',
        '          for (var j = 0; j < 4; j++) pts.push({ x: approx.data32S[j * 2], y: approx.data32S[j * 2 + 1] });',
        '          found = order(pts); bestArea = area;',
        '        }',
        '        approx.delete();',
        '      }',
        '      cnt.delete();',
        '    }',
        '  } finally { mat.delete(); gray.delete(); edges.delete(); contours.delete(); hierarchy.delete(); kernel.delete(); }',
        '  return found;',
        '}',
        'self.onmessage = function (e) {',
        '  var m = e.data;',
        '  init(m.url).then(function (cv) {',
        '    if (m.type === "init") { self.postMessage({ id: m.id, ok: true }); return; }',
        '    self.postMessage({ id: m.id, ok: true, corners: detect(cv, m.image) });',
        '  }).catch(function (err) { self.postMessage({ id: m.id, ok: false, error: String(err && err.message || err) }); });',
        '};'
    ].join('\n');

    var worker = null, workerReady = false, readyPromise = null, pending = {}, reqId = 0;

    function cvIsReady() { return workerReady; }

    function callWorker(message, transfer) {
        return new Promise(function (resolve, reject) {
            var id = ++reqId;
            pending[id] = { resolve: resolve, reject: reject };
            message.id = id;
            message.url = new URL(OPENCV_URL, location.href).href;
            worker.postMessage(message, transfer || []);
        });
    }

    function loadOpenCv() {
        if (readyPromise) return readyPromise;
        readyPromise = new Promise(function (resolve, reject) {
            try {
                var blobUrl = URL.createObjectURL(new Blob([WORKER_SOURCE], { type: 'text/javascript' }));
                worker = new Worker(blobUrl);
            } catch (e) { reject(e); return; }

            worker.onmessage = function (e) {
                var p = pending[e.data.id];
                if (!p) return;
                delete pending[e.data.id];
                e.data.ok ? p.resolve(e.data) : p.reject(new Error(e.data.error));
            };
            worker.onerror = function (e) { reject(new Error(e.message || 'Worker OpenCV gagal')); };

            callWorker({ type: 'init' }).then(function () { workerReady = true; resolve(); }, reject);
        }).catch(function (err) {
            readyPromise = null; // boleh dicoba lagi nanti
            if (worker) { worker.terminate(); worker = null; }
            throw err;
        });
        return readyPromise;
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
        if (best >= 0) { e.preventDefault(); state.userMoved = true; }
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

    // ---------- Deteksi sudut otomatis (di worker) ----------
    function detectCorners(src) {
        var ratio = Math.min(1, DETECT_SIDE / Math.max(src.width, src.height));
        var small = document.createElement('canvas');
        small.width = Math.round(src.width * ratio);
        small.height = Math.round(src.height * ratio);
        var sctx = small.getContext('2d');
        sctx.drawImage(src, 0, 0, small.width, small.height);
        var image = sctx.getImageData(0, 0, small.width, small.height);

        return callWorker({ type: 'detect', image: image }, [image.data.buffer]).then(function (res) {
            if (!res.corners) return null;
            return res.corners.map(function (p) { return { x: p.x / ratio, y: p.y / ratio }; });
        });
    }

    function applyDetection(corners) {
        if (corners) {
            state.corners = corners;
            setStatus('text-success', 'Sudut dokumen terdeteksi. Periksa dan geser titik bila perlu.');
        } else {
            state.corners = fullCorners(state.source.width, state.source.height, 0.04);
            setStatus('text-warning', 'Sudut tidak terdeteksi otomatis. Geser 4 titik ke sudut dokumen.');
        }
        draw();
    }

    /**
     * Deteksi sudut di worker. Pelamar tidak pernah menunggu: titik manual langsung tampil,
     * hasil deteksi diterapkan begitu siap (selama titik belum digeser sendiri).
     */
    function runDetect(manualClick) {
        var session = state.session;

        state.corners = state.corners || fullCorners(state.source.width, state.source.height, 0.04);
        if (manualClick) state.userMoved = false;
        setLoading(false);
        applyBtn.disabled = false;
        draw();
        setStatus('text-muted', cvIsReady()
            ? 'Mendeteksi sudut dokumen...'
            : 'Menyiapkan deteksi otomatis (hanya pertama kali)... Anda bisa langsung menggeser titik.');

        return loadOpenCv().then(function () {
            if (session !== state.session || !modalEl.classList.contains('show')) return;
            return detectCorners(state.source).then(function (corners) {
                if (session !== state.session || !modalEl.classList.contains('show')) return;
                if (state.userMoved && !manualClick) {
                    setStatus('text-muted', 'Deteksi otomatis siap. Klik "Deteksi Otomatis" bila ingin dipakai.');
                    return;
                }
                applyDetection(corners);
            });
        }).catch(function () {
            if (session !== state.session) return;
            setStatus('text-warning', 'Deteksi otomatis tidak tersedia. Geser 4 titik secara manual.');
        });
    }

    // ---------- Hasil crop (koreksi perspektif) ----------
    var OUTPUT_MAX_SIDE = 2000; // sama dengan batas server untuk folder Documents

    /** Homografi 3x3 (h8 = 1) yang memetakan 4 titik "from" ke 4 titik "to". */
    function homography(from, to) {
        var A = [], i, j, k;
        for (i = 0; i < 4; i++) {
            var x = from[i].x, y = from[i].y, u = to[i].x, v = to[i].y;
            A.push([x, y, 1, 0, 0, 0, -u * x, -u * y, u]);
            A.push([0, 0, 0, x, y, 1, -v * x, -v * y, v]);
        }
        // Eliminasi Gauss dengan pivot parsial (sistem 8x8)
        for (i = 0; i < 8; i++) {
            var max = i;
            for (k = i + 1; k < 8; k++) if (Math.abs(A[k][i]) > Math.abs(A[max][i])) max = k;
            var tmp = A[i]; A[i] = A[max]; A[max] = tmp;
            if (Math.abs(A[i][i]) < 1e-12) throw new Error('Titik sudut tidak valid (segaris).');
            for (k = i + 1; k < 8; k++) {
                var f = A[k][i] / A[i][i];
                for (j = i; j < 9; j++) A[k][j] -= f * A[i][j];
            }
        }
        var h = new Array(8);
        for (i = 7; i >= 0; i--) {
            var sum = A[i][8];
            for (j = i + 1; j < 8; j++) sum -= A[i][j] * h[j];
            h[i] = sum / A[i][i];
        }
        return h;
    }

    /** Koreksi perspektif dengan JavaScript murni (bilinear) — tanpa menunggu OpenCV. */
    function warpJs(src, c, outW, outH) {
        var sw = src.width, sh = src.height;
        var sdata = src.getContext('2d').getImageData(0, 0, sw, sh).data;
        var out = document.createElement('canvas');
        out.width = outW;
        out.height = outH;
        var octx = out.getContext('2d');
        var img = octx.createImageData(outW, outH), d = img.data;

        // Peta piksel hasil -> piksel sumber
        var h = homography(
            [{ x: 0, y: 0 }, { x: outW, y: 0 }, { x: outW, y: outH }, { x: 0, y: outH }],
            c
        );
        var maxX = sw - 1, maxY = sh - 1, o = 0;

        for (var y = 0; y < outH; y++) {
            var py = y + 0.5;
            var bx = h[1] * py + h[2], by = h[4] * py + h[5], bw = h[7] * py + 1;
            for (var x = 0; x < outW; x++) {
                var px = x + 0.5;
                var w = h[6] * px + bw;
                var sx = (h[0] * px + bx) / w - 0.5;
                var sy = (h[3] * px + by) / w - 0.5;
                if (sx < 0) sx = 0; else if (sx > maxX) sx = maxX;
                if (sy < 0) sy = 0; else if (sy > maxY) sy = maxY;

                var x0 = sx | 0, y0 = sy | 0;
                var x1 = x0 < maxX ? x0 + 1 : x0, y1 = y0 < maxY ? y0 + 1 : y0;
                var fx = sx - x0, fy = sy - y0;
                var i00 = (y0 * sw + x0) * 4, i10 = (y0 * sw + x1) * 4, i01 = (y1 * sw + x0) * 4, i11 = (y1 * sw + x1) * 4;
                var w00 = (1 - fx) * (1 - fy), w10 = fx * (1 - fy), w01 = (1 - fx) * fy, w11 = fx * fy;

                d[o]     = sdata[i00] * w00 + sdata[i10] * w10 + sdata[i01] * w01 + sdata[i11] * w11;
                d[o + 1] = sdata[i00 + 1] * w00 + sdata[i10 + 1] * w10 + sdata[i01 + 1] * w01 + sdata[i11 + 1] * w11;
                d[o + 2] = sdata[i00 + 2] * w00 + sdata[i10 + 2] * w10 + sdata[i01 + 2] * w01 + sdata[i11 + 2] * w11;
                d[o + 3] = 255;
                o += 4;
            }
        }
        octx.putImageData(img, 0, 0);
        return out;
    }

    function warp() {
        var c = orderCorners(state.corners), src = state.source; // rapikan bila titik diseret bersilangan
        var outW = Math.round(Math.max(dist(c[0], c[1]), dist(c[3], c[2])));
        var outH = Math.round(Math.max(dist(c[0], c[3]), dist(c[1], c[2])));
        var ratio = Math.min(1, OUTPUT_MAX_SIDE / Math.max(outW, outH));
        outW = Math.max(1, Math.round(outW * ratio));
        outH = Math.max(1, Math.round(outH * ratio));

        // Jalankan setelah overlay loading sempat tampil (pelurusan JS murni, ~puluhan ms).
        return new Promise(function (resolve) { setTimeout(resolve, 30); }).then(function () {
            return warpJs(src, c, outW, outH);
        });
    }

    // ---------- Alur modal ----------
    function open(input, file) {
        state.input = input;
        state.file = file;
        state.applied = false;
        state.corners = null;
        state.userMoved = false;
        state.session++;
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

    document.getElementById('docCropDetect').addEventListener('click', function () { if (state.source) runDetect(true); });
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
        var input = state.input;
        modal.hide();
        if (!input) return;
        input.value = ''; // tidak boleh upload tanpa crop
        // Beri tahu halaman bahwa pilihan file kosong (mis. sembunyikan badge "File siap")
        input._docCropDone = true;
        input.dispatchEvent(new Event('change', { bubbles: true }));
    });

    applyBtn.addEventListener('click', function () {
        if (!state.corners || !state.input) return;
        applyBtn.disabled = true;
        setLoading(true, 'Meluruskan dokumen...');

        warp().then(function (out) {
            return new Promise(function (resolve) { out.toBlob(resolve, 'image/jpeg', 0.88); });
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

            // data-doc-crop-submit: langsung unggah setelah crop (form dikirim otomatis,
            // termasuk validasi bawaan form & modal loading).
            if (input.hasAttribute('data-doc-crop-submit') && input.form) {
                setTimeout(function () {
                    if (input.form.requestSubmit) input.form.requestSubmit();
                    else input.form.submit();
                }, 150);
            }
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

    // ---------- Unduh OpenCV lebih awal ----------
    // Mulai unduh saat pelamar akan memilih file (fokus/klik input atau modal upload dibuka),
    // atau saat halaman idle bila input dokumen langsung terlihat (mis. langkah Dokumen).
    function preload() { if (!cvIsReady()) loadOpenCv().catch(function () {}); }
    var SELECTOR = 'input[type="file"][data-doc-crop]';

    document.addEventListener('pointerdown', function (e) { if (e.target.closest && e.target.closest(SELECTOR)) preload(); }, true);
    document.addEventListener('focusin', function (e) { if (e.target.matches && e.target.matches(SELECTOR)) preload(); }, true);
    document.addEventListener('show.bs.modal', function (e) { if (e.target.querySelector && e.target.querySelector(SELECTOR)) preload(); });

    window.addEventListener('load', function () {
        var visible = Array.prototype.some.call(document.querySelectorAll(SELECTOR), function (el) { return el.offsetParent !== null; });
        if (!visible) return;
        (window.requestIdleCallback || function (fn) { setTimeout(fn, 1500); })(preload);
    });
})();
</script>
@endpush
@endonce
