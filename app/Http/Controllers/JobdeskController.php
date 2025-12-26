<?php

namespace App\Http\Controllers;

use App\Models\Jobdesk;
use Illuminate\Http\Request;

class JobdeskController extends Controller
{
    public function store(Request $request)
    {
        $validateRequest = $request->validate([
            'site_id' => 'required',
            'name' => 'required|string',
            'job_code' => 'required|unique:jobdesks,job_code',
            'floor_id' => 'required',
            'work_type' => 'required',
            'service_type' => 'required',
        ]);

        Jobdesk::create($validateRequest);
    
        return redirect()->back()->with('success', 'Jobdesk created successfully.');
    }
}
