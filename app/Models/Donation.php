<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['donor_id', 'donated_on', 'blood_type', 'location', 'bags', 'mbd_date', 'serial_number', 'status', 'reason_for_deferral', 'reserved_for_px', 'remarks'])]
class Donation extends Model
{
    protected function casts(): array
    {
        return [
            'donated_on' => 'date',
            'bags' => 'integer',
        ];
    }

    public function donor(): BelongsTo
    {
        return $this->belongsTo(Donor::class);
    }
}
