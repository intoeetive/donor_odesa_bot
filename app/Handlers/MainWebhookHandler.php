<?php

namespace App\Handlers;

use DefStudio\Telegraph\Handlers\WebhookHandler;
use DefStudio\Telegraph\Models\TelegraphBot;
use Illuminate\Http\Request;

use App\Handlers\DonorWebhookHandler;

class MainWebhookHandler extends WebhookHandler
{
    public function handle(Request $request, TelegraphBot $bot): void
    {
        app(DonorWebhookHandler::class)->handle($request, $bot);
        /*match ($bot->name) {
            'user_bot' => app(MyWebhookHandler::class)->handle($request, $bot),
            'admin_bot' => app(MyAdminWebhookHandler::class)->handle($request, $bot),
            default => throw new Exception('unsupported bot');
       }*/
    }
}
