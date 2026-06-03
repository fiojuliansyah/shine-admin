<?php

namespace App\Models;

use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Generate extends Model
{
    use HasFactory, LogsActivity;
    protected $guarded = [];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
        ->logAll(['*']);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function letter()
    {
        return $this->belongsTo(Letter::class);
    }

    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    public function getFormattedLetterNumberAttribute(): ?string
    {
        if (!$this->sequence_number) {
            return $this->letter_number;
        }

        $letter = $this->letter;
        if (!$letter) {
            return $this->letter_number;
        }

        $letter->loadMissing('type', 'numberConfig');

        if (!$letter->letter_number_config_id && !$letter->number_format) {
            return $this->letter_number;
        }

        $site = $this->site ?: ($letter->site ?? null);
        if ($site) {
            $site->loadMissing('company');
        }

        $user = $this->user;
        if ($user) {
            $user->loadMissing('roles');
        }

        return $letter->generateLetterNumber($this->sequence_number, $site, $user);
    }
}
