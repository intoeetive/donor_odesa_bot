<?php

namespace App\Models;

use DefStudio\Telegraph\Models\TelegraphChat;
use Illuminate\Database\Eloquent\Relations\HasOne;

class DonorTelegramChat extends TelegraphChat
{
    public function donor(): HasOne
    {
        /** @phpstan-ignore-next-line */
        return $this->hasOne(Donor::class, 'donor_id');
    }
}
