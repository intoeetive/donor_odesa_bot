<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use DefStudio\Telegraph\Models\TelegraphChat;
use DefStudio\Telegraph\Keyboard\Button;
use DefStudio\Telegraph\Keyboard\Keyboard;
use Carbon\Carbon;

use App\Models\BloodRequest;
use App\Models\Donor;

class SendBloodRequest implements ShouldQueue
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
        $this->chat
            ->markdown(__('Потрібна саме ваша кров!'))
            ->keyboard(Keyboard::make()->buttons([
                Button::make(__('Хочу і можу!'))->action('respondDonorRequest')->param('blood_request_id', $this->bloodRequest->id)->param('opt_in', 1),
                Button::make(__('Не зможу :('))->action('respondDonorRequest')->param('blood_request_id', $this->bloodRequest->id)->param('opt_in', 0),
            ]))
            ->send();

        //@todo record what has been sent
        $this->donor->bloodRequests()->attach($this->bloodRequest->id);
        $this->donor->bloodRequests()->save($this->bloodRequest);
    }
}
