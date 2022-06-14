<?php

namespace App\Models;

use DefStudio\Telegraph\Models\TelegraphChat;
use DefStudio\Telegraph\Telegraph;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DonorTelegramChat extends TelegraphChat
{
    public function donor(): BelongsTo
    {
        /** @phpstan-ignore-next-line */
        return $this->belongsTo(Donor::class);
    }

    public function markdown(string $message): Telegraph
    {
        if (config('telegraph.debug_mode')) {
            $message = "БОТ ПРАЦЮЄ В ТЕСТОВОМУ РЕЖИМІ.
" . $message;
        }
        return parent::markdown($message);
    }
}
