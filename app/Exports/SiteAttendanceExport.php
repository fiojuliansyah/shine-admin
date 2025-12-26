<?php

namespace App\Exports;

use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Maatwebsite\Excel\Concerns\FromCollection;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SiteAttendanceExport implements FromCollection, WithHeadings, WithStyles
{
    protected $attendancesByUser;
    protected $dates;
    protected $totalsByUser;
    protected $highlightCellsLeave = [];
    protected $highlightCellsAlpha = [];
    protected $highlightCellsShiftOff = [];
    protected $highlightCellsBeritaAcara = [];
    protected $highlightCellsOvertime = [];
    protected $mergedCells = [];
    protected $totalShiftOffByUser = [];

    public function __construct($attendancesByUser, $dates, $totalsByUser)
    {
        $this->attendancesByUser = $attendancesByUser;
        $this->dates = $dates;
        $this->totalsByUser = $totalsByUser;
    }

    public function collection()
    {
        $data = [];
        
        foreach ($this->attendancesByUser as $user_id => $userAttendances) {
            $user = $userAttendances->first()->user;
            $totals = $this->totalsByUser[$user_id] ?? [
                'totalHK' => '',
                'totalOvertime' => '',
                'totalOvertimeIn' => '',
                'totalOvertimeOut' => '',
                'totalBA' => '',
                'totalLeave' => '',
            ];
    
            $totalShiftOff = 0;
    
            // Data untuk satu baris, sesuai dengan urutan heading
            $row = [
                $user->name,                            // Kolom Nama
                $user->employee_nik,                   // Kolom NIK
                $user->roles->first()->name ?? '-',    // Kolom Jabatan
            ];
            $rowIndex = count($data) + 3; // Hitung indeks baris (untuk highlight)
    
            // Tambahkan data ke baris berdasarkan tanggal
            foreach ($this->dates as $index => $date) {
                $attendance = $userAttendances->get($date->format('Y-m-d'));
                if ($attendance) {
                    if ($attendance->leave_id != null) {
                        $row[] = $attendance->leave->type['name'] ?? '';
                        $row[] = '';
                        $row[] = '';
                        $this->highlightCellsLeave[] = [$rowIndex, count($row) - 3];
                    } elseif ($attendance->type == 'off') {
                        $row[] = '';
                        $row[] = '';
                        $row[] = '';
                        $this->highlightCellsShiftOff[] = [$rowIndex, count($row) - 3];
                        $totalShiftOff++;
                    } elseif ($attendance->type == 'minutes') {
                        $row[] = $attendance->clock_in ? $attendance->clock_in->format('H:i') : '-';
                        $row[] = $attendance->clock_out ? $attendance->clock_out->format('H:i') : '-';
                        $this->highlightCellsBeritaAcara[] = [$rowIndex, count($row) - 2];
    
                        $totalOvertimeMinutes = 0;
                        foreach ($attendance->overtimes as $overtime) {
                            try {
                                $overtimeStart = Carbon::parse($overtime->clock_in);
                                $overtimeEnd = Carbon::parse($overtime->clock_out);
    
                                if ($overtimeEnd && $overtimeStart) {
                                    $overtimeMinutes = $overtimeStart->diffInMinutes($overtimeEnd);
                                    $totalOvertimeMinutes += $overtimeMinutes;
                                }
                            } catch (\Exception $e) {
                            }
                        }
                        $overtimeHours = intdiv($totalOvertimeMinutes, 60);
                        $remainingMinutes = $totalOvertimeMinutes % 60;
                        $overtimeFormatted = $totalOvertimeMinutes > 0 ? sprintf('%02d:%02d', $overtimeHours, $remainingMinutes) : '-';
                        $row[] = $overtimeFormatted;
                        $this->highlightCellsOvertime[] = [$rowIndex, count($row) - 1];
                    } else {
                        $row[] = $attendance->clock_in ? $attendance->clock_in->format('H:i') : '-';
                        $row[] = $attendance->clock_out ? $attendance->clock_out->format('H:i') : '-';
    
                        $totalOvertimeMinutes = 0;
                        foreach ($attendance->overtimes as $overtime) {
                            try {
                                $overtimeStart = Carbon::parse($overtime->clock_in);
                                $overtimeEnd = Carbon::parse($overtime->clock_out);
    
                                if ($overtimeEnd && $overtimeStart) {
                                    $overtimeMinutes = $overtimeStart->diffInMinutes($overtimeEnd);
                                    $totalOvertimeMinutes += $overtimeMinutes;
                                }
                            } catch (\Exception $e) {
                            }
                        }
                        $overtimeHours = intdiv($totalOvertimeMinutes, 60);
                        $remainingMinutes = $totalOvertimeMinutes % 60;
                        $overtimeFormatted = $totalOvertimeMinutes > 0 ? sprintf('%02d:%02d', $overtimeHours, $remainingMinutes) : '-';
                        $row[] = $overtimeFormatted;
                        $this->highlightCellsOvertime[] = [$rowIndex, count($row) - 1];
                    }
                } else {
                    $row[] = '';
                    $row[] = '';
                    $row[] = ''; 
                    $this->highlightCellsAlpha[] = [$rowIndex, count($row) - 3];
                }
            }
    
            // Tambahkan kolom total
            $row[] = $totals['totalHK'];
            $row[] = $totals['totalOvertime'];
            $row[] = $totals['totalBA'];
            $row[] = $totals['totalLeave'];
            $row[] = $totalShiftOff; 
    
            $data[] = $row;
            $this->totalShiftOffByUser[$user_id] = $totalShiftOff;
        }

        return collect($data);
    }
    
    
    public function headings(): array
    {
        // Heading utama (Baris pertama)
        $headings = [
            'Nama Karyawan', // Kolom 1
            'NIK',           // Kolom 2
            'Jabatan',       // Kolom 3
        ];
    
        // Tambahkan header untuk tanggal
        foreach ($this->dates as $date) {
            $headings[] = $date->format('d'); // Tanggal akan di-merge
            $headings[] = '';
            $headings[] = '';
        }
    
        // Tambahkan header untuk kolom total
        $headings = array_merge($headings, [
            'Total HK',
            'Total Lembur',
            'Total BA',
            'Total Cuti',
            'Total OFF',
        ]);
    
        // Subheading (Baris kedua)
        $subHeadings = [
            '',  // Kolom Nama
            '',  // Kolom NIK
            '',  // Kolom Jabatan
        ];
    
        // Subheading untuk tanggal
        foreach ($this->dates as $date) {
            $subHeadings[] = 'IN';
            $subHeadings[] = 'OUT';
            $subHeadings[] = 'LEMBUR';
        }
    
        // Subheading untuk total
        $subHeadings = array_merge($subHeadings, [
            '', '', '', '', '',
        ]);
    
        // Return header sebagai array 2 dimensi (2 baris)
        return [
            $headings,    // Baris pertama (header utama)
            $subHeadings, // Baris kedua (subheader)
        ];
    }
    
    
    public function styles(Worksheet $sheet)
    {
        // Gaya untuk baris header (utama dan subheadings)
        $sheet->getStyle('1:2')->getFont()->setName('Verdana'); // Set font ke Verdana
        $sheet->getStyle('1:2')->getFont()->setSize(14); // Set ukuran font menjadi 14
        $sheet->getStyle('1:2')->getFont()->setBold(true); // Set font menjadi bold
        $sheet->getStyle('1:2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // Center alignment horizontal
        $sheet->getStyle('1:2')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER); // Center alignment vertical
    
        // Merge header cells untuk kolom tanggal (dimulai dari kolom ke-4)
        $startColumnIndex = 4; // Kolom ke-4 setelah Nama, NIK, Jabatan
        foreach ($this->dates as $index => $date) {
            $endColumnIndex = $startColumnIndex + 2; // Tiga kolom per tanggal (IN, OUT, LEMBUR)
            $sheet->mergeCells($this->getColumnLetter($startColumnIndex) . '1:' . $this->getColumnLetter($endColumnIndex) . '1');
            $startColumnIndex = $endColumnIndex + 1;
        }
    
        // Atur tinggi baris
        // Baris 1 dan 2 (heading dan subheading)
        $sheet->getRowDimension(1)->setRowHeight(25); // Tinggi baris 1
        $sheet->getRowDimension(2)->setRowHeight(25); // Tinggi baris 2
    
        // Baris lainnya (selain heading dan subheading)
        foreach (range(3, $sheet->getHighestRow()) as $rowIndex) {
            $sheet->getRowDimension($rowIndex)->setRowHeight(100); // Atur tinggi baris 100
            
            // Dapatkan kolom terakhir yang terisi
            $lastColumn = $sheet->getHighestColumn(); 
            
            // Set font untuk seluruh baris data dari kolom A hingga kolom terakhir
            $sheet->getStyle('A' . $rowIndex . ':' . $lastColumn . $rowIndex)
                ->getFont()
                ->setName('Verdana')  // Set font ke Verdana
                ->setSize(14)         // Set ukuran font menjadi 14
                ->setBold(true);      // Set font menjadi bold
        }
        
        // Atur padding kiri-kanan-atas-bawah dengan cara mengatur alignment
        $lastColumn = $sheet->getHighestColumn(); 

        $sheet->getStyle('A1:' . $lastColumn . $sheet->getHighestRow())->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // Center untuk kanan-kiri
        $sheet->getStyle('A1:' . $lastColumn . $sheet->getHighestRow())->getAlignment()->setVertical(Alignment::VERTICAL_CENTER); // Center untuk atas-bawah
        $sheet->getStyle('A1:' . $lastColumn . $sheet->getHighestRow())->getAlignment()->setIndent(1); // Tambahkan padding kiri-kanan (indentasi)
    
        // Atur lebar kolom untuk memberikan efek padding horizontal
        $sheet->getDefaultColumnDimension()->setWidth(15); // Atur lebar kolom
    
        // Terapkan warna highlight untuk berbagai jenis data
        foreach ($this->highlightCellsLeave as [$rowIndex, $columnIndex]) {
            $startCell = $this->getColumnLetter($columnIndex + 1) . $rowIndex;
            $endCell = $this->getColumnLetter($columnIndex + 3) . $rowIndex;
            $sheet->getStyle("$startCell:$endCell")
                ->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()
                ->setARGB('00B0F0');
    
            $sheet->getStyle("$startCell:$endCell")
                ->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                ->setVertical(Alignment::VERTICAL_CENTER);
        }
    
        foreach ($this->highlightCellsAlpha as [$rowIndex, $columnIndex]) {
            $startCell = $this->getColumnLetter($columnIndex + 1) . $rowIndex;
            $endCell = $this->getColumnLetter($columnIndex + 3) . $rowIndex;
            $sheet->getStyle("$startCell:$endCell")
                ->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()
                ->setARGB('FF0000');
    
            $sheet->getStyle("$startCell:$endCell")
                ->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                ->setVertical(Alignment::VERTICAL_CENTER);
        }
    
        foreach ($this->highlightCellsShiftOff as [$rowIndex, $columnIndex]) {
            $startCell = $this->getColumnLetter($columnIndex + 1) . $rowIndex;
            $endCell = $this->getColumnLetter($columnIndex + 3) . $rowIndex;
            $sheet->getStyle("$startCell:$endCell")
                ->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()
                ->setARGB('92D050');
            
            $sheet->getStyle("$startCell:$endCell")
                ->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                ->setVertical(Alignment::VERTICAL_CENTER);
        }
    }
    
    private function getColumnLetter($columnNumber)
    {
        $letters = '';
        while ($columnNumber > 0) {
            $columnNumber--;
            $letters = chr($columnNumber % 26 + 65) . $letters;
            $columnNumber = (int)($columnNumber / 26);
        }
        return $letters;
    }
}
