<?php

namespace App\Http\Controllers;

use App\Models\Site;
use App\DataTables\EmployeeReportDataTable;
use App\Exports\EmployeeReportExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class EmployeeReportController extends Controller
{
    public function index(EmployeeReportDataTable $dataTable)
    {
        $sites = Site::orderBy('name', 'asc')->get();
        return $dataTable->render('admin.employee_report.index', compact('sites'));
    }

    public function export(Request $request)
    {
        $siteId   = $request->site_id ?: null;
        $siteName = $siteId ? Site::find($siteId)?->name : 'Semua Site';
        $filename = 'Data Karyawan - ' . $siteName . ' - ' . date('d-m-Y') . '.xlsx';

        return Excel::download(new EmployeeReportExport($siteId), $filename);
    }
}
