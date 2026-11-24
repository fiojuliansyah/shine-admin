<?php

namespace App\DataTables;

use App\Models\Site;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class EmployeesDataTable extends DataTable
{
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addIndexColumn()

            ->addColumn('nama', function ($row) {
                return '<strong>' . e($row->name) . '</strong><br>
                        <small class="text-muted">' . e($row->employee_nik ?? '-') . '</small>';
            })

            ->filterColumn('nama', function ($query, $keyword) {
                $query->where(function ($q) use ($keyword) {
                    $q->where('users.name', 'like', "%{$keyword}%")
                      ->orWhere('users.employee_nik', 'like', "%{$keyword}%");
                });
            })

            ->addColumn('site', function ($row) {
                return e($row->site->name ?? '-');
            })

            ->filterColumn('site', function ($query, $keyword) {
                $query->whereHas('site', fn($q) => $q->where('name', 'like', "%{$keyword}%"));
            })

            ->orderColumn('nama', 'users.name $1')
            ->orderColumn('site', function ($query, $order) {
                $query->orderBy(Site::select('name')->whereColumn('sites.id', 'users.site_id'), $order);
            })

            ->addColumn('jabatan', function ($row) {
                return e($row->getRoleNames()->implode(', ') ?: '-');
            })

            ->filterColumn('jabatan', function ($query, $keyword) {
                $query->whereHas('roles', fn($q) => $q->where('name', 'like', "%{$keyword}%"));
            })

            ->addColumn('ttl', function ($row) {
                $bp = $row->profile->birth_place ?? '-';
                $bd = format_tanggal($row->profile?->birth_date);
                return e($bp . ', ' . $bd);
            })

            ->addColumn('alamat', function ($row) {
                $parts = array_filter([
                    $row->profile->address    ?? null,
                    $row->profile->rt_rw      ?? null,
                    $row->profile->kelurahan  ? 'Kel. ' . $row->profile->kelurahan : null,
                    $row->profile->kecamatan  ? 'Kec. ' . $row->profile->kecamatan : null,
                ]);
                return e(implode(', ', $parts) ?: '-');
            })

            ->addColumn('join_date', fn($row) => e(format_tanggal($row->profile?->join_date)))
            ->addColumn('status', function ($row) {
                return $row->profile->isResigned()
                    ? '<span class="badge bg-danger">Resign</span>'
                    : '<span class="badge bg-success">Aktif</span>';
            })
            ->addColumn('no_surat', function ($row) {
                // Tampilkan semua surat pegawai (mis. Penempatan & PKWT), terbaru di atas,
                // supaya surat lama tidak tertutup saat surat baru terbit.
                $lines = $row->generates
                    ->filter(fn($gen) => $gen->letter && $gen->letter->type)
                    ->sortByDesc('created_at')
                    ->map(function ($generate) {
                        $number = $generate->formatted_letter_number;
                        if (!$number) {
                            return null;
                        }
                        $category = $generate->letter->type->name ?? null;
                        return $category
                            ? e($number) . '<br><small class="text-muted">' . e($category) . '</small>'
                            : e($number);
                    })
                    ->filter();

                return $lines->isEmpty()
                    ? '-'
                    : '<div class="text-nowrap">' . $lines->implode('<hr class="my-1">') . '</div>';
            })

            ->addColumn('resume', function ($row) {
                $applicant = $row->applicants->sortByDesc('id')->first();
                return view('admin.employees.partials.actions', ['row' => $row, 'applicant' => $applicant])->render();
            })

            ->rawColumns(['nama', 'status', 'no_surat', 'resume'])
            ->setRowId('id');
    }

    public function query(User $model): QueryBuilder
    {
        $query = $model->newQuery()
            ->with(['profile' => fn($q) => $q->select('user_id', 'birth_place', 'birth_date', 'address', 'rt_rw', 'kelurahan', 'kecamatan', 'join_date', 'resign_date', 'employment_status'), 'roles', 'site', 'generates.letter.type', 'applicants:id,user_id'])
            ->where('is_employee', 1)
            ->whereDoesntHave('roles', fn($q) => $q->where('name', 'App Administrator'));

        if (request()->filled('site_id')) {
            $query->where('site_id', request('site_id'));
        }

        if (request()->filled('company_id')) {
            $companyId = request('company_id');
            $query->whereHas('site', function ($q) use ($companyId) {
                $q->where('company_id', $companyId);
            });
        }

        return $query;
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('employees-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->responsive(true)
            ->orderBy(1)
            ->selectStyleSingle()
            ->buttons([]);
    }

    public function getColumns(): array
    {
        return [
            Column::make('DT_RowIndex')->title('No')->orderable(false)->searchable(false),
            Column::make('nama')->title('Nama / NIK'),
            Column::make('site')->title('Site'),
            Column::make('jabatan')->title('Jabatan')->orderable(false),
            Column::make('ttl')->title('Tempat/Tgl Lahir')->orderable(false)->searchable(false),
            Column::make('alamat')->title('Alamat')->orderable(false)->searchable(false),
            Column::make('join_date')->title('Tgl Masuk')->orderable(false)->searchable(false),
            Column::make('status')->title('Status')->orderable(false)->searchable(false),
            Column::make('no_surat')->title('No Surat')->orderable(false)->searchable(false),
            Column::make('resume')->title('Aksi')->orderable(false)->searchable(false),
        ];
    }

    protected function filename(): string
    {
        return 'Employees_' . date('YmdHis');
    }
}
