(function () {
    'use strict';

    var TEMPLATE_STORAGE_KEY = 'ktp_polygon_template_v1';

    var FIELDS = [
        { key: 'nik', label: 'NIK', type: 'digits' },
        { key: 'nama', label: 'Nama', type: 'text' },
        { key: 'ttl', label: 'Tempat/Tgl Lahir', type: 'text' },
        { key: 'jenis_kelamin', label: 'Jenis Kelamin', type: 'text' },
        { key: 'alamat', label: 'Alamat', type: 'text' },
        { key: 'rt_rw', label: 'RT/RW', type: 'text' },
        { key: 'kelurahan', label: 'Kel/Desa', type: 'text' },
        { key: 'kecamatan', label: 'Kecamatan', type: 'text' }
    ];

    var DEFAULT_TEMPLATE = {
        nik: [[0.305, 0.205], [0.96, 0.205], [0.96, 0.30], [0.305, 0.30]],
        nama: [[0.305, 0.305], [0.96, 0.305], [0.96, 0.375], [0.305, 0.375]],
        ttl: [[0.305, 0.38], [0.96, 0.38], [0.96, 0.45], [0.305, 0.45]],
        jenis_kelamin: [[0.305, 0.455], [0.62, 0.455], [0.62, 0.52], [0.305, 0.52]],
        alamat: [[0.305, 0.525], [0.96, 0.525], [0.96, 0.60], [0.305, 0.60]],
        rt_rw: [[0.42, 0.605], [0.70, 0.605], [0.70, 0.665], [0.42, 0.665]],
        kelurahan: [[0.42, 0.67], [0.96, 0.67], [0.96, 0.73], [0.42, 0.73]],
        kecamatan: [[0.42, 0.735], [0.96, 0.735], [0.96, 0.80], [0.42, 0.80]]
    };

    function clone(obj) {
        return JSON.parse(JSON.stringify(obj));
    }

    function loadTemplate() {
        try {
            var raw = localStorage.getItem(TEMPLATE_STORAGE_KEY);
            if (raw) {
                var parsed = JSON.parse(raw);
                var ok = FIELDS.every(function (f) {
                    return Array.isArray(parsed[f.key]) && parsed[f.key].length >= 3;
                });
                if (ok) return parsed;
            }
        } catch (e) { /* ignore */ }
        return clone(DEFAULT_TEMPLATE);
    }

    function saveTemplate(tpl) {
        localStorage.setItem(TEMPLATE_STORAGE_KEY, JSON.stringify(tpl));
    }

    function polygonBounds(points, w, h) {
        var minX = Infinity, minY = Infinity, maxX = -Infinity, maxY = -Infinity;
        points.forEach(function (p) {
            var x = p[0] * w, y = p[1] * h;
            if (x < minX) minX = x;
            if (y < minY) minY = y;
            if (x > maxX) maxX = x;
            if (y > maxY) maxY = y;
        });
        return {
            x: Math.max(0, Math.floor(minX)),
            y: Math.max(0, Math.floor(minY)),
            w: Math.ceil(maxX - minX),
            h: Math.ceil(maxY - minY)
        };
    }

    function cropPolygon(sourceCanvas, points) {
        var w = sourceCanvas.width;
        var h = sourceCanvas.height;
        var b = polygonBounds(points, w, h);
        if (b.w < 2 || b.h < 2) return null;

        var out = document.createElement('canvas');
        out.width = b.w;
        out.height = b.h;
        var ctx = out.getContext('2d');

        ctx.save();
        ctx.beginPath();
        points.forEach(function (p, i) {
            var x = p[0] * w - b.x;
            var y = p[1] * h - b.y;
            if (i === 0) ctx.moveTo(x, y);
            else ctx.lineTo(x, y);
        });
        ctx.closePath();
        ctx.clip();
        ctx.drawImage(sourceCanvas, -b.x, -b.y);
        ctx.restore();

        return out;
    }

    function preprocess(canvas) {
        var ctx = canvas.getContext('2d');
        var img = ctx.getImageData(0, 0, canvas.width, canvas.height);
        var d = img.data;
        var scale = 2;
        for (var i = 0; i < d.length; i += 4) {
            var gray = 0.299 * d[i] + 0.587 * d[i + 1] + 0.114 * d[i + 2];
            gray = (gray - 128) * 1.4 + 128;
            var v = gray > 150 ? 255 : (gray < 90 ? 0 : gray);
            d[i] = d[i + 1] = d[i + 2] = v;
        }
        ctx.putImageData(img, 0, 0);

        var up = document.createElement('canvas');
        up.width = canvas.width * scale;
        up.height = canvas.height * scale;
        var uctx = up.getContext('2d');
        uctx.imageSmoothingEnabled = true;
        uctx.drawImage(canvas, 0, 0, up.width, up.height);
        return up;
    }

    var TESSERACT_CDN = 'https://cdn.jsdelivr.net/npm/tesseract.js@5/dist/tesseract.min.js';
    var tesseractLoading = null;

    function ensureTesseract() {
        if (window.Tesseract) return Promise.resolve(window.Tesseract);
        if (tesseractLoading) return tesseractLoading;
        tesseractLoading = new Promise(function (resolve, reject) {
            var s = document.createElement('script');
            s.src = TESSERACT_CDN;
            s.onload = function () {
                if (window.Tesseract) resolve(window.Tesseract);
                else reject(new Error('Tesseract gagal dimuat'));
            };
            s.onerror = function () { reject(new Error('Gagal memuat tesseract.js')); };
            document.head.appendChild(s);
        });
        return tesseractLoading;
    }

    var workerPromise = null;

    function getWorker(onProgress) {
        if (workerPromise) return workerPromise;
        workerPromise = ensureTesseract().then(function (T) {
            return T.createWorker('ind', 1, {
                logger: function (m) {
                    if (m.status === 'recognizing text' && typeof onProgress === 'function') {
                        onProgress(m.progress);
                    }
                }
            });
        });
        return workerPromise;
    }

    function cleanLine(s) {
        return (s || '').replace(/\s+/g, ' ').trim();
    }

    function parseField(field, raw) {
        var text = cleanLine(raw);
        if (field.type === 'digits') {
            return text.replace(/\D/g, '');
        }
        return text.replace(/^[:.\-\s]+/, '').trim();
    }

    function splitTtl(value) {
        var out = { birth_place: '', birth_date: '' };
        if (!value) return out;
        var m = value.match(/(\d{2}[-\/.]\d{2}[-\/.]\d{4})/);
        if (m) {
            var parts = m[1].replace(/[.\/]/g, '-').split('-');
            out.birth_date = parts[2] + '-' + parts[1] + '-' + parts[0];
            out.birth_place = value.slice(0, m.index).replace(/[,:\s]+$/, '').trim();
        } else {
            out.birth_place = value.replace(/,\s*$/, '').trim();
        }
        return out;
    }

    function normalizeGender(value) {
        var v = (value || '').toUpperCase();
        if (v.indexOf('PEREMPUAN') !== -1 || v.indexOf('WANITA') !== -1) return 'Perempuan';
        if (v.indexOf('LAKI') !== -1 || v.indexOf('PRIA') !== -1) return 'Laki-Laki';
        return '';
    }

    function mapToProfile(results) {
        var ttl = splitTtl(results.ttl);
        return {
            raw: results,
            mapped: {
                nik: results.nik || '',
                name: results.nama || '',
                gender: normalizeGender(results.jenis_kelamin),
                birth_place: ttl.birth_place,
                birth_date: ttl.birth_date,
                address: results.alamat || '',
                rt_rw: (results.rt_rw || '').replace(/[^0-9\/]/g, ''),
                kelurahan: results.kelurahan || '',
                kecamatan: results.kecamatan || ''
            }
        };
    }

    function scanWithTemplate(sourceCanvas, template, onProgress) {
        return getWorker(function (p) {
            if (onProgress) onProgress('ocr', p);
        }).then(function (worker) {
            var results = {};
            var total = FIELDS.length;
            var chain = Promise.resolve();

            FIELDS.forEach(function (field, idx) {
                chain = chain.then(function () {
                    var poly = template[field.key];
                    if (!poly) { results[field.key] = ''; return; }
                    var crop = cropPolygon(sourceCanvas, poly);
                    if (!crop) { results[field.key] = ''; return; }
                    var prepped = preprocess(crop);

                    var opts = {
                        tessedit_char_whitelist: field.type === 'digits' ? '0123456789' : ''
                    };

                    return worker.setParameters(opts).then(function () {
                        return worker.recognize(prepped);
                    }).then(function (res) {
                        results[field.key] = parseField(field, res.data.text);
                        if (onProgress) onProgress('field', (idx + 1) / total, field.label);
                    });
                });
            });

            return chain.then(function () {
                return mapToProfile(results);
            });
        });
    }

    var FIELD_COLORS = {
        nik: '#e74c3c', nama: '#27ae60', ttl: '#2980b9', jenis_kelamin: '#8e44ad',
        alamat: '#d35400', rt_rw: '#16a085', kelurahan: '#c0392b', kecamatan: '#2c3e50'
    };

    function PolygonEditor(canvas, options) {
        this.canvas = canvas;
        this.ctx = canvas.getContext('2d');
        this.image = null;
        this.template = options && options.template ? clone(options.template) : loadTemplate();
        this.activeField = FIELDS[0].key;
        this.dragPoint = null;
        this.dragPoly = null;
        this.dragOffset = null;
        this.bind();
    }

    PolygonEditor.prototype.setImage = function (img) {
        this.image = img;
        this.resize();
        this.draw();
    };

    PolygonEditor.prototype.setActiveField = function (key) {
        this.activeField = key;
        this.draw();
    };

    PolygonEditor.prototype.resize = function () {
        if (!this.image) return;
        var maxW = this.canvas.parentElement.clientWidth || 600;
        var ratio = this.image.height / this.image.width;
        this.canvas.width = maxW;
        this.canvas.height = Math.round(maxW * ratio);
    };

    PolygonEditor.prototype.toPx = function (p) {
        return [p[0] * this.canvas.width, p[1] * this.canvas.height];
    };

    PolygonEditor.prototype.toNorm = function (x, y) {
        return [
            Math.min(1, Math.max(0, x / this.canvas.width)),
            Math.min(1, Math.max(0, y / this.canvas.height))
        ];
    };

    PolygonEditor.prototype.draw = function () {
        var ctx = this.ctx;
        ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);
        if (this.image) ctx.drawImage(this.image, 0, 0, this.canvas.width, this.canvas.height);

        var self = this;
        FIELDS.forEach(function (field) {
            var poly = self.template[field.key];
            if (!poly) return;
            var isActive = field.key === self.activeField;
            var color = FIELD_COLORS[field.key] || '#3498db';

            ctx.beginPath();
            poly.forEach(function (p, i) {
                var px = self.toPx(p);
                if (i === 0) ctx.moveTo(px[0], px[1]);
                else ctx.lineTo(px[0], px[1]);
            });
            ctx.closePath();
            ctx.lineWidth = isActive ? 3 : 1.5;
            ctx.strokeStyle = color;
            ctx.fillStyle = color + (isActive ? '33' : '14');
            ctx.fill();
            ctx.stroke();

            var first = self.toPx(poly[0]);
            ctx.fillStyle = color;
            ctx.font = '12px sans-serif';
            ctx.fillText(field.label, first[0] + 4, first[1] - 4);

            if (isActive) {
                poly.forEach(function (p) {
                    var px = self.toPx(p);
                    ctx.beginPath();
                    ctx.arc(px[0], px[1], 5, 0, Math.PI * 2);
                    ctx.fillStyle = '#fff';
                    ctx.fill();
                    ctx.lineWidth = 2;
                    ctx.strokeStyle = color;
                    ctx.stroke();
                });
            }
        });
    };

    PolygonEditor.prototype.eventPos = function (e) {
        var rect = this.canvas.getBoundingClientRect();
        var src = e.touches && e.touches[0] ? e.touches[0] : e;
        return {
            x: (src.clientX - rect.left) * (this.canvas.width / rect.width),
            y: (src.clientY - rect.top) * (this.canvas.height / rect.height)
        };
    };

    PolygonEditor.prototype.pointInPoly = function (x, y, poly) {
        var inside = false;
        for (var i = 0, j = poly.length - 1; i < poly.length; j = i++) {
            var pi = this.toPx(poly[i]), pj = this.toPx(poly[j]);
            var intersect = ((pi[1] > y) !== (pj[1] > y)) &&
                (x < (pj[0] - pi[0]) * (y - pi[1]) / (pj[1] - pi[1]) + pi[0]);
            if (intersect) inside = !inside;
        }
        return inside;
    };

    PolygonEditor.prototype.onDown = function (e) {
        if (!this.image) return;
        e.preventDefault();
        var pos = this.eventPos(e);
        var poly = this.template[this.activeField];
        if (!poly) return;

        for (var i = 0; i < poly.length; i++) {
            var px = this.toPx(poly[i]);
            if (Math.hypot(px[0] - pos.x, px[1] - pos.y) <= 9) {
                this.dragPoint = i;
                return;
            }
        }
        if (this.pointInPoly(pos.x, pos.y, poly)) {
            this.dragPoly = true;
            this.dragOffset = pos;
        }
    };

    PolygonEditor.prototype.onMove = function (e) {
        if (this.dragPoint === null && !this.dragPoly) return;
        e.preventDefault();
        var pos = this.eventPos(e);
        var poly = this.template[this.activeField];

        if (this.dragPoint !== null) {
            poly[this.dragPoint] = this.toNorm(pos.x, pos.y);
        } else if (this.dragPoly) {
            var dx = (pos.x - this.dragOffset.x) / this.canvas.width;
            var dy = (pos.y - this.dragOffset.y) / this.canvas.height;
            poly.forEach(function (p) {
                p[0] = Math.min(1, Math.max(0, p[0] + dx));
                p[1] = Math.min(1, Math.max(0, p[1] + dy));
            });
            this.dragOffset = pos;
        }
        this.draw();
    };

    PolygonEditor.prototype.onUp = function () {
        this.dragPoint = null;
        this.dragPoly = false;
        this.dragOffset = null;
    };

    PolygonEditor.prototype.bind = function () {
        var self = this;
        ['mousedown', 'touchstart'].forEach(function (ev) {
            self.canvas.addEventListener(ev, function (e) { self.onDown(e); }, { passive: false });
        });
        ['mousemove', 'touchmove'].forEach(function (ev) {
            self.canvas.addEventListener(ev, function (e) { self.onMove(e); }, { passive: false });
        });
        ['mouseup', 'touchend', 'mouseleave'].forEach(function (ev) {
            self.canvas.addEventListener(ev, function () { self.onUp(); });
        });
    };

    PolygonEditor.prototype.getTemplate = function () { return clone(this.template); };
    PolygonEditor.prototype.resetTemplate = function () {
        this.template = clone(DEFAULT_TEMPLATE);
        this.draw();
    };

    function loadImageToCanvas(file) {
        return new Promise(function (resolve, reject) {
            var reader = new FileReader();
            reader.onload = function () {
                var img = new Image();
                img.onload = function () {
                    var canvas = document.createElement('canvas');
                    var maxDim = 1600;
                    var scale = Math.min(1, maxDim / Math.max(img.width, img.height));
                    canvas.width = Math.round(img.width * scale);
                    canvas.height = Math.round(img.height * scale);
                    canvas.getContext('2d').drawImage(img, 0, 0, canvas.width, canvas.height);
                    resolve({ canvas: canvas, image: img });
                };
                img.onerror = function () { reject(new Error('Gagal memuat gambar')); };
                img.src = reader.result;
            };
            reader.onerror = function () { reject(new Error('Gagal membaca file')); };
            reader.readAsDataURL(file);
        });
    }

    window.KtpOcr = {
        FIELDS: FIELDS,
        FIELD_COLORS: FIELD_COLORS,
        DEFAULT_TEMPLATE: DEFAULT_TEMPLATE,
        loadTemplate: loadTemplate,
        saveTemplate: saveTemplate,
        loadImageToCanvas: loadImageToCanvas,
        scan: scanWithTemplate,
        PolygonEditor: PolygonEditor
    };
})();


