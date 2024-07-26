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

//power to sql 
Route::get('/PowerBiRunBaseline', [BaselineController::class, 'PowerBiRunBaseline'])->name('PowerBiRunBaseline');
Route::get('/Route', [BaselineController::class, 'Route'])->name('Route');
Route::get('/TimeDifferenceMins', [BaselineController::class, 'TimeDifferenceMins'])->name('TimeDifferenceMins');
Route::get('/GeofenceWithRBayClass', [BaselineController::class, 'GeofenceWithRBayClass'])->name('GeofenceWithRBayClass');
Route::get('/GFupdated11', [BaselineController::class, 'GFupdated11'])->name('GFupdated11');
Route::get('/GFNew11', [BaselineController::class, 'GFNew11'])->name('GFNew11');
Route::get('/Classification11', [BaselineController::class, 'Classification11'])->name('Classification11');
Route::get('/ClassNew11', [BaselineController::class, 'ClassNew11'])->name('ClassNew11');
Route::get('/TripClassification', [BaselineController::class, 'TripClassification'])->name('TripClassification');
//

Route::get('/updateLong', [BaselineController::class, 'updateLong'])->name('updateLong');
Route::get('/SoapFleetboard', [BaselineController::class, 'SoapFleetboard'])->name('SoapFleetboard');
Route::get('/CumulativeTime', [BaselineController::class, 'CumulativeTime'])->name('CumulativeTime');
Route::get('/truckLogic', [BaselineController::class, 'truckLogic'])->name('truckLogic');
Route::get('/newBase', [BaselineController::class, 'newBase'])->name('newBase');
Route::get('/runBaseline', [BaselineController::class, 'RunBaseline'])->name('RunBaseline');
//Route::get('/updateLong', [BaselineController::class, 'updateLong'])->name('updateLong');
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
Route::get('/Dates', [BaselineController::class, 'Dates'])->name('Dates');


Route::get('/TotalDistanceFuel', [BaselineController::class, 'TotalDistanceFuel'])->name('TotalDistanceFuel');
Route::get('/RouteClassification', [BaselineController::class, 'RouteClassification'])->name('RouteClassification');
Route::get('/TimeSpentPercentageDeadruns', [BaselineController::class, 'TimeSpentPercentageDeadruns'])->name('TimeSpentPercentageDeadruns');
Route::get('/TimeSpentPercentageOffloading', [BaselineController::class, 'TimeSpentPercentageOffloading'])->name('TimeSpentPercentageOffloading');

Route::get('/FbCartrack', [BaselineController::class, 'FbCartrack'])->name('FbCartrack');
Route::get('/FbCartrackDistanceLink', [BaselineController::class, 'FbCartrackDistanceLink'])->name('FbCartrackDistanceLink');
Route::get('/FleetboardTripDataDistance', [BaselineController::class, 'FleetboardTripDataDistance'])->name('FleetboardTripDataDistance');
Route::get('/Dailyfuel', [BaselineController::class, 'Dailyfuel'])->name('Dailyfuel');
Route::get('/FleetPerfomance', [BaselineController::class, 'FleetPerfomance'])->name('FleetPerfomance');
Route::get('/FuelClassification', [BaselineController::class, 'FuelClassification'])->name('FuelClassification');
Route::get('/CarTrackIdlingFuelOffloading', [BaselineController::class, 'CarTrackIdlingFuelOffloading'])->name('CarTrackIdlingFuelOffloading');
Route::get('/CarTrackIdlingFuelDeadruns', [BaselineController::class, 'CarTrackIdlingFuelDeadruns'])->name('CarTrackIdlingFuelDeadruns');
Route::get('/CarTrackIdlingFuelDaily', [BaselineController::class, 'CarTrackIdlingFuelDaily'])->name('CarTrackIdlingFuelDaily');

Route::get('/TripTimeTruck', [BaselineController::class, 'TripTimeTruck'])->name('TripTimeTruck');

Route::get('/TripClassificationV3', [BaselineController::class, 'TripClassificationV3'])->name('TripClassificationV3');
Route::get('/TripClassificationV3Updated', [BaselineController::class, 'TripClassificationV3Updated'])->name('TripClassificationV3Updated');
Route::get('/BaselineV2', [BaselineController::class, 'BaselineV2'])->name('BaselineV2');
Route::get('/TripClassificationV7', [BaselineController::class, 'TripClassificationV7'])->name('TripClassificationV7');
Route::get('/TripClassificationV7loading', [BaselineController::class, 'TripClassificationV7loading'])->name('TripClassificationV7loading');
Route::get('/TripTimeRoutev2', [BaselineController::class, 'TripTimeRoutev2'])->name('TripTimeRoutev2');

Route::get('/Stops', [BaselineController::class, 'Stops'])->name('Stops');
Route::get('/Deadruns', [BaselineController::class, 'Deadruns'])->name('Deadruns');
Route::get('/TripTimeRoutev2Deadruns', [BaselineController::class, 'TripTimeRoutev2Deadruns'])->name('TripTimeRoutev2Deadruns');
Route::get('/TripID', [BaselineController::class, 'TripID'])->name('TripID');
Route::get('/StartTime', [BaselineController::class, 'StartTime'])->name('StartTime');
Route::get('/GoogleApi', [BaselineController::class, 'GoogleApi'])->name('GoogleApi');
Route::get('/GoogleTripTime', [BaselineController::class, 'GoogleTripTime'])->name('GoogleTripTime');

Route::get('/TripSummary', [BaselineController::class, 'TripSummary'])->name('TripSummary');
Route::get('/TripDetail', [BaselineController::class, 'TripDetail'])->name('TripDetail'); 
Route::get('/lineClassification', [BaselineController::class, 'lineClassification'])->name('lineClassification');
Route::get('/lineclassificationV2', [BaselineController::class, 'lineclassificationV2'])->name('lineclassificationV2');
Route::get('/TripSoapFleetboard', [BaselineController::class, 'TripSoapFleetboard'])->name('TripSoapFleetboard');
Route::get('/DailySoapFleetboard', [BaselineController::class, 'DailySoapFleetboard'])->name('DailySoapFleetboard');
Route::get('/FleetRefactor', [BaselineController::class, 'FleetRefactor'])->name('FleetRefactor');
Route::get('/Reorder', [BaselineController::class, 'Reorder'])->name('Reorder');

