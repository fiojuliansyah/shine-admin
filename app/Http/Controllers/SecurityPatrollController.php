<?php

namespace App\Http\Controllers;

use App\Models\Floor;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\TaskPlanner;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\DataTables\SecurityPatrolTableDataTable;

class SecurityPatrollController extends Controller
{
    public function index(SecurityPatrolTableDataTable $dataTable)
    {
        return $dataTable->render('admin.security-patroll.index');
    }

    public function showFloor($id)
    {
        $floors = Floor::where('site_id', $id)->get();
        return view('admin.security-patroll.showFloor', compact('floors', 'id'));
    }

    public function showTask($id)
    {
        $today = Carbon::now()->toDateString();
        $tasks = TaskPlanner::where('floor_id', $id)->get();

        $floor = Floor::where('id', $id)->first();
        return view('admin.security-patroll.showTask', compact('tasks', 'floor'));
    }

    public function exportAll(Request $request)
    {
        $floors = Floor::all();
        $dataFloors = [];

        $logoPath = public_path('admin/assets/img/gama-trans.png');
        $logoBase64 = '';
        
        if (file_exists($logoPath)) {
            $logoData = file_get_contents($logoPath);
            $logoBase64 = 'data:image/svg+xml;base64,' . base64_encode($logoData);
        }

        foreach ($floors as $floor) {
            $svgContent = $floor->floor_qr;
            
            $svgContent = str_replace('<?xml version="1.0" encoding="UTF-8"?>', '', $svgContent);
            $svgContent = str_replace("'", '"', $svgContent);

            $base64Svg = 'data:image/svg+xml;base64,' . base64_encode($svgContent);

            $dataFloors[] = [
                'id' => $floor->id,
                'name' => $floor->name,
                'description' => $floor->description,
                'barcodeSvgUri' => $base64Svg,
            ];
        }

        $data = [
            'dataFloors' => $dataFloors,
            'logoBase64' => $logoBase64,
        ];

        $options = [
            'isRemoteEnabled' => true, 
            'defaultFont' => 'sans-serif'
        ];

        $pdf = PDF::loadView('pdf.barcodePatrolAll', $data)
            ->setOptions($options)
            ->setPaper('A4', 'landscape');

        return $pdf->stream('semua-barcode-Patrol.pdf');
    }
}
