<?php

namespace App\Models;

use DefStudio\Telegraph\Models\TelegraphChat;
use Illuminate\Database\Eloquent\Relations\HasOne;

class DonorChat extends TelegraphChat
{
    protected $fillable = [
        'chat_id',
        'name',
        'phone',
        'blood_type',
        'blood_rh',
        'last_donor_date',
        'last_request_date'
    ];

    public function donor(): HasOne
    {
        /** @phpstan-ignore-next-line */
        return $this->hasOne(Donor::class, 'chat_id', 'chat_id');
    }
}
