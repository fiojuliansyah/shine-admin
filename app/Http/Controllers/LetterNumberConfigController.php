<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LetterNumberConfig;
use App\Models\Company;

class LetterNumberConfigController extends Controller
{
    public function index()
    {
        $configs = LetterNumberConfig::with('company', 'sharedCounter')->latest()->get();
        $companies = Company::orderBy('name')->get();
        return view('admin.letter_number_configs.index', compact('configs', 'companies'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'              => 'required|string|max:100',
            'format'            => 'required|string|max:255',
            'prefix'            => 'nullable|string|max:50',
            'padding'           => 'required|integer|min:1|max:6',
            'start_number'      => 'required|integer|min:1',
            'shared_counter_id' => 'nullable|exists:letter_number_configs,id',
        ]);

        LetterNumberConfig::create($request->only(
            'name', 'format', 'prefix', 'padding', 'start_number',
            'company_id', 'description', 'shared_counter_id'
        ));

        return redirect()->route('letter-number-configs.index')
            ->with('success', 'Konfigurasi nomor surat berhasil ditambahkan.');
    }

    public function update(Request $request, LetterNumberConfig $letterNumberConfig)
    {
        $request->validate([
            'name'              => 'required|string|max:100',
            'format'            => 'required|string|max:255',
            'prefix'            => 'nullable|string|max:50',
            'padding'           => 'required|integer|min:1|max:6',
            'start_number'      => 'required|integer|min:1',
            'current_number'    => 'nullable|integer|min:0',
            'shared_counter_id' => 'nullable|exists:letter_number_configs,id',
        ]);

        $data = $request->only(
            'name', 'format', 'prefix', 'padding', 'start_number',
            'company_id', 'description', 'shared_counter_id'
        );

        if ($request->filled('current_number')) {
            $data['current_number'] = $request->current_number;
        }

        if ($request->shared_counter_id == $letterNumberConfig->id) {
            return redirect()->back()->with('error', 'Konfigurasi tidak bisa bergandengan dengan dirinya sendiri.');
        }

        $letterNumberConfig->update($data);

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
