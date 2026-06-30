<?php

namespace App\Models;

use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SalarySetting extends Model
{
    use HasFactory, LogsActivity;

    protected $guarded = [];

    protected $casts = [
        'gaji_pokok'         => 'decimal:2',
        'tunj_jabatan'       => 'decimal:2',
        'tunj_kehadiran'     => 'decimal:2',
        'tunj_komunikasi'    => 'decimal:2',
        'tunj_makan'         => 'decimal:2',
        'tunj_transport'     => 'decimal:2',
        'tunj_lembur_tetap'  => 'decimal:2',
        'tunj_other_non_fix' => 'decimal:2',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logAll(['*']);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
