<?php

namespace App\Http\Controllers;

use App\Models\Valet;
use App\Models\User;
use Illuminate\Http\Request;
use App\DataTables\ValetDataTable;
use Illuminate\Support\Str;

class ValetController extends Controller
{
    /**
     * Display the list of valet transactions.
     */
    public function index(ValetDataTable $dataTable)
    {   
        $users = User::all();
        return $dataTable->render('valets.index',compact('users'));
    }

    /**
     * Store a newly created valet transaction.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'nullable|exists:users,id',
            'transaction_id' => 'nullable|string|max:255',
            'name' => 'nullable|string',
            'image_url' => 'nullable|string',
            'image_public_id' => 'nullable|string',
            'plat_number' => 'nullable|string|max:255',
            'amount' => 'nullable|string|max:255',
            'q_code' => 'nullable|string',
            'status' => 'nullable|in:success,pending,canceled',
        ]);

        $validated['transaction_id'] = date('Ymd') . '-' . str_pad(rand(0, 99999), 5, '0', STR_PAD_LEFT);

        Valet::create($validated);

        return redirect()->route('valets.index')
                        ->with('success', 'Valet transaction created successfully.');
    }


    /**
     * Update the specified valet transaction.
     */
    public function update(Request $request, Valet $valet)
    {
        $validated = $request->validate([
            'user_id' => 'nullable|exists:users,id',
            'transaction_id' => 'nullable|string|max:255',
            'name' => 'nullable|string',
            'image_url' => 'nullable|string',
            'image_public_id' => 'nullable|string',
            'plat_number' => 'nullable|string|max:255',
            'amount' => 'nullable|string|max:255',
            'q_code' => 'nullable|string',
            'status' => 'nullable|in:success,pending,canceled',
        ]);

        $valet->update($validated);

        return redirect()->route('valets.index')
                         ->with('success', 'Valet transaction updated successfully.');
    }

    /**
     * Remove the specified valet transaction.
     */
    public function destroy(Valet $valet)
    {
        $valet->delete();

        return redirect()->route('valets.index')
                         ->with('success', 'Valet transaction deleted successfully.');
    }
}
