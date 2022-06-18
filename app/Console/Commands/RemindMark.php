<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;

use App\Jobs\RemindMarkJob;
use App\Models\DonorBloodRequestResponse;

/**
 * Checks for responses that have not been marked
 * and triggers sending requests to mark donorship
 */
class RemindMark extends Command
{
    protected $signature = 'donor:remind-mark';

    protected $description = 'Create jobs for blood requests that are not closed';

    protected $currentBloodRequest;
    protected $minYear;
    protected $maxYear;

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        //get all blood requests that are not complete yet
        $responses = DonorBloodRequestResponse::with('donor')->whereNull('donorship_date')->where('confirmation_date', '<', Carbon::now()->subDays(2))->get();
        if (!$responses->isEmpty()) {
            $this->maxYear = Carbon::now()->year - 18;
            $this->minYear = Carbon::now()->year - 64;
            foreach ($responses->all() as $responses) {
                RemindMarkJob::dispatch($responses);
            }
        }
    }
}
