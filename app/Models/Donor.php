<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Donor extends Model
{
    protected $fillable = [
        'name',
        'phone',
        'blood_type',
        'blood_rh',
        'last_donor_date',
        'last_request_date',
        'sheet_row',
    ];

    public function chat(): BelongsTo
    {
        /** @phpstan-ignore-next-line */
        return $this->belongsTo(DonorChat::class, 'chat_id', 'chat_id');
    }
}
