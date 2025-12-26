<?php

namespace App\DataTables;

use App\Models\Permit;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class PermitsDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('date', function ($row) {
                $start = optional($row->start_date)->format('d M Y') ?? '';
                $end = optional($row->end_date)->format('d M Y') ?? '';
                return $start . ' - ' . $end;
            })
            ->addColumn('user', function ($row) {
                return optional($row->user)->name ?? '';
            })
            ->addColumn('site', function ($row) {
                return optional($row->site)->name ?? '';
            })
            ->addColumn('image', function ($row) {
                $image = $row->image_url ?? '';
                return $image ? '<img src="' . $image . '" width="70px">' : '';
            })
            ->addColumn('is_paid', function ($row) {
                return $row->is_paid ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-danger">No</span>';
            })
            ->addColumn('status', function ($row) {
                switch($row->status) {
                    case 'approved':
                        $class = 'bg-success';
                        break;
                    case 'rejected':
                        $class = 'bg-danger';
                        break;
                    case 'pending':
                    default:
                        $class = 'bg-warning';
                }
                return '<span class="badge '.$class.'">'.ucfirst($row->status).'</span>';
            })
            ->addColumn('action', function ($row) {
                return view('attendances.permits.partials.actions', compact('row'))->render();
            })
            ->rawColumns(['image', 'is_paid', 'action', 'status'])
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(Permit $model): QueryBuilder
    {
        $query = $model->newQuery()->with('user', 'site');
        
        // Filter by date
        if ($this->request()->has('date') && !empty($this->request()->date)) {
            $date = $this->request()->date;
            $query->where(function($q) use ($date) {
                $q->whereDate('start_date', '<=', $date)
                  ->whereDate('end_date', '>=', $date);
            });
        }
        
        // Filter by employee/user
        if ($this->request()->has('user_id') && !empty($this->request()->user_id)) {
            $query->where('user_id', $this->request()->user_id);
        }
        
        // Filter by site
        if ($this->request()->has('site_id') && !empty($this->request()->site_id)) {
            $query->where('site_id', $this->request()->site_id);
        }

        // Filter by status
        if ($this->request()->has('status') && !empty($this->request()->status)) {
            $query->where('status', $this->request()->status);
        }
        
        return $query;
    }
    /**
     * Optional method if you want to use the HTML builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('leaves-table')
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
            Column::make('date')->title('Tanggal'),
            Column::make('title')->title('Judul'),
            Column::make('user')->title('Pegawai'),
            Column::make('site')->title('Site'),
            Column::make('reason')->title('Alasan'),
            Column::computed('image')->title('Foto')->searchable(false),
            Column::computed('is_paid')->title('Is Paid')->exportable(false)->printable(false),
            Column::make('contact')->title('Contact'),
            Column::computed('status')->title('Status')->exportable(false)->printable(false),
            Column::computed('action')->exportable(false)->printable(false)->orderable(false)->searchable(false),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'Permits_' . date('YmdHis');
    }
}

