<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use DefStudio\Telegraph\Models\TelegraphChat;
use DefStudio\Telegraph\Keyboard\Button;
use DefStudio\Telegraph\Keyboard\Keyboard;
use Carbon\Carbon;

use App\Models\DonorBloodRequestResponse;
use App\Models\Donor;
use App\Models\DonorTelegramChat;

class RemindMarkJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $response;

    protected $chat;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(DonorBloodRequestResponse $response)
    {
        $this->response = $response;
    }

    /**
     * Execute the job.
     * send Telegram messages
     *
     * @return void
     */
    public function handle()
    {
        $this->chat = $this->response->donor->telegramChat;
        if (config('telegraph.debug_mode')) {
            $this->chat = DonorTelegramChat::find(33);
        }
        if (empty($this->chat)) {
            return;
        }
        $this->chat
            ->markdown(__('messages.message.did_you_donate_blood'))
            ->keyboard(Keyboard::make()->buttons([
                Button::make(__('messages.button.yes_i_did_donate'))->action('confirmDonorship')->param('blood_request_response_id', $this->response->id)->param('confirm', 1),
                Button::make(__('messages.button.no_i_did_not_donate'))->action('confirmDonorship')->param('blood_request_response_id', $this->response->id)->param('confirm', 0),
            ]))
            ->send();

        if (config('telegraph.debug_mode')) {
            Log::debug('Confirmed donorship', [
                'responseId' => $this->response->id,
                'donor' => $this->response->donor->name,
            ]);
        }
    }
}
