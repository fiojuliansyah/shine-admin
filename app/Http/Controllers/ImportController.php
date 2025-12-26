<?php


namespace App\Http\Controllers;

use App\Imports\CombinedImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ImportController extends Controller
{
    public function processImport(Request $request)
    {
        $request->validate([
            'template' => 'required',
            'site' => 'required',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'file' => 'required|file|mimes:xlsx,xls',
        ]);
    
        $file = $request->file('file');
        $template = $request->input('template');
        $site = $request->input('site');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
    
        // Mengirimkan parameter ke CombinedImport
        Excel::import(new CombinedImport($template, $site, $startDate, $endDate), $file);
    
        return redirect()->back()->with('success', 'Data imported successfully.');
    }
    
}

