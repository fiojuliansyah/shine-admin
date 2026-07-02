<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Site;
use App\Models\SalarySetting;
use App\Exports\SalarySettingsExport;
use App\Imports\SalarySettingsImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class SalarySettingController extends Controller
{
    private array $componentFields = [
        'gaji_pokok',
        'tunj_jabatan',
        'tunj_kehadiran',
        'tunj_komunikasi',
        'tunj_makan',
        'tunj_transport',
        'tunj_lembur_tetap',
        'tunj_other_non_fix',
    ];

    public function index(Request $request)
    {
        $sites = Site::orderBy('name')->get();

        $settings = SalarySetting::with(['user.site', 'user.roles'])
            ->whereHas('user')
            ->when($request->search, function ($query, $search) {
                $query->whereHas('user', function ($q) use ($search) {
                    $q->where('name', 'like', "%$search%")
                        ->orWhere('employee_nik', 'like', "%$search%");
                });
            })
            ->when($request->site_id, function ($query, $siteId) {
                $query->whereHas('user', fn ($q) => $q->where('site_id', $siteId));
            })
            ->orderByDesc('updated_at')
            ->get();

        $employees = User::where('is_employee', 1)
            ->orderBy('name')
            ->get(['id', 'name', 'employee_nik']);

        return view('admin.salary_settings.index', compact('settings', 'employees', 'sites'));
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);

        if (SalarySetting::where('user_id', $data['user_id'])->exists()) {
            return redirect()->route('salary-settings.index')
                ->with('error', 'Pengaturan gaji untuk karyawan ini sudah ada. Silakan edit data yang ada.');
        }

        SalarySetting::create($data);

        return redirect()->route('salary-settings.index')
            ->with('success', 'Pengaturan gaji berhasil ditambahkan.');
    }

    public function update(Request $request, SalarySetting $salarySetting)
    {
        $data = $this->validateData($request, $salarySetting->id);

        $salarySetting->update($data);

        return redirect()->route('salary-settings.index')
            ->with('success', 'Pengaturan gaji berhasil diperbarui.');
    }

    public function destroy(SalarySetting $salarySetting)
    {
        $salarySetting->delete();

        return redirect()->route('salary-settings.index')
            ->with('success', 'Pengaturan gaji berhasil dihapus.');
    }

    public function export(Request $request)
    {
        $siteId   = $request->site_id ?: null;
        $siteName = $siteId ? (Site::find($siteId)?->name ?? 'Site') : 'Semua Site';
        $filename = 'Pengaturan Gaji - ' . $siteName . ' - ' . date('d-m-Y') . '.xlsx';

        return Excel::download(new SalarySettingsExport($siteId), $filename);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file'    => 'required|file|mimes:xlsx,xls,csv',
            'site_id' => 'nullable|exists:sites,id',
        ]);

        $siteId = $request->site_id ?: null;
        $import = new SalarySettingsImport($siteId);

        try {
            Excel::import($import, $request->file('file'));
        } catch (\Throwable $e) {
            return redirect()->route('salary-settings.index')
                ->with('error', 'Gagal mengimpor file: ' . $e->getMessage());
        }

        $message = "Import selesai. {$import->getImported()} data gaji diperbarui.";
        if ($import->getSkipped() > 0) {
            $niks = implode(', ', array_slice($import->getSkippedNiks(), 0, 10));
            $more = $import->getSkipped() > 10 ? ' ...' : '';
            $message .= " {$import->getSkipped()} baris dilewati (NIK tidak ditemukan: {$niks}{$more}).";
        }

        return redirect()->route('salary-settings.index')->with('success', $message);
    }

    private function validateData(Request $request, $ignoreId = null): array
    {
        $rules = ['user_id' => 'required|exists:users,id'];
        foreach ($this->componentFields as $field) {
            $rules[$field] = 'nullable|numeric|min:0';
        }

        $data = $request->validate($rules);

        foreach ($this->componentFields as $field) {
            $data[$field] = $data[$field] ?? 0;
        }

        return $data;
    }
}
