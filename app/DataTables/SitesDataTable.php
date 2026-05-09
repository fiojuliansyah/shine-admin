<?php

namespace App\DataTables;

use App\Models\Site;
use App\Models\Company;
use Illuminate\Http\Request;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Services\DataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;

class SitesDataTable extends DataTable
{
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('company', function ($row) {
                return $row->company->name ?? '-';
            })
            ->addColumn('action', function ($row) {
                $companies = Company::all();
                return view('admin.sites.partials.actions', compact('row', 'companies'))->render();
            })
            ->rawColumns(['action'])
            ->setRowId('id');
    }

    public function query(Site $model, Request $request): QueryBuilder
    {
        $query = $model->newQuery()->with('company');

        if ($request->filled('company_id')) {
            $query->where('company_id', $request->company_id);
        }

        return $query;
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('sites-table')
            ->columns($this->getColumns())
            ->minifiedAjax('', 'data.company_id = $("#filter_company").val();')
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
            Column::make('id')
                ->title('#')
                ->render('meta.row + meta.settings._iDisplayStart + 1'),
            Column::make('company')
                ->title('Nama Perusahaan'),
            Column::make('name')
                ->title('Nama Project'),
            Column::make('client_name')
                ->title('Nama Management'),
            Column::make('client_position')
                ->title('Jabatan Client'),
            Column::computed('action')
                ->exportable(false)
                ->printable(false)
                ->orderable(false)
                ->searchable(false),
        ];
    }

    protected function filename(): string
    {
        return 'Sites_' . date('YmdHis');
    }
}