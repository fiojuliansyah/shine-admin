<?php

namespace App\DataTables;

use App\Models\Leave;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class LeavesDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('date', function ($row) {
                $start = optional($row->start_date)->format('d M Y') ?? '';
                $end = optional($row->end_date)->format('d M Y') ?? '';
                return $start . ' - ' . $end;
            })
            ->addColumn('type', function ($row) {
                return optional($row->type)->name ?? '';
            })
            ->addColumn('user', function ($row) {
                return optional($row->user)->name ?? '';
            })
            ->addColumn('site', function ($row) {
                return optional($row->site)->name ?? '';
            })
            ->addColumn('image', function ($row) {
                $image = $row->image_url ?? '';
                return $image ? '<img src="' . $image . '" width="70px">' : '';
            })
            ->addColumn('is_paid', function ($row) {
                return $row->is_paid ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-danger">No</span>';
            })
            ->addColumn('action', function ($row) {
                $types = \App\Models\TypeLeave::all();
                $users = \App\Models\User::all();
                $sites = \App\Models\Site::all();

                return view('attendances.leaves.partials.actions', compact('row', 'types', 'users', 'sites'))->render();
            })
            ->rawColumns(['image', 'is_paid', 'action'])
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(Leave $model): QueryBuilder
    {
        $query = $model->newQuery()->with('user', 'site', 'type');

        // Filter by date
        if ($this->request()->has('date') && !empty($this->request()->date)) {
            $date = $this->request()->date;
            $query->where(function ($q) use ($date) {
                $q->whereDate('start_date', '<=', $date)
                    ->whereDate('end_date', '>=', $date);
            });
        }

        // Filter by leave type
        if ($this->request()->has('type_id') && !empty($this->request()->type_id)) {
            $query->where('type_id', $this->request()->type_id);
        }

        // Filter by employee/user
        if ($this->request()->has('user_id') && !empty($this->request()->user_id)) {
            $query->where('user_id', $this->request()->user_id);
        }

        // Filter by site
        if ($this->request()->has('site_id') && !empty($this->request()->site_id)) {
            $query->where('site_id', $this->request()->site_id);
        }

        // Filter by status
        if ($this->request()->has('status') && !empty($this->request()->status)) {
            $query->where('status', $this->request()->status);
        }

        return $query;
    }

    /**
     * Optional method if you want to use the HTML builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('leaves-table')
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
            Column::make('date')->title('Tanggal'),
            Column::make('type')->title('Tipe'),
            Column::make('user')->title('Pegawai'),
            Column::make('site')->title('Site'),
            Column::make('reason')->title('Alasan'),
            Column::computed('image')->title('Foto')->searchable(false),
            Column::computed('is_paid')->title('Is Paid')->exportable(false)->printable(false),
            Column::make('contact')->title('Contact'),
            Column::computed('action')->exportable(false)->printable(false)->orderable(false)->searchable(false),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'Leaves_' . date('YmdHis');
    }
}
