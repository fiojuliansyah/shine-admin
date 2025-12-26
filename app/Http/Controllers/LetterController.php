<?php

namespace App\Http\Controllers;

use DataTables;
use App\Models\Site;
use App\Models\Letter;
use App\Models\TypeLetter;
use Illuminate\Http\Request;
use App\DataTables\LettersDataTable;

class LetterController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(LettersDataTable $dataTable)
    {
        $sites = Site::all();
        $types = TypeLetter::all();

        return $dataTable->render('letters.index', compact('sites', 'types'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $sites = Site::all();
        $types = TypeLetter::all();

        return view('letters.create',compact('sites','types'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $letter = new Letter;
        $letter->site_id = $request->site_id;
        $letter->title = $request->title;
        $letter->type_letter_id = $request->type_letter_id;
        $letter->description = $request->description;
        $letter->save();

        return redirect()->route('letters.index')
                        ->with('success', 'Letter ' . $letter->title . ' berhasil dibuat');
    }

    public function edit(Letter $letter)
    {
        $sites = Site::all();
        $types = TypeLetter::all();
        return view('letters.edit',compact('letter','sites','types'));
    }
    public function show(Letter $letter)
    {
        return view('letters.show',compact('letter'));
    }


    public function update(Request $request, $id)
    {
        $letter = Letter::findOrFail($id);
        $letter->site_id = $request->site_id;
        $letter->title = $request->title;
        $letter->type_letter_id = $request->type_letter_id;
        $letter->description = $request->description;
        $letter->save();

        return redirect()->route('letters.index')
                        ->with('success', 'Letter ' . $letter->title . ' berhasil diubah');
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
