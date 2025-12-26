<?php

namespace App\DataTables;

use App\Models\Overtime;
use App\Models\OvertimeRequest;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;

class OvertimeRequestDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('date', function ($row) {
                return optional($row->attendance->date)->format('d M Y') ?? '';
            })
            ->addColumn('user', function ($row) {
                return optional($row->attendance->user)->name ?? '';
            })
            ->addColumn('clock', function ($row) {
                $clock_in = $row->clock_in ? $row->clock_in : '';
                $clock_out = $row->clock_out ? $row->clock_out : '';
                return $clock_in . ' - ' . $clock_out;
            })                
            ->addColumn('backup', function ($row) {
                return optional($row->backup)->name ?? ''; 
            })
            ->addColumn('reason', function ($row) {
                return $row->reason ?? '';
            })
            ->addColumn('demand', function ($row) {
                return $row->demand ?? ''; 
            })
            ->addColumn('status', function ($row) {
                $badgeClass = 'bg-secondary';
                $status = $row->status ?? 'pending';
                
                if ($status === 'approved') {
                    $badgeClass = 'bg-success';
                } elseif ($status === 'rejected') {
                    $badgeClass = 'bg-danger';
                } elseif ($status === 'pending') {
                    $badgeClass = 'bg-warning';
                }
                
                return '<span class="badge ' . $badgeClass . '">' . ucfirst($status) . '</span>';
            })
            ->addColumn('action', function ($row) {
                return view('overtime-request.partials.actions', compact('row'))->render();
            })
            ->rawColumns(['action', 'status'])
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(Overtime $model): QueryBuilder
    {
        return $model->newQuery()->with('attendance.user', 'backup');
    }
    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
                    ->setTableId('overtimerequest-table')
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
            Column::make('date')->title('Tanggal'),
            Column::make('user')->title('Pegawai'),
            Column::make('clock')->title('Jam Lembur'),
            Column::make('backup')->title('Backup'),
            Column::make('reason')->title('Alasan'),
            Column::make('demand')->title('Persetujuan'),
            Column::make('status')->title('Status'),
            Column::computed('action')->exportable(false)->printable(false)->orderable(false)->searchable(false),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'OvertimeRequest_' . date('YmdHis');
    }
}