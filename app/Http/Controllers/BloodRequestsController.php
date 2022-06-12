<?php

namespace App\Http\Controllers;

use App\Models\BloodRequest;
use App\Models\BloodType;
use App\Models\Location;
use App\Jobs\SendBloodRequest;
use App\Console\Commands\CheckBloodRequestAreClosed;

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
        return view('requests.index');
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

        return view('requests.create', [
            'locations' => $locations,
            'bloodTypes' => $bloodTypes
        ]);
    }

    /**
     * Upon submission, we create job which will create sending jobs (and clone itself, if necessary)
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

        $bloodRequest = BloodRequest::create([
            'blood_type_id' => $input['type'],
            'qty' => $input['qty'],
        ]);
        $bloodRequest->owner_id = auth()->user()->id;
        $bloodRequest->location_id = $input['location_id'];
        $bloodRequest->save();

        $sendRequestCommand = new CheckBloodRequestAreClosed();
        $sendRequestCommand->handle();

        return $request->wantsJson()
                    ? new JsonResponse('', 200)
                    : back()->banner(__('Blood request sent.'));
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
