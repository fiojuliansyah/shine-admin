<?php

namespace App\DataTables;

use App\Models\User;
use App\Models\Valet;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Services\DataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;

class ValetDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('user', function ($row) {
                return optional($row->user)->name ?? '';  // Menampilkan nama user
            })
            ->addColumn('status', function ($row) {
                switch ($row->status) {
                    case 'success':
                        return '<span class="badge bg-success">Success</span>';
                    case 'pending':
                        return '<span class="badge bg-warning">Pending</span>';
                    case 'canceled':
                        return '<span class="badge bg-danger">Canceled</span>';
                    default:
                        return '-';
                }
            })
            ->addColumn('action', function ($row) {
                $users = User::all();
                return view('valets.partials.actions', compact('row','users'))->render();
            })
            ->rawColumns(['status', 'action'])  // Membuat status dan action bisa dieksekusi sebagai HTML
            ->setRowId('id');  // Menggunakan ID sebagai row ID
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(Valet $model): QueryBuilder
    {
        return $model->newQuery()->with('user');  // Mengambil data Valet bersama dengan relasi user
    }

    /**
     * Optional method if you want to use the HTML builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('valets-table')
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
            Column::make('user')->title('Pengajuan'),
            Column::make('transaction_id')->title('Transaction ID'),
            Column::make('name')->title('Nama Customer'),
            Column::make('plat_number')->title('Plat Number'),
            Column::make('amount')->title('Amount'),
            Column::computed('status')->title('Status')->exportable(false)->printable(false),
            Column::computed('action')->exportable(false)->printable(false)->orderable(false)->searchable(false),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'Valets_' . date('YmdHis');
    }
}

