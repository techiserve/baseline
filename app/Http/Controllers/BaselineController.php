<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use DateTime;
use App\Imports\BaselineImport;
use Maatwebsite\Excel\Facades\Excel;

class BaselineController extends Controller
{

  public function BaselineImportCreate(){

  //  dd('here..');
    return view('BaselineImport');
  }

  public function BaselineImport()
    {

    //  dd(request()->file('excel_file'));
        ini_set('max_execution_time', 36000000); // 3600 seconds = 60 minutes
        set_time_limit(36000000);

        try {
          Excel::import(new BaselineImport, request()->file('excel_file'));

          return redirect()->back()->with('success', 'Excel file imported successfully');
      } catch (\Exception $e) {
          return redirect()->back()->with('error', 'Error importing Excel file: ' . $e->getMessage());
      }

      //  return view('timeDifference');
    }





    public function LongDifference()
    {

        ini_set('max_execution_time', 360000000); // 3600 seconds = 60 minutes
        set_time_limit(360000000);

        $truckData = DB::connection('mysql')->table('baseline')->groupBy('Truck')->orderBy('id')->get();
        $truckData = $truckData->take(1);
   
         foreach ($truckData as $truckCode => $rows) {
     
         $trucks =  DB::connection('mysql')->table('baseline')->where('Truck', '=', $rows->Truck)->orderBy('Date')->orderBy('Time')->get(); 
          //  dd($trucks);
        foreach ($trucks as  $truckrows => $trip) {
        
         $currentTrip = DB::connection('mysql')->table('baseline')->where('id', '=', $trip->id)->first(); 

         if($truckrows  > 0){

          $nextIndex = $truckrows - 1;
        }else{
          $nextIndex = 0;
        }
                    
         $previousTrip = DB::connection('mysql')->table('baseline')->where('id', '=',  $trucks[$nextIndex]->id)->first();          

         $interval =  $currentTrip->Longitude - $previousTrip->Longitude;        
       //  dd(number_format($interval,));
         $tripUpdate = DB::connection('mysql')->table('baseline')->where('id', '=', $trip->id)->update([

            'LongitudeDifference' => number_format(abs($interval),6)
         ]); 

        }

     }
   
        dd('done...');

        return view('timeDifference');
    }

    public function LatDifference()
    {
        ini_set('max_execution_time', 3600000); // 3600 seconds = 60 minutes
        set_time_limit(36000000);

        $truckData = DB::connection('mysql')->table('baseline')->groupBy('Truck')->orderBy('id')->get();
        $truckData = $truckData->take(1);
   
         foreach ($truckData as $truckCode => $rows) {
     
         $trucks =  DB::connection('mysql')->table('baseline')->where('Truck', '=', $rows->Truck)->orderBy('Date')->orderBy('Time')->get();
            // dd($trucks);
        foreach ($trucks as $truckrows => $trip) {

         $currentTrip = DB::connection('mysql')->table('baseline')->where('id', '=', $trip->id)->first(); 

         if($truckrows  > 0){

          $nextIndex = $truckrows - 1;
        }else{
          $nextIndex = 0;
        }

        $previousTrip = DB::connection('mysql')->table('baseline')->where('id', '=',  $trucks[$nextIndex]->id)->first();   

         $interval =  $currentTrip->Latitude - $previousTrip->Latitude;        
       //  dd($interval);
         $tripUpdate = DB::connection('mysql')->table('baseline')->where('id', '=', $trip->id)->update([

            'LatitudeDifference' => number_format(abs($interval),6)
         ]); 

        }
   
      }
        dd('done...');

        return view('timeDifference');
    }

    public function CoordinateTest()
    {
        ini_set('max_execution_time', 36000000); // 3600 seconds = 60 minutes
        set_time_limit(3600000);
          
     //  dd('testing');
        $truckData = DB::connection('mysql')->table('baseline')->groupBy('Truck')->orderBy('id')->get();
        $truckData = $truckData->take(1);
   
         foreach ($truckData as $truckCode => $rows) {
     
         $trucks =  DB::connection('mysql')->table('baseline')->where('Truck', '=', $rows->Truck)->orderBy('Date')->orderBy('Time')->get();
        // dd($trucks);

        foreach ($trucks as $trip) {
        
         $currentTrip = DB::connection('mysql')->table('baseline')->where('id', '=', $trip->id)->first(); 
            
          if($currentTrip->LongitudeDifference < 0.0001 || $currentTrip->LatitudeDifference < 0.0001 ){

            $test = 1;

          }else{

            $test = 0;
          }
       
         $tripUpdate = DB::connection('mysql')->table('baseline')->where('id', '=', $trip->id)->update([

            'CoordinateTest' => $test
         ]); 
   

        }

       }
   
        dd('done...');
        return view('timeDifference');
    }
 
