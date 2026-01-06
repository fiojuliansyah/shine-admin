<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table,
        th,
        td {
            border: 1px solid #000;
        }

        th,
        td {
            padding: 6px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        td img {
            max-width: 60px;
            max-height: 60px;
            object-fit: cover;
        }
    </style>
</head>

<body>
    <h2>{{ $title }}</h2>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>Pegawai</th>
                <th>Task Dibuat</th>
                <th>Foto Sebelum</th>
                <th>Foto Progress</th>
                <th>Foto Sesudah</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($tasks as $index => $task)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $task->created_at->format('d/m/Y') }}</td>
                    <td>{{ $task->user->name ?? '-' }}</td>
                    <td>{{ $task->start_time }} - {{ $task->end_time }}</td>
                    @php
                        $imageDataBefore = !empty($task->image_before_url) ? @file_get_contents($task->image_before_url) : null;
                        $imageDataProgress = !empty($task->image_progress_url) ? @file_get_contents($task->image_progress_url) : null;
                        $imageDataAfter = !empty($task->image_after_url) ? @file_get_contents($task->image_after_url) : null;
                        $base64before = base64_encode($imageDataBefore) ?? '-';
                        $base64progress = base64_encode($imageDataProgress) ?? '-';
                        $base64after = base64_encode($imageDataAfter) ?? '-';
                    @endphp
                    <td>
                        <img src="data:image/jpeg;base64,{{ $base64before }}" style="max-width:60px; max-height:60px;">
                    </td>
                    <td>
                        <img src="data:image/jpeg;base64,{{ $base64progress }}" style="max-width:60px; max-height:60px;">
                    </td>
                    <td>
                        <img src="data:image/jpeg;base64,{{ $base64after }}" style="max-width:60px; max-height:60px;">
                    </td>
                    <td>{{ $task->progress_description ?? '-' }}</td>
                </tr>
            @endforeach
            @if ($tasks->isEmpty())
                <tr>
                    <td colspan="9" style="text-align: center;">Tidak ada data</td>
                </tr>
            @endif
        </tbody>
    </table>
</body>

</html>
