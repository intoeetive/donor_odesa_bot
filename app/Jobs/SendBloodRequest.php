<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use App\Models\BloodRequest;

class SendBloodRequest implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $bloodRequest;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(BloodRequest $bloodRequest)
    {
        $this->bloodRequest = $bloodRequest->withoutRelations();
    }

    /**
     * Execute the job.
     * send Telegram messages
     *
     * @return void
     */
    public function handle()
    {
        //
    }
}
