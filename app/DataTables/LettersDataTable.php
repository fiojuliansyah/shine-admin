<?php

namespace App\DataTables;

use App\Models\Letter;
use App\Models\Site;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class LettersDataTable extends DataTable
{
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('site', function ($row) {
                return $row->site->name ?? '';
            })
            ->addColumn('action', function ($row) {
                $sites = Site::all();
                return view('letters.partials.actions', compact('row', 'sites'))->render();
            })
            ->rawColumns(['action'])
            ->setRowId('id');
    }

    public function query(Letter $model): QueryBuilder
    {
        return $model->newQuery()->with('site')
            ->when(request('search')['value'] ?? null, function ($query, $search) {
                return $query->orWhereHas('site', function ($q) use ($search) {
                    $q->where('name', 'like', "%$search%");
                });
            });
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('letters-table')
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

    public function getColumns(): array
    {
        return [
            Column::make('site')->title('Site'),
            Column::make('title')->title('Judul Surat'),
            Column::computed('action')->exportable(false)->printable(false)->orderable(false)->searchable(false),
        ];
    }

    protected function filename(): string
    {
        return 'Letters_' . date('YmdHis');
    }
}

