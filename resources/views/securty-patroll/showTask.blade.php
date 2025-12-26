@extends('layouts.main')

@section('content')
    <div class="page-wrapper">
        <div class="content">
            <!-- Breadcrumb -->
            <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
                <div class="my-auto mb-2">
                    <h2 class="mb-1">Securty Patroll Task Planner</h2>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href=""><i class="ti ti-smart-home"></i></a>
                            </li>
                            <li class="breadcrumb-item">
                                Productivity
                            </li>
                            <li class="breadcrumb-item" aria-current="page">Securty Patroll</li>
                            {{-- <li class="breadcrumb-item active" aria-current="page">{{ $site->name }}</li> --}}
                            <li class="breadcrumb-item" aria-current="page">Securty Patroll Task Planner</li>
                        </ol>
                    </nav>
                </div>
            </div>

            <div class="row">
                {{-- qr --}}
                <div class="col-12 col-md-4">
                    <div class="card border-0 p-3">
                        <h3 class="text-center">QR Code Floor</h2>
                            {{-- qr --}}
                            <div id="PrintQR{{ $floor->id }}" class="mt-2 mx-auto">
                                {!! $floor->floor_qr !!}
                                <p class="text-center mt-1 printable">{{ $floor->name }}</p>
                            </div>


                            <div class="mt-3 gap-3 d-flex justify-content-center">
                                <div>
                                    <button class="btn btn-primary" onclick="printQR({{ $floor->id }})">
                                        <i class="ti ti-printer"></i> Cetak QR
                                    </button>
                                </div>
                                <div>
                                    <button class="btn btn-success"
                                        onclick="downloadQRasPNG({{ $floor->id }}, '{{ $floor->name }}')">
                                        <i class="fa-solid fa-download"></i> Download QR
                                    </button>

                                </div>
                            </div>

                    </div>
                </div>
                {{-- list task --}}
                <div class="col-12 col-md-8 theiaStickySidebar">
                    <div class="card border-0 p-3">
                        <h3>List Task Planner</h2>
                            {{-- list task item --}}
                            <div class="mt-3">
                                <div class="mb-2">
                                    <span class="mb-2">Hari ini</span>
                                    @foreach ($tasks as $task)
                                        <div class="rounded shadow-md p-2 mb-2" style="background-color: #f8f9fa">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <h3 class="text-primary" style="font-size:18px">{{ $task->name }}
                                                </h3>
                                            </div>

                                            <div class="mt-2 d-flex align-items-center gap-2">
                                                <small>
                                                    <i class="me-1 ti ti-clock" style="font-size: 15px"></i>
                                                    {{ $task->start_time }}
                                                </small>
                                                <small>
                                                    <i class="me-1 ti ti-stairs" style="font-size: 15px"></i>
                                                    {{ $task->floor->name }}
                                                </small>
                                                <small class="ms-auto badge bg-primary text-white">
                                                    <i class="me-1 ti ti-calendar" style="font-size: 15px"></i>
                                                    {{ $task->date }}
                                                </small>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <div class="mt-3 d-flex justify-content-end">
                                    {{-- {{ $tasks->links() }} --}}
                                </div>
                            </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script src="/admin/assets/js/tasksPlanner.js"></script>

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
@endpush
