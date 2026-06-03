<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\EmployeeNikConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EmployeeNikConfigController extends Controller
{
    public function index()
    {
        $configs = EmployeeNikConfig::with('company')
            ->orderBy('company_id')
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->get();
        $companies = Company::orderBy('name', 'asc')->get();

        return view('admin.employee_nik_configs.index', compact('configs', 'companies'));
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);

        DB::transaction(function () use ($data) {
            if (!empty($data['is_default'])) {
                EmployeeNikConfig::where('company_id', $data['company_id'])
                    ->update(['is_default' => false]);
            }

            $exists = EmployeeNikConfig::where('company_id', $data['company_id'])->exists();
            if (!$exists) {
                $data['is_default'] = true;
            }

            EmployeeNikConfig::create($data);
        });

        return redirect()->route('employee-nik-configs.index')
            ->with('success', 'Konfigurasi NIK berhasil ditambahkan.');
    }

    public function update(Request $request, EmployeeNikConfig $employeeNikConfig)
    {
        $data = $this->validateData($request, $employeeNikConfig->id);

        DB::transaction(function () use ($data, $employeeNikConfig) {
            if (!empty($data['is_default'])) {
                EmployeeNikConfig::where('company_id', $data['company_id'])
                    ->where('id', '!=', $employeeNikConfig->id)
                    ->update(['is_default' => false]);
            }

            unset($data['current_number']);
            $employeeNikConfig->update($data);
        });

        return redirect()->route('employee-nik-configs.index')
            ->with('success', 'Konfigurasi NIK berhasil diperbarui.');
    }

    public function destroy(EmployeeNikConfig $employeeNikConfig)
    {
        $companyId = $employeeNikConfig->company_id;
        $wasDefault = $employeeNikConfig->is_default;

        $employeeNikConfig->delete();

        if ($wasDefault) {
            $next = EmployeeNikConfig::where('company_id', $companyId)->first();
            if ($next) {
                $next->update(['is_default' => true]);
            }
        }

        return redirect()->route('employee-nik-configs.index')
            ->with('success', 'Konfigurasi NIK berhasil dihapus.');
    }

    public function preview(Request $request)
    {
        $request->validate([
            'company_id' => 'required|exists:companies,id',
            'format'     => 'required|string',
            'padding'    => 'nullable|integer|min:1|max:10',
        ]);

        $tmp = new EmployeeNikConfig([
            'company_id'   => $request->company_id,
            'format'       => $request->format,
            'prefix'       => $request->prefix,
            'padding'      => $request->padding ?: 5,
            'start_number' => $request->start_number ?: 1,
        ]);
        $tmp->setRelation('company', Company::find($request->company_id));

        $sequence = (int) ($request->start_number ?: 1);

        return response()->json([
            'preview' => $tmp->formatNumber($sequence, null, now()),
        ]);
    }

    private function validateData(Request $request, $ignoreId = null): array
    {
        $rules = [
            'company_id'   => 'required|exists:companies,id',
            'name'         => 'required|string|max:120',
            'format'       => 'required|string|max:255',
            'prefix'       => 'nullable|string|max:50',
            'padding'      => 'required|integer|min:1|max:10',
            'start_number' => 'required|integer|min:1',
            'is_default'   => 'nullable|boolean',
            'description'  => 'nullable|string|max:500',
        ];

        $data = $request->validate($rules);
        $data['is_default'] = (bool) ($data['is_default'] ?? false);

        $unique = EmployeeNikConfig::where('company_id', $data['company_id'])
            ->where('name', $data['name']);
        if ($ignoreId) {
            $unique->where('id', '!=', $ignoreId);
        }
        if ($unique->exists()) {
            abort(redirect()->back()
                ->withInput()
                ->withErrors(['name' => 'Nama konfigurasi sudah dipakai untuk company ini.']));
        }

        return $data;
    }
}
