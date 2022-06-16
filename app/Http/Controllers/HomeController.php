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
