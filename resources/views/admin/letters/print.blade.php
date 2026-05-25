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
        }
        @media print {
            #controls { display: none !important; }
            html, body { background: #fff; }
            #canvas-area { margin-top: 0; padding: 0; }
            .page-wrapper { box-shadow: none; margin-bottom: 0; page-break-after: always; }
            .page-wrapper:last-child { page-break-after: avoid; }
        }
    </style>
</head>
<body>
<div id="controls">
    <span style="font-weight:700;color:#0d6efd;">{{ $title ?? 'Surat' }}</span>
    <button class="btn-print" onclick="window.print()">&#128438; Print / Save PDF</button>
    <button class="btn-close" onclick="window.close()">Tutup</button>
</div>
<div id="canvas-area">
    @foreach($pages as $i => $page)
        <div class="page-wrapper">
            <canvas id="printCanvas-{{ $i }}" width="794" height="1123"></canvas>
        </div>
    @endforeach
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/fabric.js/5.3.1/fabric.min.js"></script>
<script>
    const pages = @json($pages);

    pages.forEach(function(pageData, i) {
        const fc = new fabric.Canvas('printCanvas-' + i, {
            width: 794,
            height: 1123,
            backgroundColor: '#fff',
            selection: false,
        });
        fc.loadFromJSON(pageData.canvasJSON, function() {
            fc.getObjects().forEach(function(obj) {
                obj.set({ selectable: false, evented: false, hoverCursor: 'default' });
            });
            fc.renderAll();
        });
    });
</script>
</body>
</html>
