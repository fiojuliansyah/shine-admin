<?php

namespace App\DataTables;

use App\Models\Letter;
use App\Models\Site;
use App\Models\TypeLetter;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class LettersDataTable extends DataTable
{
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        $sites = Site::all();

        return (new EloquentDataTable($query))
            ->addColumn('site', function ($row) {
                return $row->site->name ?? '';
            })
            ->filterColumn('site', function ($query, $keyword) {
                $query->whereHas('site', fn ($q) => $q->where('name', 'like', "%{$keyword}%"));
            })
            ->orderColumn('site', function ($query, $order) {
                $query->orderBy(Site::select('name')->whereColumn('sites.id', 'letters.site_id'), $order);
            })
            ->addColumn('type', function ($row) {
                return $row->type->name ?? '-';
            })
            ->filterColumn('type', function ($query, $keyword) {
                $query->whereHas('type', fn ($q) => $q->where('name', 'like', "%{$keyword}%"));
            })
            ->orderColumn('type', function ($query, $order) {
                $query->orderBy(TypeLetter::select('name')->whereColumn('type_letters.id', 'letters.type_letter_id'), $order);
            })
            ->addColumn('editor', function ($row) {
                return ($row->editor_type ?? 'canvas') === 'text' ? 'Teks' : 'Canvas';
            })
            ->addColumn('updated', function ($row) {
                return format_tanggal($row->updated_at);
            })
            ->addColumn('action', function ($row) use ($sites) {
                return view('admin.letters.partials.actions', compact('row', 'sites'))->render();
            })
            ->rawColumns(['action'])
            ->setRowId('id');
    }

    public function query(Letter $model): QueryBuilder
    {
        return $model->newQuery()->with(['site', 'type'])
            ->when(request('site_id'), fn ($query, $siteId) => $query->where('site_id', $siteId))
            ->when(request('type_id'), fn ($query, $typeId) => $query->where('type_letter_id', $typeId))
            ->when(request('editor_type'), fn ($query, $editor) => $query->where('editor_type', $editor));
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('letters-table')
            ->columns($this->getColumns())
            ->minifiedAjax('', 'data.site_id = $("#filter_site").val(); data.type_id = $("#filter_type").val(); data.editor_type = $("#filter_editor").val();')
            ->orderBy(2, 'asc')
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
            Column::make('site')->title('Site'),
            Column::make('type')->title('Jenis Surat'),
            Column::make('title')->title('Judul Surat'),
            Column::make('editor')->title('Editor')->orderable(false)->searchable(false),
            Column::make('updated')->title('Terakhir Diubah')->orderable(false)->searchable(false),
            Column::computed('action')->exportable(false)->printable(false)->orderable(false)->searchable(false),
        ];
    }

    protected function filename(): string
    {
        return 'Letters_' . date('YmdHis');
    }
}

