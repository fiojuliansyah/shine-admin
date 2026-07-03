@php $preview = request()->boolean('preview'); @endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Surat' }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html, body { background: #eceef4; font-family: Arial, sans-serif; }
        #controls {
            position: fixed; top: 0; left: 0; right: 0; z-index: 999;
            background: #fff; border-bottom: 1px solid #dee2e6;
            padding: 8px 16px; display: flex; align-items: center; gap: 12px;
        }
        #controls button {
            padding: 6px 18px; border-radius: 4px; border: none; cursor: pointer;
            font-size: 14px; font-weight: 600;
        }
        .btn-print { background: #0d6efd; color: #fff; }
        .btn-close { background: #6c757d; color: #fff; }
        #canvas-area {
            margin-top: 56px;
            padding: 24px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .page-wrapper {
            background: #fff;
            box-shadow: 0 2px 12px rgba(0,0,0,0.18);
            margin-bottom: 24px;
            overflow: hidden;
        }
        .page-inner { transform-origin: top left; }
        .html-page {
            background: #fff;
            box-shadow: 0 2px 12px rgba(0,0,0,0.18);
            margin-bottom: 24px;
            width: 794px;
            min-height: 1123px;
            padding: 60px 80px;
            font-family: Arial, sans-serif;
            font-size: 14px;
            line-height: 1.6;
            position: relative;
        }
        .html-page table { border-collapse: collapse; width: 100%; }
        .html-page td, .html-page th { border: 1px solid #ccc; padding: 6px 8px; }
        .html-page img { max-width: 100%; }
        .html-page .floating-box { position: absolute; box-sizing: border-box; padding: 8px; }
        .html-page .floating-box .fb-drag,
        .html-page .floating-box .fb-resize { display: none !important; }
        .html-page .floating-image { border: none !important; background: transparent !important; padding: 0 !important; }
        .html-page .floating-image img { display: block; width: 100%; height: auto; }
        @if($preview)
        html, body { background: #eceef4; }
        #controls { display: none !important; }
        #canvas-area { margin-top: 0; padding: 16px; }
        .html-page { transform-origin: top center; }
        @endif
        @media print {
            #controls { display: none !important; }
            html, body { background: #fff; }
            #canvas-area { margin-top: 0; padding: 0; }
            .page-wrapper { box-shadow: none; margin-bottom: 0; page-break-after: always; overflow: visible; }
            .page-wrapper:last-child { page-break-after: avoid; }
            .page-inner { transform: none !important; }
            .html-page { box-shadow: none; margin-bottom: 0; transform: none !important; }
        }
    </style>
</head>
<body>
@unless($preview)
<div id="controls">
    <span style="font-weight:700;color:#0d6efd;">{{ $title ?? 'Surat' }}</span>
    <button class="btn-print" onclick="window.print()">&#128438; Print / Save PDF</button>
    <button class="btn-close" onclick="window.close()">Tutup</button>
</div>
@endunless
<div id="canvas-area">
    @if(!$isFabric)
        @php
            $descHtml = preg_replace_callback(
                '/(data:image\/[a-zA-Z0-9.+-]+;base64,[A-Za-z0-9+\/=]+)/',
                function ($matches) {
                    return '<img src="' . $matches[1] . '" style="max-width:150px;max-height:80px;object-fit:contain;">';
                },
                $description ?? ''
            );
        @endphp
        <div class="html-page" id="htmlPage">{!! $descHtml !!}</div>
    @else
    @foreach($pages as $i => $page)
        <div class="page-wrapper" style="width:794px;height:1123px;">
            <div class="page-inner">
                <canvas id="printCanvas-{{ $i }}" width="794" height="1123"></canvas>
            </div>
        </div>
    @endforeach
    @endif
</div>
@unless($isFabric)
<script>
    (function () {
        const PREVIEW_MODE = {{ $preview ? 'true' : 'false' }};
        if (!PREVIEW_MODE) return;
        function fitHtml() {
            const area = document.getElementById('canvas-area');
            const page = document.getElementById('htmlPage');
            if (!area || !page) return;
            const style = getComputedStyle(area);
            const avail = area.clientWidth
                - parseFloat(style.paddingLeft) - parseFloat(style.paddingRight);
            const scale = Math.min(1, avail / 794);
            page.style.transform = 'scale(' + scale + ')';
            page.style.marginBottom = (24 - page.offsetHeight * (1 - scale)) + 'px';
        }
        window.addEventListener('resize', fitHtml);
        window.addEventListener('load', fitHtml);
        fitHtml();
    })();
</script>
@else
<script src="/admin/assets/js/fabric-5.5.2.min.js"></script>
<script>
    // Samakan rendering dengan editor: patch enlargeSpaces agar teks rata
    // kanan-kiri (justify) memiliki spasi yang sama persis seperti di editor.
    if (typeof fabric !== 'undefined') {
        fabric.devicePixelRatio = window.devicePixelRatio || 1;
        if (fabric.Text && fabric.Text.prototype) {
            fabric.Text.prototype.enlargeSpaces = function () {
                for (var i, e, l, k, n, t, b, a = 0, c = this._textLines.length; a < c; a++) {
                    if (a === c - 1 || this.isEndOfWrapping(a)) continue;
                    k = 0;
                    n = this._textLines[a];
                    e = this.getLineWidth(a);
                    if (e < this.width && (t = this.textLines[a].match(this._reSpacesAndTabs))) {
                        i = t.length;
                        l = (this.width - e) / i;
                        for (var h = 0, m = n.length; h <= m; h++) {
                            b = this.__charBounds[a][h];
                            if (this._reSpaceAndTab.test(n[h])) {
                                b.width += l;
                                b.kernedWidth += l;
                                b.left += k;
                                k += l;
                            } else {
                                b.left += k;
                            }
                        }
                    }
                }
            };
        }
    }

    const pages = @json($pages);
    const PREVIEW_MODE = {{ $preview ? 'true' : 'false' }};
    const PAGE_W = 794, PAGE_H = 1123;

    // Pada mode preview (di dalam iframe halaman detail), lebar canvas tetap
    // 794px bisa melebihi lebar iframe. Skalakan tiap halaman agar pas tanpa
    // mengubah tata letak objek (persis seperti di editor).
    function fitPreview() {
        if (!PREVIEW_MODE) return;
        const area = document.getElementById('canvas-area');
        const style = getComputedStyle(area);
        const avail = area.clientWidth
            - parseFloat(style.paddingLeft) - parseFloat(style.paddingRight);
        const scale = Math.min(1, avail / PAGE_W);
        document.querySelectorAll('.page-wrapper').forEach(function (wrap) {
            wrap.style.width = (PAGE_W * scale) + 'px';
            wrap.style.height = (PAGE_H * scale) + 'px';
            const inner = wrap.querySelector('.page-inner');
            inner.style.transform = 'scale(' + scale + ')';
        });
    }
    window.addEventListener('resize', fitPreview);

    // Setelah teks variabel diganti dengan nilai sebenarnya (mis. nama project /
    // jabatan yang panjang), sebuah textbox bisa membesar ke beberapa baris dan
    // menimpa objek di bawahnya. Fungsi ini mendorong objek yang tumpang tindih
    // ke bawah secara otomatis sehingga tata letak tetap rapi.
    function reflowOverlaps(fc) {
        const GAP = 4;
        let objects = fc.getObjects().filter(function (o) {
            return o.visible !== false && !o.excludeFromExport;
        });

        // Urutkan dari atas ke bawah.
        objects.sort(function (a, b) { return a.top - b.top; });

        const horizontallyOverlap = function (a, b) {
            const aL = a.left;
            const aR = a.left + a.getScaledWidth();
            const bL = b.left;
            const bR = b.left + b.getScaledWidth();
            // beri sedikit toleransi agar kolom yang benar-benar terpisah tidak ikut tergeser
            return aL < bR - 2 && bL < aR - 2;
        };

        for (let i = 0; i < objects.length; i++) {
            const upper = objects[i];
            const upperBottom = upper.top + upper.getScaledHeight();
            for (let j = i + 1; j < objects.length; j++) {
                const lower = objects[j];
                if (lower.top >= upperBottom) continue;
                if (!horizontallyOverlap(upper, lower)) continue;
                const newTop = upperBottom + GAP;
                if (newTop > lower.top) {
                    lower.set({ top: newTop });
                    lower.setCoords();
                }
            }
            // re-sort sisanya karena posisi berubah
            objects.sort(function (a, b) { return a.top - b.top; });
        }
        fc.renderAll();
    }

    pages.forEach(function(pageData, i) {
        const fc = new fabric.Canvas('printCanvas-' + i, {
            width: PAGE_W,
            height: PAGE_H,
            backgroundColor: '#fff',
            selection: false,
            enableRetinaScaling: true,
            imageSmoothingEnabled: true,
        });
        fc.loadFromJSON(pageData.canvasJSON, function() {
            fc.getObjects().forEach(function(obj) {
                obj.set({ selectable: false, evented: false, hoverCursor: 'default' });
            });
            if (!PREVIEW_MODE) reflowOverlaps(fc);
            fc.renderAll();
            fitPreview();
        });
    });
</script>
@endunless
</body>
</html>
