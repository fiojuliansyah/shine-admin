<?php

namespace App\Models;

use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Status extends Model
{
    use HasFactory, LogsActivity;
    protected $guarded = [];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
        ->logAll(['*']);
    }

    public function applicants()
    {
        return $this->hasMany(Applicant::class, 'status_id');
    }

    public function unapprovedApplicants()
    {
        return $this->applicants()
        ->whereNull('done')
        ->whereNull('approve_id');
    }
}
