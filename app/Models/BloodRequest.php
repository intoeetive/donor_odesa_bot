<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Blood request sent by hospital
 */
class BloodRequest extends Model
{
    protected $fillable = [
        'blood_type', //the blood type and RH needeed
        'qty', //number of people / responses needed
        'closed_on', //timestamp when request reached the desired responses number
    ];

    /**
     * User that submitted request
     *
     * @return BelongsTo
     */
    public function owner(): BelongsTo
    {
        /** @phpstan-ignore-next-line */
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * Location / hospital that the request is for
     *
     * @return BelongsTo
     */
    public function location(): BelongsTo
    {
        /** @phpstan-ignore-next-line */
        return $this->belongsTo(Location::class);
    }
}
