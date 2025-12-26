<?php

namespace App\Http\Controllers;

use App\Models\TaxRate;
use Illuminate\Http\Request;

class TaxRateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $taxRates = TaxRate::all();
        return view('tax_rates.index',compact('taxRates'));
    }

    // Menyimpan tax rate baru
    public function store(Request $request)
    {
        $request->validate([
            'marriage_status' => 'required|string|max:255',
            'ptkp' => 'required|integer',
            'tax_percentage' => 'required|numeric',
        ]);

        TaxRate::create([
            'marriage_status' => $request->marriage_status,
            'ptkp' => $request->ptkp,
            'tax_percentage' => $request->tax_percentage,
        ]);

        return redirect()->route('taxrates.index')
        ->with('success', 'Tax Rate berhasil dibuat');
    }

    // Mengambil data untuk edit
    public function edit($id)
    {
        $taxRate = TaxRate::findOrFail($id);
        return response()->json($taxRate);
    }

    // Menyimpan perubahan tax rate
    public function update(Request $request, $id)
    {
        $request->validate([
            'marriage_status' => 'required|string|max:255',
            'ptkp' => 'required|integer',
            'tax_percentage' => 'required|numeric',
        ]);

        $taxRate = TaxRate::findOrFail($id);
        $taxRate->update([
            'marriage_status' => $request->marriage_status,
            'ptkp' => $request->ptkp,
            'tax_percentage' => $request->tax_percentage,
        ]);

        return redirect()->route('taxrates.index')
        ->with('success', 'Tax Rate berhasil diubah');
    }

    // Menghapus tax rate
    public function destroy($id)
    {
        $taxRate = TaxRate::findOrFail($id);
        $taxRate->delete();

        return redirect()->route('taxrates.index')
        ->with('success', 'Tax Rate berhasil dihapus');
    }
}
