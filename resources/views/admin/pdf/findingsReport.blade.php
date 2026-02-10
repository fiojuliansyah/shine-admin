<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        table, th, td { border: 1px solid #000; }
        th, td { padding: 6px; text-align: left; }
        th { background-color: #f2f2f2; }
        td img { max-width: 60px; max-height: 60px; object-fit: cover; margin-bottom: 2px; }
    </style>
</head>
<body>
    <h2>{{ $title }}</h2>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>Foto Bukti</th>
                <th>Tipe</th>
                <th>Pegawai</th>
                <th>Site</th>
                <th>Status</th>
                <th>Deskripsi</th>
                <th>Lokasi</th>
                <th>Tindakan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($reports as $index => $report)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $report->created_at->format('d/m/Y') }}</td>
                    <td>
                        @if($report->image_url)
                            @php
                                $img_url = base64_encode(file_get_contents($report->image_url));
                            @endphp
                            <img src="data:image/jpeg;base64,{{ $img_url }}">
                        @else
                            -
                        @endif
                    </td>
                    <td>{{ $report->type ?? '-' }}</td>
                    <td>{{ $report->user->name ?? '-' }}</td>
                    <td>{{ $report->site->name ?? '-' }}</td>
                    <td>{{ ucfirst($report->status) }}</td>
                    <td>{{ $report->description ?? '-' }}</td>
                    <td>{{ $report->location ?? '-' }}</td>
                    <td>{{ $report->direct_action ?? '-' }}</td>
                </tr>
            @endforeach
            @if($reports->isEmpty())
                <tr>
                    <td colspan="10" style="text-align: center;">Tidak ada data</td>
                </tr>
            @endif
        </tbody>
    </table>
</body>
</html>
