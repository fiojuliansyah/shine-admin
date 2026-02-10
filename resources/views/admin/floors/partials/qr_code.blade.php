<button class="btn btn-sm btn-primary rounded-pill" data-bs-toggle="modal" data-bs-target="#qrCodeModal-{{ $row->id }}">
    Show QR
</button>

<!-- Modal -->
<div class="modal fade" id="qrCodeModal-{{ $row->id }}" tabindex="-1" aria-labelledby="qrCodeModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title text-center" id="qrCodeModalLabel">{{ $row->name }}</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex justify-content-center">
                    <div id="PrintQR{{ $row->id }}" >
                        {!! $row->floor_qr !!}
                        <p class="text-center mt-1 printable">{{ $row->name }}</p>
                    </div>
                </div>

                <div class="mt-3 gap-3 d-flex justify-content-center">
                    <div>
                        <button class="btn btn-primary" onclick="printQR({{ $row->id }})">
                            <i class="ti ti-printer"></i> Cetak QR
                        </button>
                    </div>
                    <div>
                        <button class="btn btn-success"
                            onclick="downloadQRasPNG({{ $row->id }}, '{{ $row->name }}')">
                            <i class="fa-solid fa-download"></i> Download QR
                        </button>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function downloadQRasPNG(floorId, floorName) {
        const qrDiv = document.getElementById('PrintQR' + floorId);
        if (!qrDiv) return;

        // cari SVG
        const svg = qrDiv.querySelector('svg');
        if (!svg) {
            alert("SVG QR tidak ditemukan");
            return;
        }

        // Ambil SVG sebagai string
        const svgData = new XMLSerializer().serializeToString(svg);
        const svgBlob = new Blob([svgData], {
            type: 'image/svg+xml;charset=utf-8'
        });
        const DOMURL = window.URL || window.webkitURL || window;

        const url = DOMURL.createObjectURL(svgBlob);

        const img = new Image();
        img.onload = function() {
            // ambil ukuran asli QR
            const qrWidth = svg.viewBox.baseVal.width || svg.width.baseVal.value;
            const qrHeight = svg.viewBox.baseVal.height || svg.height.baseVal.value;

            // padding putih di setiap sisi
            const padding = 50;

            // Buat canvas lebih besar
            const canvas = document.createElement('canvas');
            canvas.width = qrWidth + padding * 2;
            canvas.height = qrHeight + padding * 2;

            const ctx = canvas.getContext('2d');

            // background putih
            ctx.fillStyle = '#ffffff';
            ctx.fillRect(0, 0, canvas.width, canvas.height);

            // gambar QR di tengah
            ctx.drawImage(img, padding, padding, qrWidth, qrHeight);

            // Buat file PNG
            canvas.toBlob(function(blob) {
                const pngUrl = DOMURL.createObjectURL(blob);

                const a = document.createElement('a');
                a.download = (floorName || 'qrcode') + '.png';
                a.href = pngUrl;
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);

                DOMURL.revokeObjectURL(pngUrl);
            }, 'image/png');

            DOMURL.revokeObjectURL(url);
        };
        img.src = url;
    }

    function printQR(floorId) {
        var qrDiv = document.getElementById('PrintQR' + floorId);
        if (!qrDiv) return;

        // Buat iframe tersembunyi
        var iframe = document.createElement('iframe');
        iframe.style.position = 'absolute';
        iframe.style.width = '0px';
        iframe.style.height = '0px';
        iframe.style.border = '0';
        document.body.appendChild(iframe);

        var doc = iframe.contentWindow.document;
        doc.open();
        doc.write(`
        <html>
            <head>
                <title>Print QR</title>
                <style>
                    body { text-align: center; font-family: Arial, sans-serif; margin: 20px; }
                    .printable { font-size: 18px; margin-top: 10px; }
                </style>
            </head>
            <body>
                ${qrDiv.innerHTML}
            </body>
        </html>
    `);
        doc.close();

        // Cetak iframe
        iframe.contentWindow.focus();
        iframe.contentWindow.print();

        // Hapus iframe setelah cetak
        setTimeout(() => {
            document.body.removeChild(iframe);
        }, 500);
    }
</script>
