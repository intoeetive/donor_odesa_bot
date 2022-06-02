<?php

namespace App\Http\Controllers;

use App\Models\DonorBloodRequestResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

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
        //
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
