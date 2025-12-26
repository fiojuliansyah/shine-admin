<?php

namespace App\DataTables;

use App\Models\Site;
use App\Models\User;
// use App\Models\ReportDaily;
use Illuminate\Support\Str;
use App\Models\TaskProgress;
use Carbon\Carbon;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Services\DataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;

class ReportDailyDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('date', function ($row) {
                //     // Carbon otomatis bisa format hari dalam bahasa Inggris
                //     // Untuk bahasa Indonesia bisa pakai locale
                return \Carbon\Carbon::parse($row->date)->locale('id')->isoFormat('dddd Y-M-d'); // Senin, Selasa, ...
            })

            ->addColumn('user', function ($row) {
                return $row->user->name ?? '-';
            })
            ->addColumn('site', function ($row) {
                return $row->site->name ?? '-';
            })
            ->addColumn('image', function ($row) {
                $image_before_url = $row->image_before_url ? $row->image_before_url : '';
                $image_after_url = $row->image_after_url ? $row->image_after_url : '';
                return view('dailyReport.partials.image', compact('image_before_url', 'image_after_url', 'row'))->render();
            })
            ->addColumn('detail', function ($row) {
                $image_before_url = $row->image_before_url ? $row->image_before_url : '';
                $image_after_url = $row->image_after_url ? $row->image_after_url : '';
                $start_time = !empty($row->start_time) ? Carbon::parse($row->start_time)->toTimeString() : '-';
                $end_time = !empty($row->end_time) ? Carbon::parse($row->end_time)->toTimeString() : '-';
                return view('dailyReport.partials.detail', compact('start_time', 'end_time', 'image_before_url', 'image_after_url', 'row'))->render();
            })
            ->editColumn('is_worked', function ($row) {
                return $row->is_worked === 'worked'
                    ? '<span class="badge bg-success">Worked</span>'
                    : '<span class="badge bg-danger">Not Worked</span>';
            })
            ->editColumn('progress_description', function ($row) {
                return Str::words($row->progress_description, 5, '...');;
            })
            ->addColumn('action', function ($row) {
                $users = User::all();
                $sites = Site::all();
                return view('dailyReport.partials.actions', compact('row', 'users', 'sites'))->render();
            })
            ->rawColumns(['image', 'is_worked', 'action', 'detail']) // biar HTML badge & img tampil
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(TaskProgress $model): QueryBuilder
    {
        $query = $model->newQuery()->with(['user', 'site']);

        // filters
        if ($this->request()->filled('date')) {
            $query->whereDate('date', $this->request()->get('date'));
        }

        if ($this->request()->filled('user_id')) {
            $query->where('user_id', $this->request()->get('user_id'));
        }

        if ($this->request()->filled('site_id')) {
            $query->where('site_id', $this->request()->get('site_id'));
        }

        if ($this->request()->filled('is_worked')) {
            $query->where('is_worked', $this->request()->get('is_worked'));
        }

        // urutkan terbaru berdasarkan kolom `date`
        $query->orderBy('date', 'desc');


        return $query;
    }

    /**
     * HTML builder
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('reportdaily-table')
            ->columns($this->getColumns())
            ->minifiedAjax(
                route('dailyReport.index')
                    . "?date=" . request('date', '')
                    . "&user_id=" . request('user_id', '')
                    . "&site_id=" . request('site_id', '')
                    . "&is_worked=" . request('is_worked', '')
            )
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
     * Columns definition
     */
    public function getColumns(): array
    {
        return [
            Column::computed('date')->title('Tanggal'),
            Column::computed('image')->title('Foto')->searchable(false)->orderable(false),
            Column::computed('user')->title('Pegawai'),
            Column::computed('site')->title('Site'),
            Column::make('is_worked')->title('Status'),
            Column::computed('progress_description')->title('Keterangan'),
            Column::make('detail')->title('detail')->searchable(false)->orderable(false),
            Column::computed('action')
                ->exportable(false)
                ->printable(false)
                ->orderable(false)
                ->searchable(false),
        ];
    }

    /**
     * Export filename
     */
    protected function filename(): string
    {
        return 'ReportDaily_' . date('YmdHis');
    }
}
