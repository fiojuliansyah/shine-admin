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
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('date', function ($row) {
                return $row->date ? $row->date->format('d M Y') : '';
            })

            ->addColumn('user', function ($row) {
                return $row->user->name ?? '';
            })

            ->filterColumn('user', function ($query, $keyword) {
                $query->whereHas('user', function ($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%");
                });
            })

            ->addColumn('site', function ($row) {
                return $row->site->name ?? '';
            })

            ->filterColumn('site', function ($query, $keyword) {
                $query->whereHas('site', function ($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%");
                });
            })

            ->addColumn('clock', function ($row) {
                $clockIn = $row->clock_in ? $row->clock_in->format('H:i') : '';
                $clockOut = $row->clock_out ? $row->clock_out->format('H:i') : '';
                return trim($clockIn.' - '.$clockOut);
            })

            ->filterColumn('clock', function ($query, $keyword) {
                $query->where(function ($q) use ($keyword) {
                    $q->where('clock_in', 'like', "%{$keyword}%")
                      ->orWhere('clock_out', 'like', "%{$keyword}%");
                });
            })

            ->addColumn('image', function ($row) {
                $imagein = $row->face_image_url_clockin ?? '';
                $imageout = $row->face_image_url_clockout ?? '';
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

    public function query(Attendance $model): QueryBuilder
    {
        $query = $model->newQuery()
            ->with(['user', 'site']);

        if (request('site_id')) {
            $query->where('site_id', request('site_id'));
        }

        if (request('type')) {
            $query->where('type', request('type'));
        }

        if (request('start_date') && request('end_date')) {
            $query->whereBetween('date', [
                request('start_date'),
                request('end_date')
            ]);
        } elseif (request('start_date')) {
            $query->whereDate('date', '>=', request('start_date'));
        } elseif (request('end_date')) {
            $query->whereDate('date', '<=', request('end_date'));
        }

        return $query;
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('attendances-table')
            ->columns($this->getColumns())
            ->minifiedAjax(route('attendances.index', request()->query()))
            ->orderBy(0)
            ->selectStyleSingle()
            ->buttons([
                Button::make('excel'),
                Button::make('csv'),
                Button::make('pdf'),
                Button::make('print'),
                Button::make('reset'),
                Button::make('reload'),
            ]);
    }

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
            Column::computed('action')
                ->exportable(false)
                ->printable(false)
                ->orderable(false)
                ->searchable(false),
        ];
    }

    protected function filename(): string
    {
        return 'Attendances_' . date('YmdHis');
    }
}
