<!DOCTYPE html>
<html>

<head>
    <title>Laporan Temuan</title>
</head>

<body>
    <h2>Laporan Temuan: Tidak Mengerjakan Tugas Tepat Waktu</h2>
    <p>Halo,</p>
    <p>Karyawan dengan Nama: <strong>{{ $report->user->name }}</strong> di site: <strong>{{ $report->location }}</strong> belum mengerjakan tugas tepat waktu.</p>
    <p>Deskripsi: {{ $report->description }}</p>
    <p>Tanggal laporan: {{ $report->date->format('d-m-Y') }}</p>

    <p>Mohon ditindak lanjuti.</p>
    <p>Terima kasih.</p>
</body>

</html>
