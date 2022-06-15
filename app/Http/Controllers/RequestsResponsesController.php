<?php

namespace App\Http\Controllers;

use App\Models\BloodType;
use App\Models\DonorBloodRequestResponse;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class RequestsResponsesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('responses.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $locations = Location::query()->get();
        $locations = Auth::user()->locations()->get();
        $bloodTypes = BloodType::BLOOD_TYPES;

        return view('requests.create', [
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
        return $request->wantsJson()
                    ? new JsonResponse('', 200)
                    : back()->with('status', 'two-factor-authentication-enabled');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\DonorBloodRequestResponse  $donorBloodRequestResponse
     * @return \Illuminate\Http\Response
     */
    public function show(DonorBloodRequestResponse $donorBloodRequestResponse)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\DonorBloodRequestResponse  $donorBloodRequestResponse
     * @return \Illuminate\Http\Response
     */
    public function edit(DonorBloodRequestResponse $donorBloodRequestResponse)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\DonorBloodRequestResponse  $donorBloodRequestResponse
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, DonorBloodRequestResponse $donorBloodRequestResponse)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\DonorBloodRequestResponse  $donorBloodRequestResponse
     * @return \Illuminate\Http\Response
     */
    public function destroy(DonorBloodRequestResponse $donorBloodRequestResponse)
    {
        //
    }
}
