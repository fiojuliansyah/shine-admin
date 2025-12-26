<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\ComponentType;

class ComponentTypeController extends Controller
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
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'site_id' => 'nullable',
            'name' => 'nullable'
        ]);

        $componentType = ComponentType::create([
            'site_id' => $request->site_id,
            'name' => $request->name
        ]);
        
        return redirect()->back()->with('success', 'Payroll Allowance created successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(ComponentType $componentType)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ComponentType $componentType)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ComponentType $componentType)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ComponentType $componentType)
    {
        //
    }
}
