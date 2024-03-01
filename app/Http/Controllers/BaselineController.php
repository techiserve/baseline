<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;

class BaselineController extends Controller
{



    public function LatLongDifference()
    {
        $trips = DB::connection('mysql')->table('baselinetest')->where('id', '>', 1)->get(); 
        
        foreach($trips as $trip){
        
         $currentTrip = DB::connection('mysql')->table('baselinetest')->where('id', '=', $trip->id)->first(); 
            
         $previousTrip = DB::connection('mysql')->table('baselinetest')->where('id', '=', $trip->id - 1)->first(); 

         $interval =  $currentTrip->Longitude - $previousTrip->Longitude;        
         dd($interval);
         $tripUpdate = DB::connection('mysql')->table('baselinetest')->where('id', '=', $trip->id)->update([

            'LongitudeDifference' => $interval
         ]); 

        }
   
        dd('done...');
        return view('timeDifference');
    }


    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        ini_set('max_execution_time', 3600); // 3600 seconds = 60 minutes
         set_time_limit(3600);

        //COUNT COLUMN
//         $trips = DB::connection('mysql')->table('baselinetest')->where('id', '>', 1)->get();
//         $counter = 1;
//         foreach($trips as $trip){

//          //$columnName = "Stationary/Moving";
//          $currentTrip = $trip->StationaryMoving;
//        //  dd($currentTrip);
//          $previousFullTrip = DB::connection('mysql')->table('baselinetest')->where('id', '=', $trip->id - 1)->first();
//        //  dd($previousFullTrip->);
//          if($trip->StationaryMoving == $previousFullTrip->StationaryMoving){
//         //  dd($trip->StationaryMoving,$previousFullTrip->StationaryMoving);
//             $currentCount =  $previousFullTrip->Count + 1;
//             $updateCount = DB::connection('mysql')->table('baselinetest')->where('id', '=', $trip->id)->update([
//                 'Count' => $currentCount
//             ]);
//          }else{

//             $updateCount = DB::connection('mysql')->table('baselinetest')->where('id', '=', $trip->id)->update([

//                 'Count' => 1
//             ]);
//          }
    
//    }

//    dd('done...');



        //On The Road COLUMN
        // $trips = DB::connection('mysql')->table('baselinetest')->where('id', '>', 0)->get();   
        // foreach($trips as $trip){

        //     if($trip->Count > 17 AND $trip->StationaryMoving == 'Moving'){
              
        //     $updateCount = DB::connection('mysql')->table('baselinetest')->where('id', '=', $trip->id)->update([

        //         'OnTheRoad' => 'on the road'
        //     ]);

        //     }
        //     else{

        //         $updateCount = DB::connection('mysql')->table('baselinetest')->where('id', '=', $trip->id)->update([

        //             'OnTheRoad' => 'False'
        //         ]);
        //     }
        // }

        // dd('done..');

        //TRIP START
    //    $trips = DB::connection('mysql')->table('baselinetest')->where('id', '>', 0)->get();

    //     foreach($trips as $trip){

    //         $currentTrip = $trip->OnTheRoad;
    //      // dd($currentTrip);
    //     $previousFullTrip = DB::connection('mysql')->table('baselinetest')->where('id', '=', $trip->id - 1)->first();
    //    // dd($previousFullTrip);

    //       if($currentTrip == 'on the road' AND $previousFullTrip->OnTheRoad == 'False'){

    //         $updatetripstart = DB::connection('mysql')->table('baselinetest')->where('id', '=', $trip->id - 17)->update([

    //             'TripStart' => 'Trip Start'
    //         ]);

    //         $updatetripprogress = DB::connection('mysql')->table('baselinetest')->where('id', '=', $trip->id)->update([

    //             'TripStart' => 'Trip in progress'
    //         ]);

    //         $updateinbetween =  DB::connection('mysql')->table('baselinetest')->whereBetween('id', [$trip->id - 16, $trip->id - 1])->update([

    //             'TripStart' => 'Trip in progress'
    //         ]);


    //         $updatetripprogrezs = DB::connection('mysql')->table('baselinetest')->where('OnTheRoad', '=', 'on the road')->where('TripStart', '=', null)->where('id', '>', 1)->update([

    //             'TripStart' => 'Trip in progress'
    //         ]);

            
    //         $updatetripprogresq = DB::connection('mysql')->table('baselinetest')->where('OnTheRoad', '=', 'False')->where('TripStart', '=', null)->update([

    //             'TripStart' => 'None'
    //         ]);

    //       }
         
    //     }

    //     dd('done');




    //TripTest 
        //   $trips = DB::connection('mysql')->table('baselinetest')->where('id', '>', 0)->get();

        //   $updateTrip = DB::connection('mysql')->table('baselinetest')->where('id', '>', 0)->update([

        //     'Trip' => null
        //   ]);
        //   foreach($trips as $trip){

        //     $currentTrip = $trip->TripStart;
        //     $nextTrip = DB::connection('mysql')->table('baselinetest')->where('id', '=', $trip->id + 1)->first();
        //     if($currentTrip == 'None'){

