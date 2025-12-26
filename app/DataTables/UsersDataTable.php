<?php

namespace App\DataTables;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class UsersDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('avatar', function ($row) {
                return '<img class="rounded-circle" src="' . ($row->profile->avatar_url ?? '/assets/media/avatars/blank.png') . '" alt="' . ($row->name ?? 'User') . '" width="50px"/>';
            })
            ->addColumn('employee', function ($row) {
                return $row->name . '<br>' . $row->employee_nik;
            })
            ->addColumn('detail', function ($row) {
                $colors = ['bg-warning', 'bg-primary', 'bg-success', 'bg-danger', 'bg-info', 'bg-secondary'];
                $output = '';

                if (!empty($row->getRoleNames())) {
                    foreach ($row->getRoleNames() as $role) {
                        $randomColor = $colors[array_rand($colors)];
                        $output .= '<span class="badge ' . $randomColor . '">' . $role . '</span> ';
                    }
                }

                $output .= '<br>';
                $output .= '<span>' . $row->email . '</span><br>';
                $output .= '<span>Leader : <strong>' . ($row->leader['name'] ?? '') . '</strong></span>';

                return $output;
            })
            ->addColumn('site', function ($row) {
                $siteName = $row->site->name ?? '';
                $companyName = $row->site->company->name ?? '';
                return $siteName . '<br>' . $companyName;
            })
            ->addColumn('status', function ($row) {
                return $row->profile && $row->profile->resign_date != null
                    ? '<span class="badge bg-danger">Resign</span>'
                    : '<span class="badge bg-success">Active</span>';
            })
            ->addColumn('action', function ($row) {
                return view('users.partials.actions', compact('row'))->render();
            })
            ->rawColumns(['action', 'avatar', 'employee', 'detail', 'status', 'site'])
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(User $model): QueryBuilder
    {
        return $model->newQuery()
            ->with('site', 'leader', 'profile')
            ->where('is_employee', 1);
    }

    /**
     * Optional method if you want to use the HTML builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('users-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(1)
            ->selectStyleSingle()
            ->buttons([
                Button::make('excel'),
                Button::make('csv'),
                Button::make('pdf'),
                Button::make('print'),
                Button::make('reset'),
                Button::make('reload')
            ]);
    }

    /**
     * Get the dataTable columns definition.
     */
    public function getColumns(): array
    {
        return [
            Column::make('avatar')->title('Avatar')->orderable(false)->searchable(false),
            Column::make('employee')->title('Pegawai'),
            Column::make('detail')->title('Detail'),
            Column::make('site')->title('Site'),
            Column::make('status')->title('Status'),
            Column::computed('action')->exportable(false)->printable(false)->orderable(false)->searchable(false),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'Users_' . date('YmdHis');
    }
}

