<?php

namespace App\Http\Controllers;

use DataTables;
use App\Models\Site;
use App\Models\Letter;
use App\Models\TypeLetter;
use Illuminate\Http\Request;
use App\DataTables\LettersDataTable;
use App\Models\CustomVariable;
use App\Models\LetterNumberConfig;
use Barryvdh\DomPDF\Facade\Pdf;

class LetterController extends Controller
{
    use \App\Traits\PdfPageImageTrait;
    /**
     * Display a listing of the resource.
     */
    public function index(LettersDataTable $dataTable)
    {
        $sites = Site::all();
        $types = TypeLetter::all();

        return $dataTable->render('admin.letters.index', compact('sites', 'types'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $sites = Site::all();
        $types = TypeLetter::all();
        $numberConfigs = LetterNumberConfig::latest()->get();
        return view('admin.letters.create', compact('sites', 'types', 'numberConfigs'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // 1. Simpan data utama
        $letter = new Letter;
        $letter->site_id = $request->site_id;
        $letter->title = $request->title;
        $letter->type_letter_id = $request->type_letter_id;
        $letter->description = $request->description;
        $letter->letter_number_config_id = $request->letter_number_config_id ?: null;
        $letter->number_format = $request->number_format;
        $letter->number_prefix = $request->number_prefix;
        $letter->number_padding = $request->number_padding ?? 3;
        $letter->save();

        // 2. Simpan variabel kustom jika ada
        if ($request->has('custom_vars')) {
            foreach ($request->custom_vars as $var) {
                // Pastikan model CustomVariable sudah memiliki relasi atau gunakan Model langsung
                $customVar = new \App\Models\CustomVariable();
                $customVar->letter_id = $letter->id;
                $customVar->name = $var['name'];
                $customVar->variable = $var['variable'];
                $customVar->save();
            }
        }

        return redirect()->route('letters.index')
                        ->with('success', 'Letter ' . $letter->title . ' berhasil dibuat');
    }

    public function edit(Letter $letter)
    {
        $sites = Site::all();
        $customVariable = CustomVariable::where('letter_id', $letter->id)->first();
        $types = TypeLetter::all();
        $numberConfigs = LetterNumberConfig::latest()->get();
        return view('admin.letters.edit', compact('letter', 'sites', 'types', 'customVariable', 'numberConfigs'));
    }

    public function show(Letter $letter)
    {
        return view('admin.letters.show', compact('letter'));
    }

    public function printView(Letter $letter)
    {
        $description = $letter->description ?? '';
        $pages = [];
        $isFabric = false;
        $title = $letter->title ?? 'Surat';

        if ($description) {
            try {
                $parsed = json_decode($description, true);
                if (isset($parsed['pages']) && is_array($parsed['pages'])) {
                    $isFabric = true;
                    $pages = $parsed['pages'];
                }
            } catch (\Exception $e) {}
        }

        return view('admin.letters.print', compact('pages', 'isFabric', 'title', 'description'));
    }

    public function numberPreview(Letter $letter)
    {
        $letter->load('type', 'numberConfig');
        $typeLetter = $letter->type;
        $currentNumber = ($typeLetter->number ?? 0) + 1;

        if ($letter->letter_number_config_id && $letter->numberConfig) {
            $preview = $letter->numberConfig->generateNumber($currentNumber);
        } elseif ($letter->number_format) {
            $preview = $letter->generateLetterNumber($currentNumber);
        } else {
            $preview = null;
        }

        return response()->json(['preview' => $preview]);
    }

    public function pdf(Letter $letter)
    {
        $description = $letter->description ?? '';
        $pages = [];
        $isFabric = false;

        if ($description && $description !== '') {
            try {
                $parsed = json_decode($description, true);
                if (isset($parsed['pages']) && is_array($parsed['pages'])) {
                    $isFabric = true;
                    $pages = $this->savePageImages($parsed['pages']);
                }
            } catch (\Exception $e) {}
        }

        $pdf = Pdf::loadView('admin.letters.pdf', compact('letter', 'pages', 'isFabric', 'description'))
            ->setPaper([0, 0, 794, 1123], 'portrait');

        $response = $pdf->stream($letter->title . '.pdf');
        $this->cleanPageImages($pages);
        return $response;
    }


    public function update(Request $request, $id)
    {
        $letter = Letter::findOrFail($id);
        
        // 1. Update data utama surat
        $letter->site_id = $request->site_id;
        $letter->title = $request->title;
        $letter->type_letter_id = $request->type_letter_id;
        $letter->description = $request->description;
        $letter->letter_number_config_id = $request->letter_number_config_id ?: null;
        $letter->number_format = $request->number_format;
        $letter->number_prefix = $request->number_prefix;
        $letter->number_padding = $request->number_padding ?? 3;
        $letter->save();

        // 2. Hapus variabel yang diklik hapus di modal (jika ada)
        if ($request->has('delete_vars')) {
            \App\Models\CustomVariable::whereIn('id', $request->delete_vars)->delete();
        }

        // 3. Simpan variabel kustom baru yang ditambahkan dari modal
        if ($request->has('custom_vars')) {
            foreach ($request->custom_vars as $var) {
                // Pastikan model CustomVariable sudah ada
                $newVar = new \App\Models\CustomVariable();
                $newVar->letter_id = $letter->id;
                $newVar->name = $var['name'];
                $newVar->variable = $var['variable'];
                $newVar->save();
            }
        }

        return redirect()->route('letters.index')
                        ->with('success', 'Letter ' . $letter->title . ' dan variabel kustom berhasil diubah');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $letter = Letter::findOrFail($id);
        $letter->delete();
    
        return redirect()->back()
            ->with('success', 'Data Letter berhasil dihapus');
    }
}
