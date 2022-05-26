<?php

namespace App\Models;

use DefStudio\Telegraph\Models\TelegraphChat;

class DonorChat extends TelegraphChat
{
    protected $fillable = [
        'chat_id',
        'name',
    ];
}
