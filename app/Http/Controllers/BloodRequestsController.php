<?php

namespace App\Http\Controllers;

use App\Models\BloodRequest;
use App\Models\BloodType;
use App\Models\Location;
use App\Jobs\SendBloodRequest;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class BloodRequestsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $locations = Location::query()->get();
        $bloodTypes = BloodType::BLOOD_TYPES;
        
        return view('blood_requests.create', [
            'locations' => $locations,
            'bloodTypes' => $bloodTypes
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $input = $request->all();
        /*Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
        ])->validate();*/

        return DB::transaction(function () use ($input) {
            return tap(BloodRequest::create([
                'location_id' => $input['location_id'],
                'blood_type_id' => $input['blood_type_id'],
                'qty' => $input['qty'],
            ]), function (BloodRequest $bloodRequest) {
                $sendRequestJob = new SendBloodRequest($bloodRequest);
                dispatch($sendRequestJob);
            });
        });

        return $request->wantsJson()
                    ? new JsonResponse('', 200)
                    : back()->with('status', 'two-factor-authentication-enabled');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\BloodRequest  $bloodRequest
     * @return \Illuminate\Http\Response
     */
    public function show(BloodRequest $bloodRequest)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\BloodRequest  $bloodRequest
     * @return \Illuminate\Http\Response
     */
    public function edit(BloodRequest $bloodRequest)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\BloodRequest  $bloodRequest
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, BloodRequest $bloodRequest)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\BloodRequest  $bloodRequest
     * @return \Illuminate\Http\Response
     */
    public function destroy(BloodRequest $bloodRequest)
    {
        //
    }
}
