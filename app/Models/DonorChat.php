<?php

namespace App\Models;

use DefStudio\Telegraph\Models\TelegraphChat;

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
}
