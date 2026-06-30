<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $title ?? ($letter->title ?? 'Surat') }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        html, body { width: 794px; background: #fff; }
        .page {
            position: relative;
            width: 794px;
            height: 1123px;
            overflow: hidden;
            background: #fff;
            page-break-after: always;
        }
        .page:last-child { page-break-after: avoid; }
        .fabric-object {
            position: absolute;
            white-space: pre-wrap;
            word-break: break-word;
        }
        .html-content {
            padding: 60px 80px;
            line-height: 1.6;
            font-family: Arial, sans-serif;
            font-size: 14px;
        }
        .html-content table { width: 100%; border-collapse: collapse; }
        .html-content td, .html-content th { border: 1px solid #ccc; padding: 6px 8px; }
    </style>
</head>
<body>

@if($isFabric && count($pages) > 0)
    @foreach($pages as $pageIndex => $page)
        @php
            $pageImagePath = $page['pageImagePath'] ?? null;
            $objects = $page['canvasJSON']['objects'] ?? [];

            // Reflow: setelah variabel diganti nilai sebenarnya (mis. nama project /
            // jabatan yang panjang), teks bisa membungkus ke beberapa baris dan
            // menimpa objek di bawahnya. Kita estimasi tinggi teks lalu dorong
            // objek yang tumpang tindih ke bawah agar tidak bertabrakan.
            if (!($pageImagePath && file_exists($pageImagePath)) && is_array($objects) && count($objects)) {
                $estimateHeight = function ($obj) {
                    $scaleY     = $obj['scaleY'] ?? 1;
                    $scaleX     = $obj['scaleX'] ?? 1;
                    $type       = $obj['type'] ?? '';
                    if (!in_array($type, ['textbox', 'i-text', 'text'])) {
                        return ($obj['height'] ?? 0) * $scaleY;
                    }
                    $text       = $obj['text'] ?? '';
                    if (str_starts_with($text, 'data:image/')) {
                        return ($obj['height'] ?? 0) * $scaleY;
                    }
                    $fontSize   = $obj['fontSize'] ?? 14;
                    $lineHeight = $obj['lineHeight'] ?? 1.16;
                    $boxWidth   = max(1, ($obj['width'] ?? 200) * $scaleX);
                    // perkiraan lebar rata-rata karakter ~ 0.5 * fontSize
                    $charW      = max(1, $fontSize * 0.5);
                    $perLine    = max(1, (int) floor($boxWidth / $charW));
                    $lines      = 0;
                    foreach (preg_split('/\r\n|\r|\n/', $text) as $paragraph) {
                        $len = max(1, mb_strlen($paragraph));
                        $lines += (int) ceil($len / $perLine);
                    }
                    $lines = max(1, $lines);
                    return $lines * $fontSize * $lineHeight * $scaleY;
                };

                // urutkan referensi berdasar posisi atas
                usort($objects, function ($a, $b) {
                    return ($a['top'] ?? 0) <=> ($b['top'] ?? 0);
                });

                $gap = 4;
                $count = count($objects);
                for ($i = 0; $i < $count; $i++) {
                    $upper       = $objects[$i];
                    $upperTop    = $upper['top'] ?? 0;
                    $upperLeft   = $upper['left'] ?? 0;
                    $upperRight  = $upperLeft + (($upper['width'] ?? 200) * ($upper['scaleX'] ?? 1));
                    $upperBottom = $upperTop + $estimateHeight($upper);

                    for ($j = $i + 1; $j < $count; $j++) {
                        $lowerTop   = $objects[$j]['top'] ?? 0;
                        $lowerLeft  = $objects[$j]['left'] ?? 0;
                        $lowerRight = $lowerLeft + (($objects[$j]['width'] ?? 200) * ($objects[$j]['scaleX'] ?? 1));

                        // hanya geser bila benar-benar bertumpuk secara horizontal
                        $overlapX = $lowerLeft < $upperRight - 2 && $upperLeft < $lowerRight - 2;
                        if (!$overlapX) continue;

                        if ($lowerTop < $upperBottom) {
                            $objects[$j]['top'] = $upperBottom + $gap;
                        }
                    }

                    usort($objects, function ($a, $b) {
                        return ($a['top'] ?? 0) <=> ($b['top'] ?? 0);
                    });
                }
            }
        @endphp
        <div class="page">
            @if($pageImagePath && file_exists($pageImagePath))
                <img src="{{ $pageImagePath }}" style="position:absolute;top:0;left:0;width:794px;height:1123px;" alt="">
            @else
                @foreach($objects as $obj)
                    @php
                        $type      = $obj['type'] ?? '';
                        $left      = $obj['left'] ?? 0;
                        $top       = $obj['top'] ?? 0;
                        $scaleX    = $obj['scaleX'] ?? 1;
                        $scaleY    = $obj['scaleY'] ?? 1;
                        $width     = ($obj['width'] ?? 200) * $scaleX;
                        $height    = isset($obj['height']) ? $obj['height'] * $scaleY : null;
                        $fontSize  = $obj['fontSize'] ?? 14;
                        $fontFamily= $obj['fontFamily'] ?? 'Arial';
                        $fill      = $obj['fill'] ?? '#000000';
                        $fontWeight= $obj['fontWeight'] ?? 'normal';
                        $fontStyle = $obj['fontStyle'] ?? 'normal';
                        $underline = $obj['underline'] ?? false;
                        $textAlign = $obj['textAlign'] ?? 'left';
                        $text      = $obj['text'] ?? '';
                        $angle     = $obj['angle'] ?? 0;
                        $lineHeight= $obj['lineHeight'] ?? 1.16;
                        $originX   = $obj['originX'] ?? 'left';
                        $originY   = $obj['originY'] ?? 'top';
                        $adjustLeft = $left;
                        $adjustTop  = $top;
                        if ($originX === 'center') $adjustLeft = $left - $width / 2;
                        if ($originY === 'center') $adjustTop  = $top - ($height ?? 0) / 2;
                    @endphp

                    @if($type === 'textbox' || $type === 'i-text' || $type === 'text')
                        @php $isSignature = str_starts_with($text, 'data:image/'); @endphp
                        @if($isSignature)
                            <img src="{{ $text }}" class="fabric-object" style="
                                left:{{ $adjustLeft }}px;
                                top:{{ $adjustTop }}px;
                                width:{{ $width }}px;
                                height:auto;
                                @if($angle != 0) transform:rotate({{ $angle }}deg); @endif
                            ">
                        @else
                            <div class="fabric-object" style="
                                left:{{ $adjustLeft }}px;
                                top:{{ $adjustTop }}px;
                                width:{{ $width }}px;
                                min-height:{{ $height ?: 0 }}px;
                                font-size:{{ $fontSize }}px;
                                font-family:{{ $fontFamily }},Arial,sans-serif;
                                color:{{ $fill }};
                                font-weight:{{ $fontWeight }};
                                font-style:{{ $fontStyle }};
                                text-decoration:{{ $underline ? 'underline' : 'none' }};
                                text-align:{{ $textAlign }};
                                line-height:{{ $lineHeight }};
                                @if($angle != 0) transform:rotate({{ $angle }}deg); @endif
                            ">{{ $text }}</div>
                        @endif

                    @elseif($type === 'image')
                        @php
                            $imgW = ($obj['width'] ?? 100) * $scaleX;
                            $imgH = ($obj['height'] ?? 100) * $scaleY;
                            $src  = $obj['src'] ?? '';
                        @endphp
                        @if($src)
                            <img src="{{ $src }}" class="fabric-object" style="
                                left:{{ $adjustLeft }}px;
                                top:{{ $adjustTop }}px;
                                width:{{ $imgW }}px;
                                height:{{ $imgH }}px;
                                @if($angle != 0) transform:rotate({{ $angle }}deg); @endif
                            ">
                        @endif

                    @elseif($type === 'rect')
                        @php
                            $rW = ($obj['width'] ?? 100) * $scaleX;
                            $rH = ($obj['height'] ?? 100) * $scaleY;
                            $bgFill  = $obj['fill'] ?? 'transparent';
                            $stroke  = $obj['stroke'] ?? 'transparent';
                            $strokeW = $obj['strokeWidth'] ?? 0;
                        @endphp
                        <div class="fabric-object" style="
                            left:{{ $adjustLeft }}px;
                            top:{{ $adjustTop }}px;
                            width:{{ $rW }}px;
                            height:{{ $rH }}px;
                            background:{{ $bgFill }};
                            border:{{ $strokeW }}px solid {{ $stroke }};
                            @if($angle != 0) transform:rotate({{ $angle }}deg); @endif
                        "></div>

                    @elseif($type === 'line')
                        @php
                            $x1 = $obj['x1'] ?? 0;
                            $y1 = $obj['y1'] ?? 0;
                            $x2 = $obj['x2'] ?? 100;
                            $y2 = $obj['y2'] ?? 0;
                            $lineW      = abs($x2 - $x1) * $scaleX;
                            $lineH      = abs($y2 - $y1) * $scaleY;
                            $lineColor  = $obj['stroke'] ?? '#000';
                            $lineStroke = $obj['strokeWidth'] ?? 1;
                        @endphp
                        <div class="fabric-object" style="
                            left:{{ $adjustLeft }}px;
                            top:{{ $adjustTop }}px;
                            width:{{ max($lineW, 1) }}px;
                            height:{{ max($lineH, $lineStroke) }}px;
                            background:{{ $lineColor }};
                            @if($angle != 0) transform:rotate({{ $angle }}deg); @endif
                        "></div>
                    @endif
                @endforeach
            @endif
        </div>
    @endforeach
@else
    @php
        $descHtml = preg_replace_callback(
            '/(data:image\/[a-zA-Z]+;base64,[A-Za-z0-9+\/=]+)/',
            function($matches) {
                return '<img src="' . $matches[1] . '" style="max-width:150px;max-height:80px;object-fit:contain;">';
            },
            $description
        );
    @endphp
    <div class="html-content">
        {!! $descHtml !!}
    </div>
@endif

</body>
</html>
