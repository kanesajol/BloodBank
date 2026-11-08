<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

#[Fillable([
    'user_id',
    'shift_name',
    'shift_time_range',
    'shift_type',
    'login_date',
    'login_time',
    'logged_in_at',
    'logout_time',
    'logged_out_at',
    'last_seen_at',
    'status',
    'station',
    'notes',
])]
class StaffShiftRecord extends Model
{
    protected function casts(): array
    {
        return [
            'login_date' => 'date',
            'logged_in_at' => 'datetime',
            'logged_out_at' => 'datetime',
            'last_seen_at' => 'datetime',
        ];
    }

    public function isOpen(): bool
    {
        return $this->logout_time === null || $this->logout_time === '';
    }

    public function loginLabel(): string
    {
        if ($this->logged_in_at) {
            return $this->logged_in_at->format('Y-m-d h:i A');
        }

        $date = $this->login_date?->format('Y-m-d');

        return trim($date.' '.$this->login_time);
    }

    public function logoutLabel(): string
    {
        if ($this->logged_out_at) {
            return $this->logged_out_at->format('Y-m-d h:i A');
        }

        if ($this->logout_time) {
            $date = $this->login_date?->format('Y-m-d');

            return trim($date.' '.$this->logout_time);
        }

        return '';
    }

    public function markOut(Carbon|\DateTimeInterface|string $at): void
    {
        $at = Carbon::parse($at);

        $this->forceFill([
            'logged_out_at' => $at,
            'logout_time' => $at->format('h:i A'),
            'status' => 'Completed',
        ])->save();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
