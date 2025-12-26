<?php

namespace App\DataTables;

use Carbon\Carbon;
use App\Models\Floor;
use App\Models\Jobdesk;
use App\Models\TaskPlanner;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;

class JobdeskPatrollsDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('created_at', function ($row) {
                return Carbon::parse($row->created_at)->format('Y-m-d') ?? '';
            })
            ->addColumn('floor_name', function ($row) {
                return $row->floor ? $row->floor->name : '-';
            })
            ->addColumn('action', function ($row) {
                $id = $row->site_id;
                $floors = Floor::all();
                return view('jobdesk-patroll.partials.actions', compact('row', 'floors', 'id'))->render();
            })
            ->rawColumns(['action'])
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(TaskPlanner $model): QueryBuilder
    {
        // ambil id dari route parameter
        $id = request()->route('id');

        return $model->newQuery()->where('site_id', $id);
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('jobdeskpatrolls-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            //->dom('Bfrtip')
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
            Column::computed('created_at')->title('Tanggal'),
            Column::make('name')->title('Name'),
            Column::make('work_type')->title('Work Type'),
            Column::make('service_type')->title('Service Type'),
            Column::computed('floor_name')->title('Floor Name'),
            Column::computed('action')
                ->exportable(false)
                ->printable(false)
                ->searchable(false),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'JobdeskPatrolls_' . date('YmdHis');
    }
}
