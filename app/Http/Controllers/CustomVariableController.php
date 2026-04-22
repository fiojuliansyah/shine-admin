<?php

namespace App\Http\Controllers;

use App\Models\CustomVariable;
use Illuminate\Http\Request;

class CustomVariableController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'value' => 'required|string|max:255',
        ]);

        $customVariable = new CustomVariable();
        $customVariable->name = $request->input('name');
        $customVariable->value = $request->input('value');
        $customVariable->save();

        return redirect()->back()->with('success', 'Custom variable created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(CustomVariable $customVariable)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CustomVariable $customVariable)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CustomVariable $customVariable)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'value' => 'required|string|max:255',
        ]);

        $customVariable->name = $request->input('name');
        $customVariable->value = $request->input('value');
        $customVariable->save();

        return redirect()->back()->with('success', 'Custom variable created successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CustomVariable $customVariable)
    {
        $customVariable->delete();

        return redirect()->back()->with('success', 'Custom variable created successfully.');
    }
}
