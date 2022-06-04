<?php

namespace App\Models;

use DefStudio\Telegraph\Models\TelegraphChat;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DonorTelegramChat extends TelegraphChat
{
    public function donor(): BelongsTo
    {
        /** @phpstan-ignore-next-line */
        return $this->belongsTo(Donor::class, 'chat_id');
    }
}