     public function Count(){

        ini_set('max_execution_time', 36000000); // 3600 seconds = 60 minutes
        set_time_limit(36000000);

        $truckData = DB::connection('mysql')->table('baseline')->groupBy('Truck')->orderBy('id')->get();
        $truckData = $truckData->take(1);
   
         foreach ($truckData as $truckCode => $rows) {
     
         $trucks =  DB::connection('mysql')->table('baseline')->where('Truck', '=', $rows->Truck)->where('id', '!=', $rows->id)->orderBy('Date')->orderBy('Time')->get();
         $prevTruck =  DB::connection('mysql')->table('baseline')->where('Truck', '=', $rows->Truck)->where('id', '=', $rows->id)->orderBy('Date')->orderBy('Time')->first();
          //   dd($trucks);
        foreach ($trucks as  $trip) {

        //  if($truckrows  > 0){

        //   $nextIndex = $truckrows - 1;
        // }else{
        //   $nextIndex = 0;
        // }
            
           $prev = $prevTruck->id;
        //$columnName = "Stationary/Moving";
        $currentTrip = $trip->StationaryMoving;
      //  dd($currentTrip);
        $previousFullTrip = DB::connection('mysql')->table('baseline')->where('id', '=', $prev)->first();
      //  dd($previousFullTrip->);
        if($trip->StationaryMoving == $previousFullTrip->StationaryMoving){
       //  dd($trip->StationaryMoving,$previousFullTrip->StationaryMoving);
           $currentCount =  $previousFullTrip->Count + 1;
           $updateCount = DB::connection('mysql')->table('baseline')->where('id', '=', $trip->id)->update([

               'Count' => $currentCount
           ]);
        }else{

           $updateCount = DB::connection('mysql')->table('baseline')->where('id', '=', $trip->id)->update([

               'Count' => 1
           ]);
         }

         $prevTruck = $trip;
   
       }

      }

       dd('done...');
     }

     public function OnTheRoad(){

            //On The Road COLUMN
            ini_set('max_execution_time', 36000000); // 3600 seconds = 60 minutes
            set_time_limit(360000000);
    
            $truckData = DB::connection('mysql')->table('baseline')->groupBy('Truck')->orderBy('id')->get();
            $truckData = $truckData->take(1);
       
             foreach ($truckData as $truckCode => $rows) {
         
             $trucks =  DB::connection('mysql')->table('baseline')->where('Truck', '=', $rows->Truck)->orderBy('Date')->orderBy('Time')->get();
              //   dd($trucks);
            foreach ($trucks as $trip) {

            if($trip->Count > 17 AND $trip->StationaryMoving == 'Moving'){
              
            $updateCount = DB::connection('mysql')->table('baseline')->where('id', '=', $trip->id)->update([

                'OnTheRoad' => 'on the road'
            ]);

            }
            else{

                $updateCount = DB::connection('mysql')->table('baseline')->where('id', '=', $trip->id)->update([

                    'OnTheRoad' => 'False'
                ]);
             }
          }

       }

        dd('done..');
     }

