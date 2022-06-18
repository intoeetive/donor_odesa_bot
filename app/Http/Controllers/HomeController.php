<?php

namespace App\Http\Controllers;

use App\Models\BloodType;

use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $locations = Auth::user()->locations()->get();
        $bloodTypes = BloodType::BLOOD_TYPES;

        return view('dashboard', [
            'locations' => $locations,
            'bloodTypes' => $bloodTypes
        ]);
    }
}
