<?php

namespace App\DataTables;

use App\Models\GeneratePayroll;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;
use Carbon\Carbon;

class PayrollGenerateDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('site_name', function ($row) {
                return optional($row->site)->name;
            })
            ->addColumn('end_date', function ($row) {
                return Carbon::parse($row->end_date)->format('F Y');
            })
            ->addColumn('action', function ($row) {
                $deleteUrl = route('payroll.generate.destroy', ['site_id' => $row->site_id, 'period' => $row->end_date]);
                return '
                    <a href="' . route('payroll.generateDetail', ['id' => $row->site_id, 'period' => $row->end_date]) . '" class="btn btn-primary btn-sm">View</a>
                    <a href="' . route('payroll.generatePayslip', ['id' => $row->site_id, 'period' => $row->end_date]) . '" class="btn btn-success btn-sm">Payslip</a>
                    <button type="button" class="btn btn-danger btn-sm deletePayroll"
                        data-site="' . optional($row->site)->name . '" 
                        data-date="' . Carbon::parse($row->end_date)->format('F Y') . '" 
                        data-url="' . $deleteUrl . '">
                        Delete
                    </button>
                ';
            })
            ->rawColumns(['action'])
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(GeneratePayroll $model): QueryBuilder
    {
        return $model->newQuery()
            ->with('site')
            ->select('site_id', 'end_date')
            ->groupBy('site_id', 'end_date')
            ->orderBy('end_date', 'desc');
    }

    /**
     * Optional method if you want to use the HTML builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('payroll-generate-table')
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
            Column::make('site_name')->title('Site Name'),
            Column::make('end_date')->title('Period (End Date)'),
            Column::computed('action')->exportable(false)->printable(false)->orderable(false)->searchable(false),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'PayrollGenerate_' . date('YmdHis');
    }
}

