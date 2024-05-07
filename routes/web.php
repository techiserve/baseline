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
Route::get('/SoapFleetboard', [BaselineController::class, 'SoapFleetboard'])->name('SoapFleetboard');
Route::get('/CumulativeTime', [BaselineController::class, 'CumulativeTime'])->name('CumulativeTime');
Route::get('/truckLogic', [BaselineController::class, 'truckLogic'])->name('truckLogic');
Route::get('/newBase', [BaselineController::class, 'newBase'])->name('newBase');
Route::get('/runBaseline', [BaselineController::class, 'RunBaseline'])->name('RunBaseline');
Route::get('/updateLong', [BaselineController::class, 'updateLong'])->name('updateLong');
Route::get('/BiTripEnd', [BaselineController::class, 'BiTripEnd'])->name('BiTripEnd');
Route::get('/BiTripStart', [BaselineController::class, 'BiTripStart'])->name('BiTripStart');
Route::get('/BiTimeCalculation', [BaselineController::class, 'BiTimeCalculation'])->name('BiTimeCalculation');

Route::get('/BiTripStart2', [BaselineController::class, 'BiTripStart2'])->name('BiTripStart2');
Route::get('/TripTime', [BaselineController::class, 'TripTime'])->name('TripTime');
Route::get('/LoadingTimes', [BaselineController::class, 'LoadingTimes'])->name('LoadingTimes');
Route::get('/TripF1', [BaselineController::class, 'TripF1'])->name('TripF1');
Route::get('/RbayTrips', [BaselineController::class, 'RbayTrips'])->name('RbayTrips');
Route::get('/ShiftClass', [BaselineController::class, 'ShiftClass'])->name('ShiftClass');
Route::get('/TonnesMoved', [BaselineController::class, 'TonnesMoved'])->name('TonnesMoved');
Route::get('/LoadingTimesv2', [BaselineController::class, 'LoadingTimesv2'])->name('LoadingTimesv2');
Route::get('/TripRoute', [BaselineController::class, 'TripRoute'])->name('TripRoute');
Route::get('/loadCapacity', [BaselineController::class, 'loadCapacity'])->name('loadCapacity');
Route::get('/truckmap', [BaselineController::class, 'truckmap'])->name('truckmap');
Route::get('/fleetboardfuel', [BaselineController::class, 'fleetboardfuel'])->name('fleetboardfuel');


Route::get('/TotalDistanceFuel', [BaselineController::class, 'TotalDistanceFuel'])->name('TotalDistanceFuel');
Route::get('/RouteClassification', [BaselineController::class, 'RouteClassification'])->name('RouteClassification');
Route::get('/TimeSpentPercentage', [BaselineController::class, 'TimeSpentPercentage'])->name('TimeSpentPercentage');

Route::get('/FbCartrack', [BaselineController::class, 'FbCartrack'])->name('FbCartrack');
Route::get('/FbCartrackDistanceLink', [BaselineController::class, 'FbCartrackDistanceLink'])->name('FbCartrackDistanceLink');


