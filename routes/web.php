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

Route::get('/BaselineImport', [BaselineController::class, 'BaselineImportCreate'])->name('BaselineImport');
Route::post('/BaselineImport/create', [BaselineController::class, 'BaselineImport'])->name('BaselineImport.create');
Route::get('/baseline', [BaselineController::class, 'index'])->name('index');
Route::get('/geofence', [BaselineController::class, 'geofence'])->name('geofence');
Route::get('/timeDifference', [BaselineController::class, 'timeDifference'])->name('timeDifference');
Route::get('/cycleTime', [BaselineController::class, 'cycleTime'])->name('cycleTime');
Route::get('/movingStationary', [BaselineController::class, 'movingStationary'])->name('movingStationary');
Route::get('/count', [BaselineController::class, 'Count'])->name('Count');
Route::get('/ontheroad', [BaselineController::class, 'OnTheRoad'])->name('OnTheRoad');
Route::get('/tripStart', [BaselineController::class, 'TripStart'])->name('TripStart');
Route::get('/tripTest', [BaselineController::class, 'TripTest'])->name('TripTest');
Route::get('/TripTestUpdated', [BaselineController::class, 'TripTestUpdated'])->name('TripTestUpdated');
Route::get('/tripEnd', [BaselineController::class, 'tripEnd'])->name('tripEnd');
Route::get('/coordinateTest', [BaselineController::class, 'CoordinateTest'])->name('coordinateTest');
Route::get('/longDifference', [BaselineController::class, 'LongDifference'])->name('LongDifference');
Route::get('/latDifference', [BaselineController::class, 'LatDifference'])->name('LatDifference');
Route::get('/CumulativeTime', [BaselineController::class, 'CumulativeTime'])->name('CumulativeTime');
Route::get('/updateLong', [BaselineController::class, 'updateLong'])->name('updateLong');
Route::get('/CumulativeTime', [BaselineController::class, 'CumulativeTime'])->name('CumulativeTime');
Route::get('/truckLogic', [BaselineController::class, 'truckLogic'])->name('truckLogic');
Route::get('/newBase', [BaselineController::class, 'newBase'])->name('newBase');
Route::get('/runBaseline', [BaselineController::class, 'RunBaseline'])->name('RunBaseline');
Route::get('/executeMethods', [BaselineController::class, 'executeMethods'])->name('executeMethods');
Route::get('/BiTripEnd', [BaselineController::class, 'BiTripEnd'])->name('BiTripEnd');
Route::get('/BiTripStart', [BaselineController::class, 'BiTripStart'])->name('BiTripStart');



