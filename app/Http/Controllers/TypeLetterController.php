<?php

namespace App\Http\Controllers;

use DataTables;
use App\Models\TypeLetter;
use Illuminate\Http\Request;
use App\DataTables\TypeLettersDataTable;

class TypeLetterController extends Controller
{
    /**
     * Menampilkan daftar type letter.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(TypeLettersDataTable $dataTable)
    {
        return $dataTable->render('type_letters.index');
    }

    /**
     * Menyimpan type letter baru ke database.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'nullable|string|max:255',
            'number' => 'nullable|string|max:255',
            'is_numbering' => 'nullable|string|max:255',
        ]);

        $typeLetter = TypeLetter::create([
            'name' => $request->name,
            'number' => $request->number,
            'is_numbering' => $request->is_numbering,
        ]);

        return redirect()->route('type_letters.index')
                        ->with('success', 'Type letter berhasil ditambahkan');
    }

    /**
     * Menampilkan detail type letter.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $typeLetter = TypeLetter::findOrFail($id);

        return view('type_letters.show', compact('typeLetter'));
    }

    /**
     * Menampilkan form untuk mengedit type letter.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $typeLetter = TypeLetter::findOrFail($id);

        return view('type_letters.edit', compact('typeLetter'));
    }

    /**
     * Memperbarui data type letter di database.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'nullable|string|max:255',
            'number' => 'nullable|string|max:255',
            'is_numbering' => 'nullable|string|max:255',
        ]);

        $typeLetter = TypeLetter::findOrFail($id);
        $typeLetter->update([
            'name' => $request->name,
            'number' => $request->number,
            'is_numbering' => $request->is_numbering,
        ]);

        return redirect()->route('type_letters.index')
                        ->with('success', 'Type letter berhasil diperbarui');
    }

    /**
     * Menghapus type letter dari database.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $typeLetter = TypeLetter::findOrFail($id);
        $typeLetter->delete();

        return redirect()->route('type_letters.index')
                        ->with('success', 'Type letter berhasil dihapus');
    }
}
