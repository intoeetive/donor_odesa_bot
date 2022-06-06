<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

use App\Jobs\SendBloodRequest;
use App\Models\BloodRequest;
use App\Models\Donor;

/**
 * Checks for blood requests that are not closed
 * and triggers sending requests for the requested quantity (miltiplied)
 */
class CheckBloodRequestAreClosed extends Command
{
    protected $signature = 'donor:send-requests';

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
        $bloodRequests = BloodRequest::whereNull('closed_on')->withCount('responses')->get();
        if (!$bloodRequests->isEmpty()) {
            $this->maxYear = Carbon::now()->year - 18;
            $this->minYear = Carbon::now()->year - 64;
            foreach ($bloodRequests->all() as $bloodRequest) {
                //do we already have sufficient number of responses for this request?
                if ($bloodRequest->responses_count >= $bloodRequest->qty) {
                    $bloodRequest->closed_on = Carbon::now()->toDateTimeString();
                    $bloodRequest->save();
                    continue;
                }
                
                //plan sending another batch of messages
                $this->currentBloodRequest = $bloodRequest;

                //get the donors that fit, and we did not request them yet
                $donors = Donor::whereDoesntHave(
                    'bloodRequests',
                    function (Builder $query) {
                        $query->where('id', $this->currentBloodRequest->id);
                    })
                    ->where('blood_type_id', $this->currentBloodRequest->blood_type_id)
                    ->where('birth_year', '<', $this->maxYear)
                    ->where('birth_year', '>', $this->minYear)
                    ->where('weight_ok', 1)
                    ->where('no_contras', 1)
                    ->where(function ($query) {
                        $query->where('last_donorship_date', '<', Carbon::parse('2 month ago')->format('Y-m-d'))
                            ->orWhereNull('last_donorship_date');
                    })
                    ->limit($this->currentBloodRequest->qty * 3)//we send 3 times more requests than requested
                    ->inRandomOrder()
                    //->toSql();
                    ->get();
                
                if ($donors->isNotEmpty()) {
                    foreach ($donors->all() as $donor) {
                        SendBloodRequest::dispatch($bloodRequest, $donor);
                    }
                }
            }
        }
    }
}
