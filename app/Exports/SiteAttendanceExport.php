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
    protected $highlightCellsLate = [];
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
            
            $totals = $this->totalsByUser[$user_id] ?? [];
            $totalHK = $totals['totalHK'] ?? 0;
            $totalOvertime = $totals['totalOvertime'] ?? 0;
            $totalBA = $totals['totalBA'] ?? 0;
            $totalLeave = $totals['totalLeave'] ?? 0;
    
            $totalShiftOff = 0;
            $counterLateManual = 0; 
            
            $row = [
                $user->name,
                $user->employee_nik,
                $user->roles->first()->name ?? '-', 
            ];
            $rowIndex = count($data) + 3;
    
            foreach ($this->dates as $date) {
                $attendance = $userAttendances->get($date->format('Y-m-d'));
                if ($attendance) {
                    if ($attendance->type == 'late') {
                        $this->highlightCellsLate[] = [$rowIndex, count($row)];
                        $counterLateManual++;
                    }

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
                                    $totalOvertimeMinutes += $overtimeStart->diffInMinutes($overtimeEnd);
                                }
                            } catch (\Exception $e) {}
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
                                    $totalOvertimeMinutes += $overtimeStart->diffInMinutes($overtimeEnd);
                                }
                            } catch (\Exception $e) {}
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
    
            $row[] = $totalHK;
            $row[] = $totalOvertime;
            $row[] = $totals['totalLate'] ?? $counterLateManual;
            $row[] = $totalBA;
            $row[] = $totalLeave;
            $row[] = $totalShiftOff; 
    
            $data[] = $row;
            $this->totalShiftOffByUser[$user_id] = $totalShiftOff;
        }

        return collect($data);
    }
    
    public function headings(): array
    {
        $headings = ['Nama Karyawan', 'NIK', 'Jabatan'];
    
        foreach ($this->dates as $date) {
            $headings[] = $date->format('d');
            $headings[] = '';
            $headings[] = '';
        }
    
        $headings = array_merge($headings, [
            'Total HK', 'Total Lembur', 'Total Telat', 'Total BA', 'Total Cuti', 'Total OFF',
        ]);
    
        $subHeadings = ['', '', ''];
    
        foreach ($this->dates as $date) {
            $subHeadings[] = 'IN';
            $subHeadings[] = 'OUT';
            $subHeadings[] = 'LEMBUR';
        }
    
        $subHeadings = array_merge($subHeadings, ['', '', '', '', '', '']);
    
        return [$headings, $subHeadings];
    }
    
    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('1:2')->getFont()->setName('Verdana')->setSize(14)->setBold(true);
        $sheet->getStyle('1:2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
    
        $startColumnIndex = 4;
        foreach ($this->dates as $index => $date) {
            $endColumnIndex = $startColumnIndex + 2;
            $sheet->mergeCells($this->getColumnLetter($startColumnIndex) . '1:' . $this->getColumnLetter($endColumnIndex) . '1');
            $startColumnIndex = $endColumnIndex + 1;
        }
    
        $sheet->getRowDimension(1)->setRowHeight(25);
        $sheet->getRowDimension(2)->setRowHeight(25);
    
        foreach (range(3, $sheet->getHighestRow()) as $rowIndex) {
            $sheet->getRowDimension($rowIndex)->setRowHeight(100);
            $lastColumn = $sheet->getHighestColumn(); 
            $sheet->getStyle('A' . $rowIndex . ':' . $lastColumn . $rowIndex)
                ->getFont()->setName('Verdana')->setSize(14)->setBold(true);  
        }
        
        $lastColumn = $sheet->getHighestColumn(); 
        $sheet->getStyle('A1:' . $lastColumn . $sheet->getHighestRow())->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getDefaultColumnDimension()->setWidth(15);
    
        foreach ($this->highlightCellsLeave as [$rowIndex, $columnIndex]) {
            $startCell = $this->getColumnLetter($columnIndex + 1) . $rowIndex;
            $endCell = $this->getColumnLetter($columnIndex + 3) . $rowIndex;
            $sheet->getStyle("$startCell:$endCell")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('00B0F0');
        }
    
        foreach ($this->highlightCellsAlpha as [$rowIndex, $columnIndex]) {
            $startCell = $this->getColumnLetter($columnIndex + 1) . $rowIndex;
            $endCell = $this->getColumnLetter($columnIndex + 3) . $rowIndex;
            $sheet->getStyle("$startCell:$endCell")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF0000');
        }
    
        foreach ($this->highlightCellsShiftOff as [$rowIndex, $columnIndex]) {
            $startCell = $this->getColumnLetter($columnIndex + 1) . $rowIndex;
            $endCell = $this->getColumnLetter($columnIndex + 3) . $rowIndex;
            $sheet->getStyle("$startCell:$endCell")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('92D050');
        }

        foreach ($this->highlightCellsLate as [$rowIndex, $columnIndex]) {
            $cell = $this->getColumnLetter($columnIndex + 1) . $rowIndex;
            $sheet->getStyle($cell)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFF00');
        }

        foreach ($this->highlightCellsBeritaAcara as [$rowIndex, $columnIndex]) {
            $cell = $this->getColumnLetter($columnIndex + 1) . $rowIndex;
            $sheet->getStyle($cell)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('E26B0A');
        }

        foreach ($this->highlightCellsOvertime as [$rowIndex, $columnIndex]) {
            $cell = $this->getColumnLetter($columnIndex + 1) . $rowIndex;
            $sheet->getStyle($cell)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFC000');
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