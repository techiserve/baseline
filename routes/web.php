<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BaselineController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});


Route::get('/baseline', [BaselineController::class, 'index'])->name('index');
Route::get('/geofence', [BaselineController::class, 'geofence'])->name('geofence');
Route::get('/timeDifference', [BaselineController::class, 'timeDifference'])->name('timeDifference');
Route::get('/cycleTime', [BaselineController::class, 'cycleTime'])->name('cycleTime');
Route::get('/movingStationary', [BaselineController::class, 'movingStationary'])->name('movingStationary');
Route::get('/truckLogic', [BaselineController::class, 'truckLogic'])->name('truckLogic');
