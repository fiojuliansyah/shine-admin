<?php

namespace App\Http\Controllers;

use App\DataTables\LoansDataTable;
use App\Models\Loan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class LoanController extends Controller
{
    public function index(LoansDataTable $dataTable)
    {
        $users = User::orderBy('name')->get();
        return $dataTable->render('admin.loans.index', compact('users'));
    }

    public function create()
    {
        return view('admin.loans.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:0',
            'interest_rate' => 'required|numeric|min:0',
            'tenor' => 'required|integer|min:1',
        ]);

        $r = $validated['interest_rate'] / 100 / 12;
        $principal = $validated['amount'];
        $tenor = $validated['tenor'];

        if ($r == 0) {
            $monthly = $principal / $tenor;
        } else {
            $monthly = $principal * ($r / (1 - pow(1 + $r, -$tenor)));
        }

       Loan::create([
            'user_id' => $validated['user_id'],
            'amount' => $validated['amount'],
            'interest_rate' => $validated['interest_rate'],
            'tenor' => $validated['tenor'],
            'monthly_installment' => $monthly,
            'remaining_balance' => $validated['amount'],
            'status' => 'ongoing',
            'start_date' => now(),
            'due_date' => Carbon::now()->addMonths((int) $validated['tenor']),
        ]);

        return redirect()->route('loans.index');
    }

    public function show(Loan $loan)
    {
        $loan->load(['user', 'payments']);
        return view('admin.loans.show', compact('loan'));
    }

    public function edit(Loan $loan)
    {
        return view('admin.loans.edit', compact('loan'));
    }

    public function update(Request $request, Loan $loan)
    {
        $validated = $request->validate([
            'amount' => 'sometimes|numeric|min:0',
            'interest_rate' => 'sometimes|numeric|min:0',
            'tenor' => 'sometimes|integer|min:1',
            'status' => 'sometimes|in:ongoing,paid,overdue',
        ]);

        $loan->update($validated);

        return redirect()->route('loans.index');
    }

    public function destroy(Loan $loan)
    {
        $loan->delete();
        return redirect()->route('loans.index');
    }
}