        //         $updatetriptest = DB::connection('mysql')->table('baselinetest')->where('id', '=', $trip->id)->update([

        //         'TripTest' => 'Stationary'

        //        ]);
                
        //     }elseif($currentTrip == 'Trip Start'){

        //         $updatetriptest = DB::connection('mysql')->table('baselinetest')->where('id', '=', $trip->id)->update([

        //             'TripTest' => 'Trip Start'
    
        //            ]);
                    

        //     }else{
                
        //         $updatetriptest = DB::connection('mysql')->table('baselinetest')->where('id', '=', $trip->id)->update([

        //             'TripTest' => 'Trip in progress'
    
        //            ]);
        //     }

        //     if($currentTrip == 'Trip in progress' AND $nextTrip->TripStart == 'None'){

        //         $updatetriptest = DB::connection('mysql')->table('baselinetest')->where('id', '=', $trip->id)->update([

        //             'TripTest' => 'Trip Ended'
    
        //            ]);
        //     }


        //   }

        //   dd('done...');





        //TIME DIFFERENCE 
    //     $trips = DB::connection('mysql')->table('baselinetest')->where('id', '>', 0)->get();
    //     foreach($trips as $trip){

    //         $TripEnd = DB::connection('mysql')->table('baselinetest')->where('Trip', '=' , null)->where('TripTest', '=', 'Trip Ended')->first(); 
    //         if( $TripEnd != null){
    //          $NextTripStart = DB::connection('mysql')->table('baselinetest')->where('Trip', '=' , null)->where('id', '>' , $TripEnd->id)->where('TripTest', '=', 'Trip Start')->first(); 
    //         }else{
    //         $NextTripStart = null;
    //         }
            
    //         if($NextTripStart != null AND $TripEnd != null){
    //           //  dd('ndashaya...');
    //         $interval =  date_diff(date_create($NextTripStart->Time),date_create($TripEnd->Time)); 

    //         $minutes = $interval->days * 24 * 60; 
    //         $minutes += $interval->h * 60; 
    //         $minutes += $interval->i; 

    //     //    dd($minutes);
    //         if($minutes < 10){

    //           $updateinbetween =  DB::connection('mysql')->table('baselinetest')->whereBetween('id', [$TripEnd->id, $NextTripStart->id])->update([

    //            'TripTest' => 'Trip in progress',
    //            'Trip' => '1'
    //         ]);
    //         }
            
    //      }


    //    }
    //   // dd($TripEnd,$NextTripStart);

    //    dd('done..');

