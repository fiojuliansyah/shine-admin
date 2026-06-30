<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Site;
use App\Models\SalarySetting;
use Illuminate\Http\Request;

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
