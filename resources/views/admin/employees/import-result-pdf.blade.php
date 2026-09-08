<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Kegagalan Import Pegawai</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #222; }
        h1 { font-size: 15px; margin: 0 0 4px; }
        .meta { font-size: 9px; color: #555; margin-bottom: 10px; }
        .meta strong { color: #222; }
        .summary { border: 1px solid #c62828; background: #fdecea; padding: 6px 8px; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #bbb; padding: 4px 5px; text-align: left; vertical-align: top; }
        th { background: #1e3a5f; color: #fff; font-size: 9px; text-transform: uppercase; }
        tr:nth-child(even) td { background: #f6f6f6; }
        .row-no { width: 40px; text-align: center; }
        .col-field { width: 110px; }
        .col-name { width: 140px; }
    </style>
</head>
<body>
    <h1>Laporan Kegagalan Import Data Pegawai</h1>
    <div class="meta">
        @isset($fileName)File: <strong>{{ $fileName }}</strong> &middot; @endisset
        @isset($at)Waktu import: <strong>{{ $at }}</strong> &middot; @endisset
        Dicetak: <strong>{{ now()->format('d-m-Y H:i') }}</strong>
    </div>

    <div class="summary">
        <strong>Import dibatalkan.</strong> Tidak ada data yang disimpan.
        Terdapat <strong>{{ count($rowErrors) }} kesalahan</strong> dari <strong>{{ $totalRows }} baris</strong> data.
    </div>

    <table>
        <thead>
            <tr>
                <th class="row-no">Baris</th>
                <th class="col-name">Nama / Referensi</th>
                <th class="col-field">Kolom</th>
                <th>Kesalahan</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rowErrors as $error)
                <tr>
                    <td class="row-no">{{ $error['row'] }}</td>
                    <td>{{ $error['employee'] }}</td>
                    <td>{{ $error['field'] }}</td>
                    <td>{{ $error['message'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
