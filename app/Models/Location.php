<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Hospital / Location
 */
class Location extends Model
{
    protected $fillable = [
        'name', //Location name (ex. Hospital 1)
        'address',//Address
        'coords',//Coords for Gmap
        'bot_instructions',//instruction to show in bot after response is accepted
    ];

    public function users(): BelongsToMany
    {
        /** @phpstan-ignore-next-line */
        return $this->belongsToMany(User::class, 'user_location');
    }

    public function bloodRequests(): HasMany
    {
        /** @phpstan-ignore-next-line */
        return $this->hasMany(BloodRequest::class);
    }

    public function donorBloodRequestResponses(): HasMany
    {
        /** @phpstan-ignore-next-line */
        return $this->hasMany(DonorBloodRequestResponse::class);
    }
}
