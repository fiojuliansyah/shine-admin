<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Overtime;
use App\DataTables\OvertimeRequestDataTable;

class OvertimeRequestController extends Controller
{
    public function index(OvertimeRequestDataTable $dataTable)
    {
        return $dataTable->render('overtime-request.index');
    }

    public function updateStatus(Request $request, Overtime $overtime)
    {
        $request->validate([
            'status' => 'required|in:pending,approved,rejected',
            'remarks' => 'nullable|string',
        ]);

        $overtime->update([
            'status' => $request->status,
            'remarks' => $request->remarks,
        ]);

        if ($request->status === 'approved' && $overtime->attendance) {
            $overtime->attendance->update([
                'has_overtime' => 'yes'
            ]);
        } else if ($request->status === 'rejected' && $overtime->attendance) {
            $overtime->attendance->update([
                'has_overtime' => null
            ]);
        }

        return redirect()->back()->with('success', 'Overtime status updated successfully.');
    }

}
