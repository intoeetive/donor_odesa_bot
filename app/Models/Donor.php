<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Donor
 */
class Donor extends Model
{
    protected $fillable = [
        'name', //Contact name
        'phone', //Telephone number
        'blood_type_id',
        'birth_year',//Year of birth to calculate age
        'weight_ok',// 1 if weight is above 55 kg
        'no_contras', // 1 if there are no contraindications
        'last_donorship_date' //Date of last donorship
    ];

    public function telegramChat(): HasOne
    {
        /** @phpstan-ignore-next-line */
        return $this->hasOne(DonorTelegramChat::class);
    }

    public function requestResponses(): HasMany
    {
        /** @phpstan-ignore-next-line */
        return $this->hasMany(DonorBloodRequestResponse::class);
    }

    /**
     * Blood request notifications sent to this donor
     *
     * @return BelongsToMany
     */
    public function bloodRequests(): BelongsToMany
    {
        /** @phpstan-ignore-next-line */
        return $this->belongsToMany(BloodRequest::class, 'blood_request_donors');
    }
}