     public function TripStart(){
        ini_set('max_execution_time', 360000000); // 3600 seconds = 60 minutes
        set_time_limit(360000000);

        $truckData = DB::connection('mysql')->table('baseline')->groupBy('Truck')->orderBy('id')->get();
        $truckData = $truckData->take(1);
   
         foreach ($truckData as $truckCode => $rows) {
      
         $trucks =  DB::connection('mysql')->table('baseline')->where('Truck', '=', $rows->Truck)->orderBy('Date')->orderBy('Time')->get();
         //$prevTruck =  DB::connection('mysql')->table('baseline')->where('Truck', '=', $rows->Truck)->where('id', '=', $rows->id)->orderBy('Date')->orderBy('Time')->first();
          //  dd($trucks);
        foreach ($trucks as $truckrows => $trip) {
            
          
            $currentTrip = $trip->OnTheRoad;
        
            if($truckrows  > 0){

              $prev  = $truckrows - 1;
            }else{
              $prev  = 0;
            }
         // dd($currentTrip);
        $previousFullTrip = DB::connection('mysql')->table('baseline')->where('id', '=',  $trucks[$prev]->id)->first();
       // dd($previousFullTrip);

          if($currentTrip == 'on the road' AND $previousFullTrip->OnTheRoad == 'False'){

            $seventeenth = $truckrows - 16;
           
           // dd($truckrows,$seventeenthRow,$trip);

            $updatetripstart = DB::connection('mysql')->table('baseline')->where('id', '=', $trucks[$seventeenth]->id)->where('Truck', '=', $rows->Truck)->update([

                'TripStart' => 'Trip Start'
            ]);

            $updatetripprogress = DB::connection('mysql')->table('baseline')->where('id', '=', $trip->id)->update([

                'TripStart' => 'Trip in progress'
            ]);

            // $updateinbetween =  DB::connection('mysql')->table('baseline')->whereBetween('id', [$sixthRow->id ,  $prev])->update([

            //     'TripStart' => 'Trip in progress'
            // ]);

              //  $startIndex = $truckrows - 16;
              // $endIndex = $truckrows - 1;

              // for ($i = $startIndex; $i <= $endIndex; $i++) {
              // // Perform your update logic here
            
              // $sixth = array_slice($trucksArray, $i, 1);
              // $sixthRow = end($sixth);
              // //dd($sixth[0]);
              // $updatetripprogress = DB::connection('mysql')->table('baseline')->where('id', '=', $sixth[0]->id)->update([

              //   'TripStart' => 'Trip in progress'

              // ]);

              // }

            //  dd('lets see');

         
            $updatetripprogrezs = DB::connection('mysql')->table('baseline')->where('OnTheRoad', '=', 'on the road')->where('TripStart', '=', null)->where('id', '>', 1)->where('Truck', '=',$trip->Truck)->update([

                'TripStart' => 'Trip in progress'
            ]);

            
            $updatetripprogresq = DB::connection('mysql')->table('baseline')->where('OnTheRoad', '=', 'False')->where('TripStart', '=', null)->where('Truck', '=',$trip->Truck)->update([

                'TripStart' => 'None'
            ]);

          }else{

            if($trip->OnTheRoad == "False" && $trip->TripStart == null ){

                $updatetripprogresq = DB::connection('mysql')->table('baseline')->where('id', '=',  $trip->id )->update([

                    'TripStart' => 'None'
                ]);
            }

          }

         // $prevTruck = $trip;
         
        }

       }

        dd('done');

     }

     public function TripTest(){

    
        ini_set('max_execution_time', 360000000); // 3600 seconds = 60 minutes
        set_time_limit(36000000);

        $truckData = DB::connection('mysql')->table('baseline')->groupBy('Truck')->orderBy('id')->get();
        $truckData = $truckData->take(1);
   
         foreach ($truckData as $truckCode => $rows) {
     
         $trucks =  DB::connection('mysql')->table('baseline')->where('Truck', '=', $rows->Truck)->where('id', '!=', $rows->id)->orderBy('Date')->orderBy('Time')->get();
          //   dd($trucks);
        foreach ($trucks as  $truckrows => $trip) {

            $currentTrip = $trip->TripStart;

            $nextIndex = $truckrows + 1;
           // dd($trucks[$nextIndex]->id);
            $nextTrip = DB::connection('mysql')->table('baseline')->where('id', '=', $trucks[$nextIndex]->id)->first();
            if($currentTrip == 'None'){

                $updatetriptest = DB::connection('mysql')->table('baseline')->where('id', '=', $trip->id)->update([

                'TripTest' => 'Stationary'

               ]);
                
            }elseif($currentTrip == 'Trip Start'){

                $updatetriptest = DB::connection('mysql')->table('baseline')->where('id', '=', $trip->id)->update([

                    'TripTest' => 'Trip Start'
    
                   ]);
                    

            }else{
                
                $updatetriptest = DB::connection('mysql')->table('baseline')->where('id', '=', $trip->id)->update([

                    'TripTest' => 'Trip in progress'
    
                   ]);
            }

            if($currentTrip == 'Trip in progress' AND $trip->TripEnd == 'Trip Ended'){

                $updatetriptest = DB::connection('mysql')->table('baseline')->where('id', '=', $trip->id)->update([

                    'TripTest' => 'Trip Ended'
    
                   ]);
            }


          }

        }

        dd('done');

        }




