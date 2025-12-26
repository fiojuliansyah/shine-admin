<?php

namespace App\DataTables;

use App\Models\Applicant;
use App\Models\Status;
use App\Models\Document;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class ApplicantsDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('name', function ($row) {
                return $row->user->name ?? '';
            })
            ->addColumn('career', function ($row) {
                return $row->career->name ?? '';
            })
            ->addColumn('progress', function ($row) {
                return $row->done === 'done'
                    ? '<span class="badge bg-success">Selesai</span>'
                    : '<span class="badge bg-warning">Menunggu</span>';
            })
            ->addColumn('resume', function ($row) {
                $statuses = Status::all();
                $documents = Document::where('user_id', $row->user->id)->get();
                return view('applicants.partials.resume', compact('row', 'statuses', 'documents'))->render();
            })
            ->addColumn('action', function ($row) {
                return view('applicants.partials.actions', compact('row'))->render();
            })
            ->rawColumns(['action', 'progress', 'resume'])
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(Applicant $model): QueryBuilder
    {
        return $model->newQuery()
            ->with('user', 'career')
            ->where('status_id', 0)
            ->whereNull('done');
    }

    /**
     * Optional method if you want to use the HTML builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
                    ->setTableId('applicants-table')
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
            Column::make('id')
                ->title('#')
                ->render('meta.row + meta.settings._iDisplayStart + 1'),
            Column::make('name')->title('Applicant Name'),
            Column::make('career')->title('Career'),
            Column::make('progress')->title('Progress'),
            Column::make('resume')->title('Resume'),
            Column::computed('action')
                ->exportable(false)
                ->printable(false)
                ->orderable(false)
                ->searchable(false),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'Applicants_' . date('YmdHis');
    }
}