       return view('baseline');

    }

    /**
     * 
     * Show the form for creating a new resource.
     */
    public function geofence()
    {
        ini_set('max_execution_time', 3600); // 3600 seconds = 60 minutes
        set_time_limit(3600);
        $trips = DB::connection('mysql')->table('baselinetest')->where('id', '>', 0)->get();  

        foreach($trips as $trip){
            
            $lat = $trip->Latitude;
            $lng = $trip->Longitude;
         //   dd($lat,$lng);
            $geofences = DB::connection('mysql')->table('geofence')->where('id', '>', 0)->get();
            
            foreach($geofences as $geofence){

             if($geofence->Shape == 'Polygon'){
           // dd('polygon');
               
           $otherPoints = [
            ['latitude' => $geofence->LowLat, 'longitude' => $geofence->LowLong],
            ['latitude' => $geofence->LowLat, 'longitude' => $geofence->HighLong],
            ['latitude' => $geofence->HighLat, 'longitude' => $geofence->LowLong],
            ['latitude' => $geofence->HighLat, 'longitude' => $geofence->HighLong],
            ];


             // Given test point
         $testPoint = ['latitude' =>  $lat, 'longitude' => $lng];

        // Other points to compare

        // Initialize with a large value
        $shortestDistance = PHP_INT_MAX;

        // Calculate distance for each point and find the shortest distance
        foreach ($otherPoints as $otherPoint) {
            $distance = $this->haversineDistance(
                $testPoint['latitude'],
                $testPoint['longitude'],
                $otherPoint['latitude'],
                $otherPoint['longitude']
            );

            if ($distance < $shortestDistance) {
                $shortestDistance = $distance;
            }
        }

      //  dd($shortestDistance, $trip->id);
        if($shortestDistance < 1050){

            $location = $geofence->ZoneName;
            $tripUpdate = DB::connection('mysql')->table('baselinetest')->where('id', '=', $trip->id)->update([

                'Geofence' => $location
        
               ]);  
            break;

        }else{

            $location = "Outside Geofence";
            $tripUpdate = DB::connection('mysql')->table('baselinetest')->where('id', '=', $trip->id)->update([

                'Geofence' => $location
        
               ]);  
        }
  
     //  dd($location);
      
            
    }else{
        //dd('circle');           
        ini_set('max_execution_time', 3600); // 3600 seconds = 60 minutes
         set_time_limit(3600);
        // Given test point
        $lat1 = $trip->Latitude;
        $lon1 = $trip->Longitude;
        // Other points to compare
        $lat2 = $geofence->LowLat;
        $lon2 = $geofence->LowLong;

        // Calculate distance for each point and find the shortest distance
        $distance = $this->haversineDistance($lat1, $lon1, $lat2, $lon2);
       // dd($distance);

        if($distance < 1050){

            $location = $geofence->ZoneName;
            $tripUpdate = DB::connection('mysql')->table('baselinetest')->where('id', '=', $trip->id)->update([

                'Geofence' => $location
        
               ]); 
               break;

        }else{

            $location = "Outside Geofence";
            $tripUpdate = DB::connection('mysql')->table('baselinetest')->where('id', '=', $trip->id)->update([

                'Geofence' => $location
        
               ]); 
        }

    
       }

      }

     }
        
       dd("done");
    }

    private function haversineDistance($lat1, $lon1, $lat2, $lon2)
    {
        $lat1Rad = deg2rad($lat1);
        $lon1Rad = deg2rad($lon1);
        $lat2Rad = deg2rad($lat2);
        $lon2Rad = deg2rad($lon2);

        $latDiff = $lat2Rad - $lat1Rad;
        $lonDiff = $lon2Rad - $lon1Rad;

        $a = sin($latDiff / 2) ** 2 + cos($lat1Rad) * cos($lat2Rad) * sin($lonDiff / 2) ** 2;
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        $earthRadius = 6371000; // Radius of the Earth in kilometers

        return $earthRadius * $c;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function timeDifference()
    {
        $trips = DB::connection('mysql')->table('baselinetest')->where('id', '>', 1)->get(); 
        
        foreach($trips as $trip){
        
         $currentTrip = DB::connection('mysql')->table('baselinetest')->where('id', '=', $trip->id)->first(); 
            
         $previousTrip = DB::connection('mysql')->table('baselinetest')->where('id', '=', $trip->id - 1)->first(); 

         $interval =  date_diff(date_create($currentTrip->Time),date_create($previousTrip->Time));        
        // dd($interval);
         $tripUpdate = DB::connection('mysql')->table('baselinetest')->where('id', '=', $trip->id)->update([

            'TimeDifference' => $interval->format('%H:%I:%S')
         ]); 

        }
   
        dd('done...');
        return view('timeDifference');
    }

    /**
     * Display the specified resource.
     */
    public function cycleTime()
    {
        $cycleTimeUpdate = DB::connection('mysql')->table('baselinetest')->where('TripTest', '=', 'Trip Start')->update([

            'CycleTimeEvent' => 'Load/Offload Time'
        ]);

        $cycleTimeUpdate2 = DB::connection('mysql')->table('baselinetest')->where('TripTest', '=', 'Trip Ended')->update([

            'CycleTimeEvent' => 'Travel Time'
        ]);
        
       $cycleTimeEvent = DB::connection('mysql')->table('baselinetest')->where('id', '=', 1)->update([
        'TripTest' => 'Trip Start'
       ]);

       $cycleTrips = DB::connection('mysql')->table('baselinetest')->where('id', '>', 1)->where('TripTest', '=', 'Trip Start')->orWhere('TripTest', '=', 'Trip Ended')->get();
       //dd($cycleTrips);

       $previousId = 1;
       foreach($cycleTrips as $trip){

        $currentTrip = DB::connection('mysql')->table('baselinetest')->where('id', '=', $trip->id)->first(); 
            
        $previousTrip = DB::connection('mysql')->table('baselinetest')->where('id', '=', $previousId )->first(); 

        $interval =  date_diff(date_create($currentTrip->Time),date_create($previousTrip->Time)); 
       // dd($interval);       

        $tripUpdate = DB::connection('mysql')->table('baselinetest')->where('id', '=', $trip->id)->update([

           'EventDuration' => $interval->format('%H:%I:%S')
        ]); 

        $previousId = $currentTrip->id;

       }

       dd('done..');
       
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function movingStationary()
    {
        $trips = DB::connection('mysql')->table('baselinetest')->where('id', '>', 0)->get();  

        foreach($trips as $trip){

            if($trip->CoordinateTest == 0){

                $update = DB::connection('mysql')->table('baselinetest')->where('id', '=', $trip->id)->update([

                    'StationaryMoving' => 'Moving'
                ]);

            }elseif($trip->CoordinateTest == 1 && $trip->Distance > 0.0 && $trip->HighSpeed > 0){

                $update = DB::connection('mysql')->table('baselinetest')->where('id', '=', $trip->id)->update([

                    'StationaryMoving' => 'Moving'
                ]);

            }else{


                $update = DB::connection('mysql')->table('baselinetest')->where('id', '=', $trip->id)->update([

                    'StationaryMoving' => 'Stationary'
                ]);
            }
        }

        dd('done..');
    }

    /**
     * Update the specified resource in storage.
     */
    public function truckLogic()
    {
        $truckData = DB::connection('mysql')->table('baseline')->groupBy('Truck')->get();
        dd($truckData);

        foreach ($truckData as $truckCode => $rows) {
        // Apply logic for each truck code
       foreach ($rows as $row) {
        // Your logic for each row within the same truck code

       }
        // Logic for the first row of the next truck code
      }

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy()
    {
        //
    }
}
