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

        h2,
        h3,
        h4 {
            margin: 5px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            margin-bottom: 20px;
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
            margin-bottom: 2px;
        }

        .section-title {
            font-weight: bold;
            background: #eaeaea;
            padding: 5px;
        }
    </style>
</head>

<body>
    <h2>{{ $title }}</h2>

    <h3>Site : {{ $site->name ?? '-' }}</h3>

    @foreach ($floors as $floor)
        @php
            $patrols = $reports->where('floor_id', $floor->id);
            $floorBgColor = $patrols->isEmpty() ? '#FF4D4F' : '#FE5B24';
            $floorTextColor = '#ffffff';
        @endphp

        <h4 class="section-title"
            style="background-color:{{ $floorBgColor }}; color:{{ $floorTextColor }}; margin:30px 0 20px 0">
            Floor : {{ $floor->name ?? '-' }}
            @if ($patrols->isEmpty())
                (Belum Terpatroli)
            @endif
        </h4>

        @if ($patrols->isNotEmpty())
            @foreach ($patrols->groupBy(fn($item) => $item->patroll->turn ?? '-') as $turn => $patrollReports)
                @php
                    $session = $patrollReports->first()->patroll;
                    $durationText = '-';

                    if ($session && $session->start_time && $session->end_time) {
                        $start = \Carbon\Carbon::parse($session->start_time);
                        $end = \Carbon\Carbon::parse($session->end_time);

                        $totalMinutes = $start->diffInMinutes($end);
                        if ($totalMinutes < 1) {
                            $totalMinutes = 1;
                        }

                        $hours = intdiv($totalMinutes, 60);
                        $minutes = $totalMinutes % 60;

                        $durationParts = [];
                        if ($hours > 0) {
                            $durationParts[] = $hours . ' jam';
                        }
                        if ($minutes > 0 || $hours == 0) {
                            $durationParts[] = $minutes . ' menit';
                        }

                        $durationText = implode(' ', $durationParts);
                    }
                @endphp

                <div class="section-title">
                    - Patroli : {{ $turn }}
                    @if ($durationText !== '-')
                        (Durasi: {{ $durationText }})
                    @endif
                </div>

                <table>
                    <thead>
                        <tr>
                            <th>Jam</th>
                            <th>Nama</th>
                            <th>Foto Bukti</th>
                            <th>Title</th>
                            <th>Deskripsi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($patrollReports as $report)
                            <tr>
                                <td>{{ $report->created_at->format('H:i:s') }}</td>
                                <td>{{ $report->user->name ?? '-' }}</td>
                                <td>
                                    @if ($report->image_url)
                                        @php
                                            $img_url = base64_encode(file_get_contents($report->image_url));
                                        @endphp
                                        <img src="data:image/jpeg;base64,{{ $img_url }}">
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>{{ $report->name ?? '-' }}</td>
                                <td>{{ $report->description ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endforeach
        @endif
    @endforeach

</body>

</html>
