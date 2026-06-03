<?php

namespace App\Http\Controllers;

use App\Models\Site;
use App\Models\Company;
use App\DataTables\EmployeesDataTable;
use App\Exports\EmployeesExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class EmployeeController extends Controller
{
    public function index(EmployeesDataTable $dataTable)
    {
        $companies = Company::with(['sites' => function ($q) {
            $q->orderBy('name', 'asc');
        }])->orderBy('name', 'asc')->get();

        $sites = Site::orderBy('name', 'asc')->get();

        return $dataTable->render('admin.employees.index', compact('sites', 'companies'));
    }

    public function export(Request $request)
    {
        $siteId   = $request->site_id ?: null;
        $siteName = $siteId ? Site::find($siteId)?->name : 'Semua Site';
        $filename = 'Data Karyawan - ' . $siteName . ' - ' . date('d-m-Y') . '.xlsx';

        return Excel::download(new EmployeesExport($siteId), $filename);
    }
}
