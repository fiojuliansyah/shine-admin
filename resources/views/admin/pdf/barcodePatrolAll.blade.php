<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Semua Barcode Patroli</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 15mm;
        }

        body {
            font-family: sans-serif;
        }

        /* Wrapper setiap halaman */
        .page {
            width: 100%;
            height: 100%;
            page-break-after: always;
            display: table;
            table-layout: fixed;
        }

        .row {
            display: table-row;
        }

        .floor-item {
            display: table-cell;
            text-align: center;
            vertical-align: middle;
            width: 33.33%;
            padding: 5px;
        }

        .item-box {
            border: 1px dashed #999;
            padding: 5px;
        }

        .item-box img[alt="Logo"] {
            width: 40px;
            height: auto;
            margin-top: 2px;
        }

        .company-header {
            font-size: 9px;
            font-weight: bold;
            margin: 2px 0 5px 0;
        }

        .floor-name {
            font-size: 13px;
            font-weight: bold;
            margin-top: 5px;
            white-space: nowrap;
        }

        .floor-description {
            font-size: 10px;
            margin-top: 5px;
            white-space: nowrap;
        }

        .barcode-svg img {
            width: 150px;
            height: 150px;
            margin: 5px auto;
            display: block;
        }
    </style>
</head>
<body>

@php
    $chunks = array_chunk($dataFloors, 6); // 6 item per page
@endphp

@foreach($chunks as $chunk)
    <div class="page">
        @for($r=0; $r<2; $r++) <!-- 2 baris -->
            <div class="row">
                @for($c=0; $c<3; $c++) <!-- 3 kolom -->
                    @php
                        $index = $r*3 + $c;
                        $item = $chunk[$index] ?? null;
                    @endphp
                    @if($item)
                        <div class="floor-item">
                            <div class="item-box">
                                <img src="{{ $logoBase64 }}" alt="Logo">
                                <div class="company-header">KARYAX</div>
                                <div class="barcode-svg">
                                    <img src="{{ $item['barcodeSvgUri'] }}">
                                </div>
                                <div class="floor-name">{{ $item['name'] ?? 'Lantai Tanpa Nama' }}</div>
                                <div class="floor-description">{{ $item['description'] ?? 'Lantai Tanpa Nama' }}</div>
                            </div>
                        </div>
                    @else
                        <div class="floor-item"></div> <!-- Kosong jika kurang dari 6 item -->
                    @endif
                @endfor
            </div>
        @endfor
    </div>
@endforeach

</body>
</html>