        public function TripTestUpdated(){

            ini_set('max_execution_time', 3600000); // 3600 seconds = 60 minutes
            set_time_limit(36000000);
    
            $truckData = DB::connection('mysql')->table('baseline')->groupBy('Truck')->orderBy('id')->get();
            $truckData = $truckData->take(1);
        //    dd($truckData);
             foreach ($truckData as $truckCode => $rows) {
         
             $trucks =  DB::connection('mysql')->table('baseline')->where('Truck', '=', $rows->Truck)->where('Trip', '=' , null)->where('TripTest', '=', 'Trip Ended')->where('id', '!=', $rows->id)->orderBy('Date')->orderBy('Time')->get();
            // dd($trucks);
            foreach($trucks as $truckrows => $trip){


              $updateinbetween =  DB::connection('mysql')->table('baseline')->where('id', $trip->id)->update([
    
                'Trip' => '1'
             ]);


          //    $updateinbetweens =  DB::connection('mysql')->table('baseline')->where('TripStart', "!=", 'Trip Start')->where('TripTest','=', 'Trip Start')->where('Truck','=', $trip->Truck)->update([
    
          //     'Trip' => '1'
          //  ]);
             
                $TripEnd = $trip;
               // dd($TripEnd);
                if( $TripEnd != null){

                 $NextTripStart = DB::connection('mysql')->table('baseline')->where('TripStart', '=', 'Trip Start')->where('Truck','=', $trip->Truck)->where('Trip', '=' , null)->where('TripTest', '=', 'Trip Start')->whereTime('Time', '>', $TripEnd->Time)->first();
              //   dd($TripEnd,$NextTripStart);  
                }else{
                $NextTripStart = null;
               // dd('hapana hapana');
                }
              //  dd('pane nyaya....');
                
                if($NextTripStart != null AND $TripEnd != null){
                  //  dd('ndashaya...');
                $interval =  date_diff(date_create($NextTripStart->Time),date_create($TripEnd->Time)); 
    
                $minutes = $interval->days * 24 * 60; 
                $minutes += $interval->h * 60; 
                $minutes += $interval->i; 
                   //  dd($minutes );
                if($minutes < 10){

                  // $startIndex = $truckrows - 16;
                  // $endIndex = $truckrows - 1;
    
                  // for ($i = $startIndex; $i <= $endIndex; $i++) {
                  // // Perform your update logic here
                  // $trucksArray = $trucks->toArray(); 
                  // $sixth = array_slice($trucksArray, $i, 1);
                  // $sixthRow = end($sixth);
                  // //dd($sixth[0]);
                  // $updatetripprogress = DB::connection('mysql')->table('baseline')->where('id', '=', $sixth[0]->id)->update([
    
                  //   'TripStart' => 'Trip in progress',
                  //   'Trip' => '2'
    
                  // ]);
    
                  // }
                  //dd($minutes,$TripEnd->id,$NextTripStart->id);
                  $updateinbetween =  DB::connection('mysql')->table('baseline')->whereBetween('Time', [$TripEnd->Time, $NextTripStart->Time])->where('Truck','=',$trip->Truck)->update([
    
                   'TripTest' => 'Trip in progress',
                   'Trip' => '2'
                ]);
               // dd('updated trip');
             
                }
                
             }
    
    
           }

         }

         dd('done..');

        }


        public function sortDateTime()
      {
         ini_set('max_execution_time', 3600000); // 3600 seconds = 60 minutes
         set_time_limit(3600000);

         $times = DB::connection('mysql')->table('baseline')->where('id', '>', 0)->get(); 
         
         foreach($times as $trip){

         $currentTrip = DB::connection('mysql')->table('baseline')->where('id', '=', $trip->id)->first(); 
        // dd($currentTrip);
         $dateTime = new DateTime($currentTrip->Date);
         $date = $dateTime->format('Y-m-d'); 
         $time = $dateTime->format('H:i:s');
       //  dd($time);
         $timeUpdate = DB::connection('mysql')->table('baseline')->where('id', '=', $trip->id)->update([

           'Time' => $time, 
           'Date' => $date

        ]); 

      }

      dd('done..');

        }
  
