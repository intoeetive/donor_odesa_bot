<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Response to blood request
 */
class DonorBloodRequestResponse extends Model
{
    protected $fillable = [
        'no_response_contras', // 1 if there are no contraindications
        'confirmation_date', // when the reponse was confirmed
        'donorship_date' // when the donorship had place
    ];

    public function bloodRequest(): BelongsTo
    {
        /** @phpstan-ignore-next-line */
        return $this->belongsTo(BloodRequest::class);
    }

    public function donor(): BelongsTo
    {
        /** @phpstan-ignore-next-line */
        return $this->belongsTo(Donor::class);
    }
}
