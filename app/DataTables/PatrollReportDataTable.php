<?php

namespace App\DataTables;

use Carbon\Carbon;
use App\Models\Site;
use App\Models\User;
use App\Models\Floor;
use Illuminate\Support\Str;
use App\Models\SecurityPatroll;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;

class PatrollReportDataTable extends DataTable
{
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('date', function ($row) {
                return Carbon::parse($row->created_at)->format('d-M-Y H:i:s') ?? '-';
            })
            ->addColumn('user_id', function ($row) {
                return $row->user->name ?? '-';
            })
            ->addColumn('site_id', function ($row) {
                return $row->site->name ?? '-';
            })
            ->addColumn('floor_id', function ($row) {
                return $row->floor->name ?? '-';
            })
            ->addColumn('patroll_session_id', function ($row) {
                return $row->patroll->turn . " Keliling" ?? '-';
            })
            ->addColumn('image', function ($row) {
                $image = $row->image_url ?? '';
                return $image ? "<a href='" . $image . "' data-lightbox='securty-image'>
                    <img src='" . $image . "' width='300px' style='border-radius:6px'
                </a>" : '';
            })
            ->addColumn('status', function ($row) {
                return $row->status === 'reported' ?
                    '<span class="badge bg-success">Reported</span>'
                    : '<span class="badge bg-danger">Not Reported</span>';
            })
            ->addColumn('description', function ($row) {
                return Str::words($row->description, 4, '...') ?? '-';
            })
            ->addColumn('action', function ($row) {
                $users = User::all();
                $sites = Site::all();
                $floors = Floor::all();
                return view('patroll_report.partials.actions', compact('row', 'users', 'sites', 'floors'))->render();
            })
            ->rawColumns(['image', 'status', 'action'])
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(SecurityPatroll $model): QueryBuilder
    {
        $query =  $model->newQuery()->with(['user', 'site', 'floor']);

        if ($this->request()->filled('user_id')) {
            $query->where('user_id', $this->request()->get('user_id'));
        }

        if ($this->request()->filled('site_id')) {
            $query->where('site_id', $this->request()->get('site_id'));
        }

        if ($this->request()->filled('floor_id')) {
            $query->where('floor_id', $this->request()->get('floor_id'));
        }

        if ($this->request()->filled('date')) {
            $query->whereDate('created_at', $this->request()->get('date'));
        }

        if ($this->request()->filled('patroll-turn')) {
            $turn = $this->request()->get('patroll-turn');
            $query->whereHas('patroll', function ($q) use ($turn){
                $q->where('turn', $turn)->where('date', Carbon::today()->toDateString());
            });
        }

        // 🔥 urut terbaru
        $query->orderBy('created_at', 'desc');
        return $query;
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('patrollreport-table')
            ->columns($this->getColumns())
            ->minifiedAjax(
                route('patrollReport.index')
                    . "?patroll-turn=" . request('patroll-turn', '')
                    . "&date=" . request('date', '')
                    . "&user_id=" . request('user_id', '')
                    . "&site_id=" . request('site_id', '')
                    . "&floor_id=" . request('floor_id', '')
            )
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
            Column::computed('date')->title('Tanggal')
                ->orderable(true) // pastikan ini true
                ->searchable(true),
            Column::computed('image')->title('Foto Bukti')
                ->searchable(false)
                ->orderable(false),
            Column::computed('user_id')->title('Pegawai'),
            Column::computed('site_id')->title('Site'),
            Column::computed('floor_id')->title('Floor'),
            Column::computed('patroll_session_id')->title('patroli keliling'),
            Column::make('name')->title('Title'),
            Column::computed('status')->title('Status'),
            Column::computed('description')->title('Deskripsi'),
            Column::computed(('action'))->title('Action')
                ->searchable(false)
                ->orderable(false)
                ->printable(false)
                ->exportable(false)
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'PatrollReport_' . date('YmdHis');
    }
}
