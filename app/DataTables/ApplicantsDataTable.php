<?php

namespace App\DataTables;

use App\Models\Applicant;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class ApplicantsDataTable extends DataTable
{
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->filterColumn('name', function($query, $keyword) {
                $query->whereHas('user', function($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%");
                });
            })
            ->filterColumn('career', function($query, $keyword) {
                $query->whereHas('career', function($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%");
                });
            })
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
            ->addColumn('created_at', function ($row) {
                return $row->created_at
                    ? $row->created_at->format('d M Y H:i')
                    : '-';
            })
            ->addColumn('resume', function ($row) {
                return '<a href="' . route('applicants.resume', $row->id) . '" class="btn btn-sm btn-white d-inline-flex align-items-center">
                            <i class="ti ti-file-description me-1"></i> Lihat Resume
                        </a>';
            })
            ->addColumn('action', function ($row) {
                return view('admin.applicants.partials.actions', compact('row'))->render();
            })
            ->rawColumns(['progress', 'resume', 'action'])
            ->setRowId('id');
    }

    public function query(Applicant $model): QueryBuilder
    {
        $query = $model->newQuery()
            ->with(['user', 'career'])
            ->where('status_id', 0)
            ->whereNull('done')
            ->latest();

        if (request()->filled('start_date') && request()->filled('end_date')) {
            $query->whereBetween('created_at', [
                request('start_date') . ' 00:00:00',
                request('end_date') . ' 23:59:59'
            ]);
        }

        return $query;
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('applicants-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(1, 'desc')
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
                ->render('meta.row + meta.settings._iDisplayStart + 1')
                ->orderable(false)
                ->searchable(false),
            Column::make('created_at')->title('Tanggal Daftar'),
            Column::make('name')->title('Nama Pelamar')->name('user.name'),
            Column::make('career')->title('Lowongan')->name('career.name'),
            Column::make('progress')->title('Progress'),
            Column::make('resume')->title('Resume'),
            Column::computed('action')
                ->title('Aksi')
                ->exportable(false)
                ->printable(false)
                ->orderable(false)
                ->searchable(false),
        ];
    }

    protected function filename(): string
    {
        return 'Applicants_' . date('YmdHis');
    }
}