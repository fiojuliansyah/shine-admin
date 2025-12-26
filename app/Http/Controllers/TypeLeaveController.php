<?php

namespace App\Http\Controllers;

use DataTables;
use App\Models\Site;
use App\Models\TypeLeave;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\DataTables\TypeLeavesDataTable;

class TypeLeaveController extends Controller
{
    public function index(TypeLeavesDataTable $dataTable)
    {
        $sites = Site::all();
        return $dataTable->render('types.index', compact('sites'));
    }


    public function store(Request $request)
    {
        $data = $request->all();
    
        $data['slug'] = Str::slug($request->input('name'));
    
        TypeLeave::create($data);
    
        return redirect()->route('types.index')
                         ->with('success', 'Type Leave created successfully.');
    }

    public function update(Request $request, TypeLeave $type)
    {
        $type->update($request->all());

        return redirect()->route('types.index')
                         ->with('success', 'Type Leave updated successfully.');
    }


    public function destroy(TypeLeave $type)
    {
        $type->delete();

        return redirect()->route('types.index')
                         ->with('success', 'Type Leave deleted successfully.');
    }
}
