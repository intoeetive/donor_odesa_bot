<?php

use Illuminate\Support\Facades\Route;
use App\Models\Donor;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\DonorsController;
use App\Http\Controllers\BloodRequestsController;
use App\Http\Controllers\RequestsResponsesController;
use App\Http\Controllers\LocationsController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified'
])->group(function () {
    Route::get('/', [HomeController::class, 'index'])->name('dashboard');
    Route::get('donors/{donor}', function(Donor $donor) {
        return $donor;
    });
    Route::resource('donors', DonorsController::class);
    Route::resource('blood-requests', BloodRequestsController::class);
    Route::resource('request-responses', RequestsResponsesController::class);
    Route::resource('locations', LocationsController::class);
});
