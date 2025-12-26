<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ActiveUserExport implements FromCollection, WithHeadings
{
    protected $siteId;

    public function __construct($siteId = null)
    {
        $this->siteId = $siteId;
    }

    public function collection()
    {
        $query = User::with(['profile', 'site'])
            ->whereHas('profile', function ($q) {
                $q->whereNull('resign_date');
            });

        if ($this->siteId) {
            $query->where('site_id', $this->siteId);
        }

        return $query->get()->map(function ($user) {
            return [
                'ID' => $user->id,
                'Name' => $user->name,
                'Email' => $user->email,
                'Site' => $user->site->name ?? '-',
                'Resign Date' => optional($user->profile)->resign_date,
            ];
        });
    }

    public function headings(): array
    {
        return ['ID', 'Name', 'Email', 'Site', 'Resign Date'];
    }
}
