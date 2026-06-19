<?php

namespace App\Models;

use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Generate extends Model
{
    use HasFactory, LogsActivity;
    protected $guarded = [];

    protected static function booted(): void
    {
        static::deleting(function (Generate $generate) {
            $generate->resetLetterNumber();
            $generate->resetEmployeeNik();
        });
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
        ->logAll(['*']);
    }

    public function resetLetterNumber(): void
    {
        if (!$this->sequence_number) {
            return;
        }

        $letter = $this->letter()->with('numberConfig.sharedCounter', 'type')->first();
        if (!$letter) {
            return;
        }

        DB::transaction(function () use ($letter) {
            if ($letter->numberConfig) {
                $counter = $letter->numberConfig->shared_counter_id
                    ? LetterNumberConfig::whereKey($letter->numberConfig->shared_counter_id)->lockForUpdate()->first()
                    : LetterNumberConfig::whereKey($letter->numberConfig->id)->lockForUpdate()->first();

                if ($counter && (int) $counter->current_number === (int) $this->sequence_number) {
                    $counter->current_number = max((int) $counter->current_number - 1, 0);
                    $counter->save();
                }
            } elseif ($letter->type && (int) $letter->type->number === (int) $this->sequence_number) {
                $letter->type->update(['number' => max((int) $letter->type->number - 1, 0)]);
            }
        });
    }

    public function resetEmployeeNik(): void
    {
        $user = $this->user()->with('site.company')->first();
        if (!$user || empty($user->employee_nik)) {
            return;
        }

        $companyId = $user->site?->company_id;
        if ($companyId) {
            $nikConfig = EmployeeNikConfig::defaultForCompany($companyId);
            if ($nikConfig) {
                DB::transaction(function () use ($nikConfig) {
                    $row = EmployeeNikConfig::whereKey($nikConfig->id)->lockForUpdate()->first();
                    if ($row && (int) $row->current_number > 0) {
                        $row->current_number = max((int) $row->current_number - 1, 0);
                        $row->save();
                    }
                });
            }
        }

        $user->update(['employee_nik' => null]);
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
