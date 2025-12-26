<?php

namespace App\DataTables;

use App\Models\Attendance;
use App\Models\User;
use App\Models\Site;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class AttendancesDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('date', function ($row) {
                return $row->date->format('d M Y') ?? '';
            })
            ->addColumn('user', function ($row) {
                return $row->user->name ?? '';
            })
            ->addColumn('site', function ($row) {
                return $row->site->name ?? '';
            })
            ->addColumn('clock', function ($row) {
                $clock_in = $row->clock_in ? $row->clock_in->format('H:i') : '';
                $clock_out = $row->clock_out ? $row->clock_out->format('H:i') : '';
                return $clock_in . ' - ' . $clock_out;
            })
            ->addColumn('image', function ($row) {
                $imagein = $row->face_image_url_clockin ? $row->face_image_url_clockin : '';
                $imageout = $row->face_image_url_clockout ? $row->face_image_url_clockout : '';
                return view('attendances.partials.image', compact('imagein', 'imageout', 'row'))->render();
            })
            ->addColumn('action', function ($row) {
                $users = User::all();
                $sites = Site::all();
                return view('attendances.partials.actions', compact('row', 'users', 'sites'))->render();
            })
            ->rawColumns(['image', 'action'])
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(Attendance $model): QueryBuilder
    {
        $query = $model->newQuery()->with('user', 'site');
        
        // Apply site filter
        if ($this->site_id) {
            $query->where('site_id', $this->site_id);
        }
        
        // Apply attendance type filter
        if ($this->type) {
            $query->where('type', $this->type);
        }
        
        // Apply date range filter
        if ($this->start_date && $this->end_date) {
            $query->whereBetween('date', [$this->start_date, $this->end_date]);
        } elseif ($this->start_date) {
            $query->whereDate('date', '>=', $this->start_date);
        } elseif ($this->end_date) {
            $query->whereDate('date', '<=', $this->end_date);
        }
        
        return $query;
    }

    /**
     * Optional method if you want to use the HTML builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('attendances-table')
            ->columns($this->getColumns())
            ->minifiedAjax(route('attendances.index') . '?site_id=' . request('site_id', '') . '&type=' . request('type', '') . '&start_date=' . request('start_date', '') . '&end_date=' . request('end_date', ''))
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
            Column::make('latlong')->title('Latlong'),
            Column::make('user')->title('Pegawai'),
            Column::make('site')->title('Site'),
            Column::make('clock')->title('Jam Kerja'),
            Column::make('type')->title('Tipe'),
            Column::computed('image')->title('Foto')->searchable(false),
            Column::computed('action')->exportable(false)->printable(false)->orderable(false)->searchable(false),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'Attendances_' . date('YmdHis');
    }
}