       public function index()
       {
        ini_set('max_execution_time', 3600); // 3600 seconds = 60 minutes
         set_time_limit(3600);


      //    $times = DB::connection('mysql')->table('baseline')->where('id', '>', 0)->get(); 
         
      //    foreach($times as $trip){

      //    $currentTrip = DB::connection('mysql')->table('baseline')->where('id', '=', $trip->id)->first(); 
      //   // dd($currentTrip);
      //    $dateTime = new DateTime($currentTrip->Date);
      //    $date = $dateTime->format('Y-m-d'); 
      //    $time = $dateTime->format('H:i:s');
      //  //  dd($time);
      //    $timeUpdate = DB::connection('mysql')->table('baseline')->where('id', '=', $trip->id)->update([

      //      'Time' => $time, 
      //      'Date' => $date

      //   ]); 

      // }


      $times = DB::connection('mysql')->table('baseline')->where('id', '>', 0)->orderBy('Date')->orderBy('Time')->get(); 

      dd($times);
        

       // dd();





        //  $updateinbetween =  DB::connection('mysql')->table('baseline')->first();
        //  $dateTime = new DateTime($updateinbetween->Date);
        //  $date = $dateTime->format('Y-m-d'); 
        //  $time = $dateTime->format('H:i:s');

        //  dd($date,$time);

 

         //  dd('done');





//             dd('loading.....');
//         //COUNT COLUMN
//         $trips = DB::connection('mysql')->table('baseline')->where('id', '>', 1)->get();
//         $counter = 1;
//         foreach($trips as $trip){

//          //$columnName = "Stationary/Moving";
//          $currentTrip = $trip->StationaryMoving;
//        //  dd($currentTrip);
//          $previousFullTrip = DB::connection('mysql')->table('baseline')->where('id', '=', $trip->id - 1)->first();
//        //  dd($previousFullTrip->);
//          if($trip->StationaryMoving == $previousFullTrip->StationaryMoving){
//         //  dd($trip->StationaryMoving,$previousFullTrip->StationaryMoving);
//             $currentCount =  $previousFullTrip->Count + 1;
//             $updateCount = DB::connection('mysql')->table('baseline')->where('id', '=', $trip->id)->update([
//                 'Count' => $currentCount
//             ]);
//          }else{

//             $updateCount = DB::connection('mysql')->table('baseline')->where('id', '=', $trip->id)->update([

//                 'Count' => 1
//             ]);
//          }
    
//    }

//    dd('done...');



        //On The Road COLUMN
        // $trips = DB::connection('mysql')->table('baseline')->where('id', '>', 0)->get();   
        // foreach($trips as $trip){

        //     if($trip->Count > 17 AND $trip->StationaryMoving == 'Moving'){
              
        //     $updateCount = DB::connection('mysql')->table('baseline')->where('id', '=', $trip->id)->update([

        //         'OnTheRoad' => 'on the road'
        //     ]);

        //     }
        //     else{

        //         $updateCount = DB::connection('mysql')->table('baseline')->where('id', '=', $trip->id)->update([

        //             'OnTheRoad' => 'False'
        //         ]);
        //     }
        // }

        // dd('done..');

        //TRIP START
    //    $trips = DB::connection('mysql')->table('baseline')->where('id', '>', 0)->get();

    //     foreach($trips as $trip){

    //         $currentTrip = $trip->OnTheRoad;
    //      // dd($currentTrip);
    //     $previousFullTrip = DB::connection('mysql')->table('baseline')->where('id', '=', $trip->id - 1)->first();
    //    // dd($previousFullTrip);

    //       if($currentTrip == 'on the road' AND $previousFullTrip->OnTheRoad == 'False'){

    //         $updatetripstart = DB::connection('mysql')->table('baseline')->where('id', '=', $trip->id - 17)->update([

    //             'TripStart' => 'Trip Start'
    //         ]);

    //         $updatetripprogress = DB::connection('mysql')->table('baseline')->where('id', '=', $trip->id)->update([

    //             'TripStart' => 'Trip in progress'
    //         ]);

    //         $updateinbetween =  DB::connection('mysql')->table('baseline')->whereBetween('id', [$trip->id - 16, $trip->id - 1])->update([

    //             'TripStart' => 'Trip in progress'
    //         ]);


    //         $updatetripprogrezs = DB::connection('mysql')->table('baseline')->where('OnTheRoad', '=', 'on the road')->where('TripStart', '=', null)->update([

    //             'TripStart' => 'Trip in progress'
    //         ]);

            
    //         $updatetripprogresq = DB::connection('mysql')->table('baseline')->where('OnTheRoad', '=', 'False')->where('TripStart', '=', null)->update([

    //             'TripStart' => 'None'
    //         ]);

    //       }
         
    //     }

    //     dd('done');




    //TripTest 
        //   $trips = DB::connection('mysql')->table('baseline')->where('id', '>', 0)->get();

        //   $updateTrip = DB::connection('mysql')->table('baseline')->where('id', '>', 0)->update([

        //     'Trip' => null
        //   ]);
        //   foreach($trips as $trip){

        //     $currentTrip = $trip->TripStart;
        //     $nextTrip = DB::connection('mysql')->table('baseline')->where('id', '=', $trip->id + 1)->first();
        //     if($currentTrip == 'None'){

        //         $updatetriptest = DB::connection('mysql')->table('baseline')->where('id', '=', $trip->id)->update([

        //         'TripTest' => 'Stationary'

        //        ]);
                
        //     }elseif($currentTrip == 'Trip Start'){

        //         $updatetriptest = DB::connection('mysql')->table('baseline')->where('id', '=', $trip->id)->update([

        //             'TripTest' => 'Trip Start'
    
        //            ]);
                    

        //     }else{
                
        //         $updatetriptest = DB::connection('mysql')->table('baseline')->where('id', '=', $trip->id)->update([

        //             'TripTest' => 'Trip in progress'
    
        //            ]);
        //     }

        //     if($currentTrip == 'Trip in progress' AND $nextTrip->TripStart == 'None'){

        //         $updatetriptest = DB::connection('mysql')->table('baseline')->where('id', '=', $trip->id)->update([

        //             'TripTest' => 'Trip Ended'
    
        //            ]);
        //     }


        //   }

        //   dd('done...');



        //Trip in progress update 
    //     $trips = DB::connection('mysql')->table('baseline')->where('id', '>', 0)->get();
    //     foreach($trips as $trip){

    //         $TripEnd = DB::connection('mysql')->table('baseline')->where('Trip', '=' , null)->where('TripTest', '=', 'Trip Ended')->first(); 
    //         if( $TripEnd != null){
    //          $NextTripStart = DB::connection('mysql')->table('baseline')->where('Trip', '=' , null)->where('id', '>' , $TripEnd->id)->where('TripTest', '=', 'Trip Start')->first(); 
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

    //           $updateinbetween =  DB::connection('mysql')->table('baseline')->whereBetween('id', [$TripEnd->id, $NextTripStart->id])->update([

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


    public function tripEnd()
    {
        ini_set('max_execution_time', 36000000); // 3600 seconds = 60 minutes
        set_time_limit(360000000);
       // dd('testing');
        $truckData = DB::connection('mysql')->table('baseline')->groupBy('Truck')->orderBy('id')->get();
        $truckData = $truckData->take(1);
   
         foreach ($truckData as $truckCode => $rows) {
     
         $trucks =  DB::connection('mysql')->table('baseline')->where('Truck', '=', $rows->Truck)->where('id', '!=', $rows->id)->orderBy('Date')->orderBy('Time')->get();
        // dd($trucks);
        foreach ($trucks as $truckrows => $trip) {

          $nextIndex = $truckrows + 1;
        
         $currentTrip = DB::connection('mysql')->table('baseline')->where('id', '=', $trip->id)->first(); 
            
         $nextTrip = DB::connection('mysql')->table('baseline')->where('id', '=', $trucks[$nextIndex]->id)->first(); 
           //dd($trucks,$nextTrip,$truckrows);  
         if($currentTrip->OnTheRoad == "on the road" && $nextTrip->OnTheRoad == "False"){

            $test = "Trip Ended";

         }else{

           $test = "N/A";

         }

       //  dd($test);

         $tripUpdate = DB::connection('mysql')->table('baseline')->where('id', '=', $trip->id)->update([

            'TripEnd' => $test
         ]); 

        }

      }
   
        dd('done...');
        return view('timeDifference');
    }

    /**
     * 
     * Show the form for creating a new resource.
     */
    public function geofence()
    {
        ini_set('max_execution_time', 3600000000); // 3600 seconds = 60 minutes
        set_time_limit(3600000000);

        $truckData = DB::connection('mysql')->table('baseline')->groupBy('Truck')->orderBy('id')->get();
        $truckData = $truckData->take(2);
   
         foreach ($truckData as $truckCode => $rows) {
     
         $trucks =  DB::connection('mysql')->table('baseline')->where('Truck', '=', $rows->Truck)->where('id', '>', $rows->id)->orderBy('Date')->orderBy('Time')->get();
          //   dd($trucks);
        foreach ($trucks as $trip) {
        
            
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
            $tripUpdate = DB::connection('mysql')->table('baseline')->where('id', '=', $trip->id)->update([

                'Geofence' => $location
        
               ]);  
            break;

        }else{

            $location = "Outside Geofence";
            $tripUpdate = DB::connection('mysql')->table('baseline')->where('id', '=', $trip->id)->update([

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
            $tripUpdate = DB::connection('mysql')->table('baseline')->where('id', '=', $trip->id)->update([

                'Geofence' => $location
        
               ]); 
               break;

        }else{

            $location = "Outside Geofence";
            $tripUpdate = DB::connection('mysql')->table('baseline')->where('id', '=', $trip->id)->update([

                'Geofence' => $location
        
               ]); 
        }

    
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

        ini_set('max_execution_time', 36000000000); // 3600 seconds = 60 minutes
        set_time_limit(3600000000);

        

        $truckData = DB::connection('mysql')->table('baseline')->groupBy('Truck')->orderBy('id')->get();
        $truckData = $truckData->take(1);

       // dd($truckData);
   
         foreach ($truckData as $truckCode => $rows) {
     
         $trucks =  DB::connection('mysql')->table('baseline')->where('Truck', '=', $rows->Truck)->orderBy('Date')->orderBy('Time')->get();
            // dd($trucks);
          foreach ($trucks as  $truckrows => $trip) {


            $currentTrip = DB::connection('mysql')->table('baseline')->where('id', '=', $trip->id)->first(); 
  
        //   $dateTime = new DateTime($currentTrip->Date);
        //   $date = $dateTime->format('Y-m-d'); 
        //   $time = $dateTime->format('H:i:s');
  
        //   $timeUpdate = DB::connection('mysql')->table('baseline')->where('id', '=', $trip->id)->update([

        //     'Time' => $time 
        //  ]); 
        if($truckrows  > 0){

          $nextIndex = $truckrows - 1;
        }else{
          $nextIndex = 0;
        }
                  
         $previousTrip = DB::connection('mysql')->table('baseline')->where('id', '=',  $trucks[$nextIndex]->id)->first(); 

         $interval =  date_diff(date_create($currentTrip->Time),date_create($previousTrip->Time));        
        // dd($interval);
         $tripUpdate = DB::connection('mysql')->table('baseline')->where('id', '=', $trip->id)->update([

            'TimeDifference' => $interval->format('%H:%I:%S')
         ]); 

        

        }

       // dd('done...');

      }
   
        dd('done...');
        return view('timeDifference');
    }

    /**
     * Display the specified resource.
     */
    public function cycleTime()
    {
        
        ini_set('max_execution_time', 360000000); // 3600 seconds = 60 minutes
        set_time_limit(360000000);

        $truckData = DB::connection('mysql')->table('baseline')->groupBy('Truck')->orderBy('id')->get();
      //  $truckData = $truckData->take(2);
   
         foreach ($truckData as $truckCode => $rows) {

        $cycleTimeUpdate = DB::connection('mysql')->table('baseline')->where('Truck', '=', $rows->Truck)->where('TripTest', '=', 'Trip Start')->update([

            'CycleTimeEvent' => 'Load/Offload Time'
        ]);

        $cycleTimeUpdate2 = DB::connection('mysql')->table('baseline')->where('Truck', '=', $rows->Truck)->where('TripTest', '=', 'Trip Ended')->update([

            'CycleTimeEvent' => 'Travel Time'
        ]);
        
       $cycleTimeEvent = DB::connection('mysql')->table('baseline')->where('id', '=', $rows->id)->update([

        'TripTest' => 'Trip Start'
       ]);

       $trucks = DB::connection('mysql')->table('baseline')->where('Truck', '=', $rows->Truck)->where('id', '!=', $rows->id)->where('TripTest', '=', 'Trip Start')->orWhere('TripTest', '=', 'Trip Ended')->orderBy('Date')->orderBy('Time')->get();
       //dd($cycleTrips);

       $previousId = $rows->id;
      //  $trucks =  DB::connection('mysql')->table('baseline')->where('Truck', '=', $rows->Truck)->where('id', '>', $rows->id)->get();
          //   dd($trucks);
        foreach ($trucks as $trip) {

        $currentTrip = DB::connection('mysql')->table('baseline')->where('id', '=', $trip->id)->first(); 
            
        $previousTrip = DB::connection('mysql')->table('baseline')->where('id', '=', $previousId )->first(); 

        $interval =  date_diff(date_create($currentTrip->Time),date_create($previousTrip->Time)); 
       // dd($interval);       
        $tripUpdate = DB::connection('mysql')->table('baseline')->where('id', '=', $trip->id)->update([

           'EventDuration' => $interval->format('%H:%I:%S')
        ]); 

        $previousId = $currentTrip->id;

        }

       } 

       dd('done..');
       
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function movingStationary()
    {
        ini_set('max_execution_time', 3600000000); // 3600 seconds = 60 minutes
        set_time_limit(3600000000);

        $truckData = DB::connection('mysql')->table('baseline')->groupBy('Truck')->orderBy('id')->get();
        $truckData = $truckData->take(1);
   
         foreach ($truckData as $truckCode => $rows) {
     
         $trucks =  DB::connection('mysql')->table('baseline')->where('Truck', '=', $rows->Truck)->orderBy('Date')->orderBy('Time')->get();
           //  dd($trucks);
        foreach ($trucks as $trip) {

            if($trip->CoordinateTest == 0){

                $update = DB::connection('mysql')->table('baseline')->where('id', '=', $trip->id)->update([

                    'StationaryMoving' => 'Moving'
                ]);

            }elseif($trip->CoordinateTest == 1 && $trip->Distance > 0.0 && $trip->HighSpeed > 0){

                $update = DB::connection('mysql')->table('baseline')->where('id', '=', $trip->id)->update([

                    'StationaryMoving' => 'Moving'
                ]);

            }else{

                $update = DB::connection('mysql')->table('baseline')->where('id', '=', $trip->id)->update([

                    'StationaryMoving' => 'Stationary'
                ]);
            }
        }

      }

        dd('done..');
    }

    /**
     * Update the specified resource in storage.
     */
    public function truckLogic()
    {       
        
        ini_set('max_execution_time', 3600); // 3600 seconds = 60 minutes
        set_time_limit(3600);

        $truckData = DB::connection('mysql')->table('baseline')->groupBy('Truck')->orderBy('id')->get();
       $truckData = $truckData->take(2);
  
        foreach ($truckData as $truckCode => $rows) {
    
        $trucks =  DB::connection('mysql')->table('baseline')->where('Truck', '=', $rows->Truck)->where('id', '>', $rows->id)->get();
         //   dd($trucks);
       foreach ($trucks as $trip) {
        // Your logic for each row within the same truck code
    
        $currentTrip = DB::connection('mysql')->table('baseline')->where('id', '=', $trip->id)->first(); 
            
        $previousTrip = DB::connection('mysql')->table('baseline')->where('id', '=', $trip->id - 1)->first(); 

        $interval =  date_diff(date_create($currentTrip->Time),date_create($previousTrip->Time));        
       // dd($interval);
        $tripUpdate = DB::connection('mysql')->table('baseline')->where('id', '=', $trip->id)->update([

           'TimeDifference' => $interval->format('%H:%I:%S')
        ]); 

        }

        // Logic for the first row of the next truck code
      }

      dd('done...');

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy()
    {
        //
    }
}
