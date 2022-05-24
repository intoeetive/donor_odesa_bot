<?php

namespace App\Handlers;

use DefStudio\Telegraph\Handlers\WebhookHandler;
use DefStudio\Telegraph\Keyboard\Button;
use DefStudio\Telegraph\Keyboard\Keyboard;

class DonorWebhookHandler extends WebhookHandler
{
    public function start()
    {
        $this->chat
            ->markdown("Ласкаво просимо до бота *Донор Одеса*")
            ->keyboard(Keyboard::make()->buttons([
                Button::make('Реєстрація донора')->action('delete')->param('id', '42'),
                Button::make('Я вже реєструвався')->url('https://test.it'),
            ]))
            ->send();
    }
}
