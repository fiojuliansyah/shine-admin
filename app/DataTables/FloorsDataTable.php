<?php

namespace App\DataTables;

use App\Models\Site;
use App\Models\Floor;
use Illuminate\Support\Str;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;

class FloorsDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addIndexColumn()
            ->addColumn('site_id', function ($row) {
                return $row->site->name ?? '';
            })
            ->addColumn('description', function ($row) {
                // return Str::limit($row->desctiption, 5, '...');
                return Str::words($row->description, 5, '...');
            })
            ->addColumn('floor_qr', function($row){
                return view('floors.partials.qr_code', compact('row'))->render();
            })
            ->addColumn('action', function ($row) {
                $sites = Site::all();
                return view('floors.partials.action', compact('row', 'sites'))->render();
            })
            ->rawColumns(['action', 'floor_qr'])
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(Floor $model): QueryBuilder
    {
        $query = $model->newQuery();

        if($this->request()->filled('site_id')){
            $query->where('site_id', $this->request()->get('site_id'));
        }

        return $query;
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('floors-table')
            ->columns($this->getColumns())
            ->minifiedAjax(route('floors.index'). "?site_id=".request('site_id'))
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
            Column::computed('DT_RowIndex.')->title('#'),
            Column::make('name')->title('Name'),
            Column::computed('description')->title('Description'),
            Column::computed('site_id')->title('Site'),
            Column::computed('floor_qr')
                ->title('Code QR')
                ->searchlable(false),
            Column::computed('action')
                ->exportable(false)
                ->printable(false)
                ->searchlable(false)
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'Floors_' . date('YmdHis');
    }
}
