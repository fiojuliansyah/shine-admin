<?php

namespace App\DataTables;

use App\Models\FindingsReport;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class FindingsReportDataTable extends DataTable
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
                return $row->date ? \Carbon\Carbon::parse($row->date)->format('d M Y') : '-';
            })
            ->addColumn('user', function ($row) {
                return optional($row->user)->name ?? '';
            })
            ->addColumn('site', function ($row) {
                return optional($row->site)->name ?? '';
            })
            ->addColumn('image', function ($row) {
                $image = $row->image_url ?? '';
                return $image
                    ? '<a href="' . $image . '" data-lightbox="report-image">
                <img src="' . $image . '" width="300px" style="border-radius:6px;" />
           </a>'
                    : '';
            })

            ->addColumn('action', function ($row) {
                $users = \App\Models\User::all();
                $sites = \App\Models\Site::all();

                return view('findingsreport.partials.actions', compact('row', 'users', 'sites'))->render();
            })
            ->rawColumns(['image', 'action'])
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(FindingsReport $model): QueryBuilder
    {
        $query = $model->newQuery()->with(['user', 'site']);

        // karena ajax kirim data jika ksosong itu '' atau null. maka if/filter tidak jalankan
        // filled() akan otomatis return false jika isinya '' atau null, meskipun parameternya ada.
        if ($this->request()->filled('date')) {
            $query->whereDate('date', $this->request()->get('date'));
        }

        if ($this->request()->filled('type')) {
            $query->where('type', $this->request()->get('type'));
        }

        if ($this->request()->filled('user_id')) {
            $query->where('user_id', $this->request()->get('user_id'));
        }

        if ($this->request()->filled('site_id')) {
            $query->where('site_id', $this->request()->get('site_id'));
        }

        if ($this->request()->filled('status')) {
            $query->where('status', $this->request()->get('status'));
        }

        return $query;
    }


    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('findingsreport-table')
            ->columns($this->getColumns())
            ->ajax([
                'url' => route('findingReport.index'),
                'type' => 'GET',
                'data' => 'function(d) {
                d.date = $("input[name=date]").val();
                d.type = $("select[name=type]").val();
                d.user_id = $("select[name=user_id]").val();
                d.site_id = $("select[name=site_id]").val();
                d.status = $("select[name=status]").val();
                console.log("Data dikirim ke AJAX:", d); // DEBUG: tampilkan di browser console
            }'
            ])
            ->orderBy(1)
            ->selectStyleSingle();
    }



    /**
     * Get the dataTable columns definition.
     */
    public function getColumns(): array
    {
        return [
            Column::make('date')->title('tanggal'),
            Column::computed('image')->title('Foto Bukti')->searchable(false),
            Column::make('type')->title('Tipe'),
            Column::make('user')->title('Pegawai'),
            Column::make('site')->title('Site'),
            Column::make('status')->title('Status'),
            Column::make('description')->title('Deskripsi'),
            Column::make('location')->title('Lokasi'),
            Column::make('direct_action')->title('Tindakan'),
            Column::computed('action')->exportable(false)->printable(false)->orderable(false)->searchable(false),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'FindingsReport_' . date('YmdHis');
    }
}
