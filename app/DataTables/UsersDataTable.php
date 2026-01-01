<?php

namespace App\DataTables;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class UsersDataTable extends DataTable
{
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('avatar', function ($row) {
                $avatar = $row->profile->avatar_url ?? '/assets/media/avatars/blank.png';
                return '<img src="'.$avatar.'" class="rounded-circle" width="45">';
            })

            ->addColumn('employee', function ($row) {
                return '<strong>'.$row->name.'</strong><br>
                        <small class="text-muted">'.$row->employee_nik.'</small>';
            })

            ->filterColumn('employee', function ($query, $keyword) {
                $query->where(function ($q) use ($keyword) {
                    $q->where('users.name', 'like', "%{$keyword}%")
                      ->orWhere('users.employee_nik', 'like', "%{$keyword}%");
                });
            })

            ->addColumn('detail', function ($row) {
                $colors = ['bg-primary','bg-success','bg-info','bg-warning','bg-danger','bg-secondary'];
                $html = '';

                foreach ($row->getRoleNames() as $role) {
                    $html .= '<span class="badge '.$colors[array_rand($colors)].' me-1">'.$role.'</span>';
                }

                $html .= '<br><small>'.$row->email.'</small>';
                $html .= '<br><small>Leader: <strong>'.($row->leader->name ?? '-').'</strong></small>';

                return $html;
            })

            ->filterColumn('detail', function ($query, $keyword) {
                $query->where(function ($q) use ($keyword) {
                    $q->where('users.email', 'like', "%{$keyword}%")
                      ->orWhereHas('leader', fn($l) =>
                            $l->where('name', 'like', "%{$keyword}%")
                      )
                      ->orWhereHas('roles', fn($r) =>
                            $r->where('name', 'like', "%{$keyword}%")
                      );
                });
            })

            ->addColumn('site', function ($row) {
                return '<strong>'.$row->site->name.'</strong><br>
                        <small>'.$row->site->company->name.'</small>';
            })

            ->filterColumn('site', function ($query, $keyword) {
                $query->whereHas('site', function ($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%")
                      ->orWhereHas('company', fn($c) =>
                            $c->where('name', 'like', "%{$keyword}%")
                      );
                });
            })

            ->addColumn('status', function ($row) {
                return $row->profile && $row->profile->resign_date
                    ? '<span class="badge bg-danger">Resign</span>'
                    : '<span class="badge bg-success">Active</span>';
            })

            ->filterColumn('status', function ($query, $keyword) {
                if (strtolower($keyword) === 'active') {
                    $query->whereDoesntHave('profile', fn($q) =>
                        $q->whereNotNull('resign_date')
                    );
                }

                if (strtolower($keyword) === 'resign') {
                    $query->whereHas('profile', fn($q) =>
                        $q->whereNotNull('resign_date')
                    );
                }
            })

            ->addColumn('action', function ($row) {
                return view('users.partials.actions', compact('row'))->render();
            })

            ->rawColumns(['avatar','employee','detail','site','status','action'])
            ->setRowId('id');
    }

    public function query(User $model): QueryBuilder
    {
        return $model->newQuery()
            ->with([
                'profile',
                'leader',
                'roles',
                'site.company'
            ])
            ->where('is_employee', 1);
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('users-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->responsive(true)
            ->orderBy(1)
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
            Column::make('avatar')->title('Avatar')->orderable(false)->searchable(false),
            Column::make('employee')->title('Pegawai'),
            Column::make('detail')->title('Detail'),
            Column::make('site')->title('Site'),
            Column::make('status')->title('Status'),
            Column::computed('action')
                ->exportable(false)
                ->printable(false)
                ->orderable(false)
                ->searchable(false),
        ];
    }

    protected function filename(): string
    {
        return 'Users_' . date('YmdHis');
    }
}
