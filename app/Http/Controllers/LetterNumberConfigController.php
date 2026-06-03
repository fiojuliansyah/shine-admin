<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LetterNumberConfig;
use App\Models\Company;

class LetterNumberConfigController extends Controller
{
    public function index()
    {
        $configs = LetterNumberConfig::with('company')->latest()->get();
        $companies = Company::orderBy('name')->get();
        return view('admin.letter_number_configs.index', compact('configs', 'companies'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'         => 'required|string|max:100',
            'format'       => 'required|string|max:255',
            'prefix'       => 'nullable|string|max:50',
            'padding'      => 'required|integer|min:1|max:6',
            'start_number' => 'required|integer|min:1',
        ]);

        LetterNumberConfig::create($request->only('name', 'format', 'prefix', 'padding', 'start_number', 'company_id', 'description'));

        return redirect()->route('letter-number-configs.index')
            ->with('success', 'Konfigurasi nomor surat berhasil ditambahkan.');
    }

    public function update(Request $request, LetterNumberConfig $letterNumberConfig)
    {
        $request->validate([
            'name'         => 'required|string|max:100',
            'format'       => 'required|string|max:255',
            'prefix'       => 'nullable|string|max:50',
            'padding'      => 'required|integer|min:1|max:6',
            'start_number' => 'required|integer|min:1',
        ]);

        $letterNumberConfig->update($request->only('name', 'format', 'prefix', 'padding', 'start_number', 'company_id', 'description'));

        return redirect()->route('letter-number-configs.index')
            ->with('success', 'Konfigurasi nomor surat berhasil diperbarui.');
    }

    public function destroy(LetterNumberConfig $letterNumberConfig)
    {
        $letterNumberConfig->delete();
        return redirect()->route('letter-number-configs.index')
            ->with('success', 'Konfigurasi nomor surat berhasil dihapus.');
    }
}
