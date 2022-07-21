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

use App\Models\BloodRequest;
use App\Models\Donor;
use App\Models\DonorTelegramChat;

class SendBloodRequestJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $bloodRequest;

    protected $donor;

    protected $chat;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(BloodRequest $bloodRequest, Donor $donor)
    {
        $this->bloodRequest = $bloodRequest->withoutRelations();
        $this->donor = $donor;
    }

    /**
     * Execute the job.
     * send Telegram messages
     *
     * @return void
     */
    public function handle()
    {
        $this->chat = $this->donor->telegramChat;
        if (config('telegraph.debug_mode')) {
            //$this->chat = DonorTelegramChat::find(33);
        }
        if (empty($this->chat)) {
            return;
        }
        $this->chat
            ->markdown(__('messages.message.need_your_blood'))
            ->keyboard(Keyboard::make()->buttons([
                Button::make(__('messages.button.need_your_blood.yes'))->action('respondDonorRequest')->param('blood_request_id', $this->bloodRequest->id)->param('opt_in', 1),
                Button::make(__('messages.button.need_your_blood.no'))->action('respondDonorRequest')->param('blood_request_id', $this->bloodRequest->id)->param('opt_in', 0),
            ]))
            ->send();
        
        //@todo record what has been sent
        $this->donor->bloodRequests()->attach($this->bloodRequest->id);

        if (config('telegraph.debug_mode')) {
            Log::debug('Sent blood request', [
                'id' => $this->bloodRequest->id,
                'donor' => $this->donor->name,
            ]);
        }
    }
}
