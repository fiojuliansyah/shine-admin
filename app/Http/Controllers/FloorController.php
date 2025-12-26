<?php

namespace App\Http\Controllers;

use App\Models\Site;
use App\Models\Floor;
use Illuminate\Http\Request;
use App\Exports\FloorsExport;
use Barryvdh\DomPDF\Facade\Pdf;
use App\DataTables\FloorsDataTable;
use App\Imports\FloorsImport;
use Maatwebsite\Excel\Excel as ExcelExcel;
use Maatwebsite\Excel\Facades\Excel;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class FloorController extends Controller
{
    public function index(FloorsDataTable $dataTable, Request $request)
    {
        $sites = Site::all();

        $filter = [
            'site_id' => $request->site_id
        ];
        return $dataTable->render('floors.index', compact('sites', 'filter'));
    }

    public function addFloor(Request $request)
    {
        // Validasi
        $request->validate([
            'site_id' => 'required|exists:sites,id', // site_id wajib ada dan harus valid
            'name' => 'required|string|max:255',    // nama floor wajib diisi
            'description' => 'nullable|string',     // deskripsi boleh kosong
        ], [
            'site_id.required' => 'Site/Gedung wajib dipilih.',
            'site_id.exists' => 'Site/Gedung tidak valid.',
            'name.required' => 'Nama Floor wajib diisi.',
        ]);

        // Simpan ke database
        $floor = Floor::create([
            'site_id' => $request->site_id,
            'name' => $request->name,
            'description' => $request->description,
        ]);

        $url = 'https://app.gamaintegratedsystem.com/mobile/floor/' . $floor->id . '/patroll';
        $qrCode = QrCode::size(200)->generate($url);
        
        $floor->update(['floor_qr' => $qrCode]);
        return redirect()->route('floors.index')->with('success', 'Floor berhasil ditambahkan!');
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'site_id' => 'required|exists:sites,id', // site_id wajib ada dan harus valid
            'name' => 'required|string|max:255',    // nama floor wajib diisi
            'description' => 'nullable|string',     // deskripsi boleh kosong
        ], [
            'site_id.required' => 'Site/Gedung wajib dipilih.',
            'site_id.exists' => 'Site/Gedung tidak valid.',
            'name.required' => 'Nama Floor wajib diisi.',
        ]);

        $floor = Floor::findOrFail($id);
        $floor->site_id = $validated['site_id'];
        $floor->name = $validated['name'];
        $floor->description = $validated['description'];

        $qrCode = QrCode::size(200)
            ->generate("/mobile/security-patrol/$floor->id");

        $floor->floor_qr = $qrCode;
        $floor->save();

        return redirect()->route('floors.index')->with('success', 'floor success updated');
    }

    public function destroy($id)
    {
        $floor = Floor::findOrFail($id);
        $floor->delete();

        return redirect()->route('floors.index')->with('success', 'Floor success delete');
    }

    public function export()
    {
        $filename = 'floor_' . date('Y-m-D') . '.xlsx';
        return Excel::download(new FloorsExport, $filename);
    }

    public function import(Request $request) {
        $request->validate([
            'site_id' => 'required|exists:sites,id',
            'file' => 'required|mimes:xlsx,xls,csv'
        ]);

        Excel::import(new FloorsImport($request->site_id), $request->file('file')->store('files'));

        return redirect()->back()->with('success', 'Data lantai berhasil diimport!');
    }

}
