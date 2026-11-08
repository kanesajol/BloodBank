<?php

namespace App\Support;

use App\Models\StaffShiftRecord;
use App\Models\User;
use Illuminate\Support\Carbon;

class StaffPresence
{
    public static function open(User $user): StaffShiftRecord
    {
        self::closeOpen($user);

        $shift = BloodBank::shift($user->shift_type);
        $now = now();

        $user->forceFill([
            'last_login' => $now->format('Y-m-d h:i A'),
        ])->save();

        return StaffShiftRecord::query()->create([
            'user_id' => $user->id,
            'shift_name' => $shift['name'],
            'shift_time_range' => $shift['time'],
            'shift_type' => $shift['id'],
            'login_date' => $now->toDateString(),
            'login_time' => $now->format('h:i A'),
            'logged_in_at' => $now,
            'logout_time' => null,
            'logged_out_at' => null,
            'last_seen_at' => $now,
            'status' => 'On Duty',
            'station' => $user->station,
            'notes' => 'Logged in for '.$shift['name'].' ('.$shift['time'].').',
        ]);
    }

    public static function signOut(User $user): void
    {
        self::closeOpen($user, now());
    }

    public static function touch(User $user): void
    {
        $open = self::openRecord($user);

        if ($open) {
            $open->forceFill([
                'last_seen_at' => now(),
                'status' => 'On Duty',
            ])->save();

            return;
        }

        self::open($user);
    }

    public static function closeAbandoned(): void
    {
        $cutoff = now()->subMinutes(2);

        StaffShiftRecord::query()
            ->whereNull('logout_time')
            ->where(function ($query) use ($cutoff) {
                $query->where('last_seen_at', '<', $cutoff)
                    ->orWhere(function ($query) use ($cutoff) {
                        $query->whereNull('last_seen_at')->where('updated_at', '<', $cutoff);
                    });
            })
            ->get()
            ->each(function (StaffShiftRecord $record) {
                $record->markOut($record->last_seen_at ?? $record->updated_at ?? now());
            });
    }

    public static function closeOpen(User $user, ?Carbon $at = null): void
    {
        $open = self::openRecord($user);
        if (! $open) {
            return;
        }

        $open->markOut($at ?? $open->last_seen_at ?? $open->updated_at ?? now());
    }

    private static function openRecord(User $user): ?StaffShiftRecord
    {
        return $user->shiftRecords()
            ->whereNull('logout_time')
            ->latest('id')
            ->first();
    }
}
