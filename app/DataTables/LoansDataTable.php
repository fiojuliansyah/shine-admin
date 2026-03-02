<?php

namespace App\DataTables;

use App\Models\Loan;
use App\Models\User;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Services\DataTable;

class LoansDataTable extends DataTable
{
    public function dataTable($query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('user', fn($row) => $row->user ? $row->user->name : '-')
            ->addColumn('status', fn($row) => ucfirst($row->status))
            ->addColumn('actions', function ($row) {
                $edit = route('loans.edit', $row->id);
                $show = route('loans.show', $row->id);
                $delete = route('loans.destroy', $row->id);
                $users = User::orderBy('name')->get();

                return view('admin.loans.partials.actions', compact('edit', 'show', 'delete', 'row', 'users'));
            })
            ->rawColumns(['actions']);
    }

    public function query(Loan $model)
    {
        return $model->newQuery()->with('user');
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('loans-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(0);
    }

    public function getColumns(): array
    {
        return [
            ['data' => 'id', 'name' => 'id', 'title' => 'ID'],
            ['data' => 'user', 'name' => 'user.name', 'title' => 'User'],
            ['data' => 'amount', 'name' => 'amount', 'title' => 'Amount'],
            ['data' => 'interest_rate', 'name' => 'interest_rate', 'title' => 'Interest'],
            ['data' => 'tenor', 'name' => 'tenor', 'title' => 'Tenor'],
            ['data' => 'status', 'name' => 'status', 'title' => 'Status'],
            ['data' => 'actions', 'name' => 'actions', 'title' => 'Actions', 'orderable' => false, 'searchable' => false],
        ];
    }

    protected function filename(): string
    {
        return 'Loans_' . date('YmdHis');
    }
}