<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use DateTime;
use DateInterval;
use App\Imports\BaselineImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Log;

class BaselineController extends Controller
{

 
  public function RunBaseline()
  {
  //first phase baseline
      // $this->timeDifference();
      // $this->LongDifference();
      // $this->LatDifference();
      // $this->CoordinateTest();
      // $this->movingStationary();
      // $this->Count();
      // $this->OnTheRoad();
      // $this->TripStart();
    //  $this->tripEnd();
     // $this->TripTest();
     // $this->TripTestUpdated();
     // $this->cycleTime();
    //  $this->geofence();

    //second phase baseline
    
       $this->BiTripEnd();
       $this->BiTripStart();
       $this->BiTripStart2();
       $this->BiTimeCalculation();
       $this->LoadingTimes();
       $this->RbayTrips();
       $this->TripTime();
       $this->LoadingTimesv2();
      $this->TripF1();
      $this->ShiftClass();
      $this->TripRoute();
   

  }


      public function LongDifference()
    {

        ini_set('max_execution_time', 3600000000); // 3600 seconds = 60 minutes
        set_time_limit(3600000000);
           
        $truckData = DB::connection('mysql')->table('baseline')->whereBetween('Date', ['2023-10-01' , '2023-10-31'])->groupBy('Truck')->orderBy('id')->get();
        // $truckData = $truckData->take(2);
        //   dd($truckData);

         foreach ($truckData as $truckCode => $rows) {

          Log::info('Started Longitude Difference on', ['Truck' => $rows->Truck, '#' => $truckCode]);
          $startDate = '2023-10-01'; // Replace with your start date
          $endDate = '2023-10-31';   // Replace with your end date

          // Convert to DateTime objects
          $startDateTime = new DateTime($startDate);
          $endDateTime = new DateTime($endDate);
     
         $trucks =  DB::connection('mysql')->table('baseline')->where('Truck', '=', $rows->Truck)->whereBetween('Date', [$startDateTime, $endDateTime])->orderBy('Date')->orderBy('Time')->get();
        // dd($trucks);
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

        Log::info('Finshed Longitude Difference on', ['Truck' => $rows->Truck, '#' => $truckCode]);

      }
   
    }

    public function LatDifference()
    {
        ini_set('max_execution_time', 36000000); // 3600 seconds = 60 minutes
        set_time_limit(360000000);

        $truckData = DB::connection('mysql')->table('baseline')->whereBetween('Date', ['2023-10-01' , '2023-10-31'])->groupBy('Truck')->orderBy('id')->get();

         foreach ($truckData as $truckCode => $rows) {

          Log::info('Started Latitude Difference on', ['Truck' => $rows->Truck,  '#' => $truckCode]);
          $startDate = '2023-10-01'; // Replace with your start date
          $endDate = '2023-10-31';  // Replace with your end date

          // Convert to DateTime objects
          $startDateTime = new DateTime($startDate);
          $endDateTime = new DateTime($endDate);
     
         $trucks =  DB::connection('mysql')->table('baseline')->where('Truck', '=', $rows->Truck)->whereBetween('Date', [$startDateTime, $endDateTime])->orderBy('Date')->orderBy('Time')->get();
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

        Log::info('Finshed Latitude Difference on', ['Truck' => $rows->Truck,  '#' => $truckCode]);
   
      }
 
    }

    public function CoordinateTest()
    {
        ini_set('max_execution_time', 360000000); // 3600 seconds = 60 minutes
        set_time_limit(36000000);

      
        $truckData = DB::connection('mysql')->table('baseline')->whereBetween('Date', ['2023-10-01' , '2023-10-31'])->groupBy('Truck')->orderBy('id')->get();

         foreach ($truckData as $truckCode => $rows) {

          Log::info('Started Coordinate Test on', ['Truck' => $rows->Truck,  '#' => $truckCode]);
          $startDate = '2023-10-01'; // Replace with your start date
          $endDate = '2023-10-31';  // Replace with your end date

          // Convert to DateTime objects
          $startDateTime = new DateTime($startDate);
          $endDateTime = new DateTime($endDate);
     
         $trucks =  DB::connection('mysql')->table('baseline')->whereBetween('Date', [$startDateTime, $endDateTime])->where('Truck', '=', $rows->Truck)->orderBy('Date')->orderBy('Time')->get();
       //  dd($trucks);

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

        Log::info('Finished Coordinate Test on', ['Truck' => $rows->Truck,  '#' => $truckCode]);

       }
   
    }
 
     public function Count(){

        ini_set('max_execution_time', 360000000); // 3600 seconds = 60 minutes
        set_time_limit(360000000);

        $truckData = DB::connection('mysql')->table('baseline')->whereBetween('Date', ['2023-10-01' , '2023-10-31'])->groupBy('Truck')->orderBy('id')->get();

        //dd($truckData);
   
         foreach ($truckData as $truckCode => $rows) {

          Log::info('Started Count on', ['Truck' => $rows->Truck,  '#' => $truckCode]);
          $startDate = '2023-10-01'; // Replace with your start date
          $endDate = '2023-10-31'; // Replace with your end date

          // Convert to DateTime objects
          $startDateTime = new DateTime($startDate);
          $endDateTime = new DateTime($endDate);
     
          $count =  DB::connection('mysql')->table('baseline')->whereBetween('Date', [$startDateTime, $endDateTime])->where('Truck', '=', $rows->Truck)->orderBy('Date')->orderBy('Time')->count();
          if($count > 0){
         $trucks =  DB::connection('mysql')->table('baseline')->whereBetween('Date', [$startDateTime, $endDateTime])->where('Truck', '=', $rows->Truck)->orderBy('Date')->orderBy('Time')->skip(1)->take($count - 1)->get();
       //  $trucks =  DB::connection('mysql')->table('baseline')->where('Truck', '=', $rows->Truck)->where('id', '!=', $rows->id)->orderBy('Date')->orderBy('Time')->get();
         $prevTruck =  DB::connection('mysql')->table('baseline')->whereBetween('Date', [$startDateTime, $endDateTime])->where('Truck', '=', $rows->Truck)->orderBy('Date')->orderBy('Time')->first();
             //dd($trucks);
        foreach ($trucks as  $trip) {

           $prev = $prevTruck->id;
          // dd($trip);
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

       Log::info('Finished Count on', ['Truck' => $rows->Truck,  '#' => $truckCode]);
    
      }

  
     }


   public function CumulativeTime(){

      ini_set('max_execution_time', 360000000); // 3600 seconds = 60 minutes
      set_time_limit(360000000);
 
     $truckData = DB::connection('mysql')->table('baseline')->whereBetween('Date', ['2023-10-01' , '2023-10-31'])->groupBy('Truck')->orderBy('id')->get();

     // $truckData = $truckData->take(1);
 
      foreach ($truckData as $truckCode => $rows) {

        Log::info('Started CumulativeTime', ['Truck' => $rows->Truck,  '#' => $truckCode]);
        $startDate = '2023-10-01'; // Replace with your start date
        $endDate = '2023-10-31';   // Replace with your end date

        // Convert to DateTime objects
        $startDateTime = new DateTime($startDate);
        $endDateTime = new DateTime($endDate);
   
        $count =  DB::connection('mysql')->table('baseline')->whereBetween('Date', [$startDateTime, $endDateTime])->where('Truck', '=', $rows->Truck)->orderBy('Date')->orderBy('Time')->count();
        if($count > 0){
       $trucks =  DB::connection('mysql')->table('baseline')->whereBetween('Date', [$startDateTime, $endDateTime])->where('Truck', '=', $rows->Truck)->orderBy('Date')->orderBy('Time')->skip(1)->take($count - 1)->get();
     //  $trucks =  DB::connection('mysql')->table('baseline')->where('Truck', '=', $rows->Truck)->where('id', '!=', $rows->id)->orderBy('Date')->orderBy('Time')->get();
       $prevTruck =  DB::connection('mysql')->table('baseline')->whereBetween('Date', [$startDateTime, $endDateTime])->where('Truck', '=', $rows->Truck)->orderBy('Date')->orderBy('Time')->first();
        //  dd($trucks);

      foreach ($trucks as  $trip) {

         $prev = $prevTruck->id;

      $currentTrip = $trip->StationaryMoving;

      $previousFullTrip = DB::connection('mysql')->table('baseline')->where('id', '=', $prev)->first();
 
      if($trip->StationaryMoving == 'Stationary' &&  $previousFullTrip->StationaryMoving == 'Stationary'){
    
       // dd($previousFullTrip->CumulativeTime,$trip->TimeDifference);
       if( $previousFullTrip->CumulativeTime == null){
               
        $cumulativeTime = $previousFullTrip->TimeDifference;

       }else{
        $cumulativeTime = $previousFullTrip->CumulativeTime;
       }
              
      
          $time1 = DateTime::createFromFormat('H:i:s',  $cumulativeTime);
          $time2 = DateTime::createFromFormat('H:i:s', $trip->TimeDifference);
          // Add the two time intervals
          $time1->add(new DateInterval('PT' . $time2->format('H') . 'H' . $time2->format('i') . 'M' . $time2->format('s') . 'S'));

          // Get the result
          $result = $time1->format('H:i:s');
         $updateCount = DB::connection('mysql')->table('baseline')->where('id', '=', $trip->id)->update([

             'CumulativeTime' =>  $result
         ]);

      }

       $prevTruck = $trip;
 
     }

    }

     Log::info('Finished CumulativeTime on', ['Truck' => $rows->Truck,  '#' => $truckCode]);
  
     }

     dd('Done');

 }

     public function OnTheRoad(){

            //On The Road COLUMN
            ini_set('max_execution_time', 360000000); // 3600 seconds = 60 minutes
            set_time_limit(3600000000);
    
            $truckData = DB::connection('mysql')->table('baseline')->whereBetween('Date', ['2023-10-01' , '2023-10-31'])->groupBy('Truck')->orderBy('id')->get();

        //dd($truckData);
   
         foreach ($truckData as $truckCode => $rows) {

          Log::info('Started ontheroad on', ['Truck' => $rows->Truck,  '#' => $truckCode]);
          $startDate = '2023-10-01'; // Replace with your start date
          $endDate = '2023-10-31';  // Replace with your end date

          // Convert to DateTime objects
          $startDateTime = new DateTime($startDate);
          $endDateTime = new DateTime($endDate);
     
         $trucks =  DB::connection('mysql')->table('baseline')->whereBetween('Date', [$startDateTime, $endDateTime])->where('Truck', '=', $rows->Truck)->orderBy('Date')->orderBy('Time')->get();
      //  dd($trucks);
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

          Log::info('Finished ontheroad on', ['Truck' => $rows->Truck,  '#' => $truckCode]);

       }

   
     }

     public function TripStart(){
      
      ini_set('max_execution_time', 3600000000); // 3600 seconds = 60 minutes
      set_time_limit(3600000000);

     
      $truckData = DB::connection('mysql')->table('baseline')->whereBetween('Date', ['2023-10-01' , '2023-10-31'])->groupBy('Truck')->orderBy('id')->get();

       foreach ($truckData as $truckCode => $rows) {
        Log::info('Started trip start on', ['Truck' => $rows->Truck,  '#' => $truckCode]);
        $startDate = '2023-10-01'; // Replace with your start date
        $endDate = '2023-10-31';   // Replace with your end date

        // Convert to DateTime objects
        $startDateTime = new DateTime($startDate);
        $endDateTime = new DateTime($endDate);
   
        $count =  DB::connection('mysql')->table('baseline')->whereBetween('Date', [$startDateTime, $endDateTime])->where('Truck', '=', $rows->Truck)->orderBy('Date')->orderBy('Time')->count();

        if($count > 0){
       $trucks =  DB::connection('mysql')->table('baseline')->whereBetween('Date', [$startDateTime, $endDateTime])->where('Truck', '=', $rows->Truck)->orderBy('Date')->orderBy('Time')->skip(1)->take($count - 1)->get();
       $prevTruck =  DB::connection('mysql')->table('baseline')->whereBetween('Date', [$startDateTime, $endDateTime])->where('Truck', '=', $rows->Truck)->orderBy('Date')->orderBy('Time')->first();
        //   dd($trucks);
      foreach ($trucks as $truckrows => $trip) {
   
          $currentTrip = $trip->OnTheRoad;
          $prev = $prevTruck->id;
    
         $previousFullTrip = DB::connection('mysql')->table('baseline')->where('id', '=',  $prev)->first();

        if($currentTrip == 'on the road' AND $previousFullTrip->OnTheRoad == 'False'){

          // $trucksArray = $trucks->toArray(); 
          // $seventeenth = array_slice($trucksArray,$truckrows - 17, 1);
          // $seventeenthRow = end($seventeenth);
          $seven = $truckrows - 17;
         // dd($truckrows,$seventeenthRow->id,$trucks[$seven]->id);
          $updatetripstart = DB::connection('mysql')->table('baseline')->where('id', '=', $trucks[$seven]->id)->where('Truck', '=', $rows->Truck)->update([

              'TripStart' => 'Trip Start'
          ]);

        }

        $prevTruck = $trip;
       
       }

      }

      Log::info('Finished trip start on', ['Truck' => $rows->Truck,  '#' => $truckCode]);

      }


   }




     public function TripTest(){

        ini_set('max_execution_time', 3600000000); // 3600 seconds = 60 minutes
        set_time_limit(360000000);

        $truckData = DB::connection('mysql')->table('baseline')->whereBetween('Date', ['2023-10-01' , '2023-10-31'])->groupBy('Truck')->orderBy('id')->get();
       // dd($truckDataCount,$truckData);
   
         foreach ($truckData as $truckCode => $rows) {
          Log::info('Started trip test on', ['Truck' => $rows->Truck,  '#' => $truckCode]);
          $startDate = '2023-10-01'; // Replace with your start date
          $endDate = '2023-10-31';   // Replace with your end date
  
          // Convert to DateTime objects
          $startDateTime = new DateTime($startDate);
          $endDateTime = new DateTime($endDate);
     
          $count =  DB::connection('mysql')->table('baseline')->where('Truck', '=', $rows->Truck)->whereBetween('Date', [$startDateTime, $endDateTime])->where('Truck', '=', $rows->Truck)->orderBy('Date')->orderBy('Time')->count();

          if($count > 0){

         $trucks =  DB::connection('mysql')->table('baseline')->where('Truck', '=', $rows->Truck)->whereBetween('Date', [$startDateTime, $endDateTime])->where('Truck', '=', $rows->Truck)->orderBy('Date')->orderBy('Time')->skip(1)->take($count - 1)->get();
           //  dd($trucks);
        foreach ($trucks as  $truckrows => $trip) {
         
            if($trip->TripStart == 'Trip Start'){

                $updatetriptest = DB::connection('mysql')->table('baseline')->where('id', '=', $trip->id)->update([

                    'TripTest' => 'Trip Start'
    
                   ]);
                    
            }

            if($trip->TripEnd == 'Trip Ended'){

                $updatetriptest = DB::connection('mysql')->table('baseline')->where('id', '=', $trip->id)->update([

                    'TripTest' => 'Trip Ended'
    
                   ]);
            }


        }

      }

        Log::info('Finished trip test on', ['Truck' => $rows->Truck,  '#' => $truckCode]);

       

      }


   }


        public function TripTestUpdated(){

            ini_set('max_execution_time', 3600000000); // 3600 seconds = 60 minutes
            set_time_limit(3600000000000);
           
            
            $truckData = DB::connection('mysql')->table('baseline')->whereBetween('Date', ['2023-10-01' , '2023-10-31'])->groupBy('Truck')->orderBy('id')->get();
  
   
         foreach ($truckData as $truckCode => $rows) {
  
          Log::info('Started trip test updated on', ['Truck' => $rows->Truck,  '#' => $truckCode]);
          $startDate = '2023-10-01'; // Replace with your start date
          $endDate = '2023-10-31'; // Replace with your end date
  
          // Convert to DateTime objects
          $startDateTime = new DateTime($startDate);
          $endDateTime = new DateTime($endDate);
     
          $count =  DB::connection('mysql')->table('baseline')->where('Truck', '=', $rows->Truck)->whereBetween('Date', [$startDateTime, $endDateTime])->where('TripTest', '=', 'Trip Start')->orWhere('TripTest', '=', 'Trip Ended')->where('Truck', '=', $rows->Truck)->orderBy('Date')->orderBy('Time')->count();

          if($count > 0){

          $trucks =  DB::connection('mysql')->table('baseline')->where('Truck', '=', $rows->Truck)->whereBetween('Date', [$startDateTime, $endDateTime])->where('TripTest', '=', 'Trip Start')->orWhere('TripTest', '=', 'Trip Ended')->where('Truck', '=', $rows->Truck)->orderBy('Date')->orderBy('Time')->skip(1)->take($count - 1)->get();
    
           //  dd($trucks,$rows->Truck,$count);

            foreach($trucks as $truckrows => $trip){


              if($truckrows != ($count - 2)){  

             
                $TripEnd = $trip;
                $nexttrip = $trucks[$truckrows + 1];
              //  dd($TripEnd,$nexttrip);

                if($TripEnd->TripTest == 'Trip Ended' && $nexttrip->TripTest == 'Trip Start'){
                //  dd($TripEnd,$nexttrip);
                
                $interval =  date_diff(date_create($TripEnd->Time),date_create($nexttrip->Time)); 
    
                $minutes = $interval->days * 24 * 60; 
                $minutes += $interval->h * 60; 
                $minutes += $interval->i; 

                
                if($minutes < 10){
                 
                  $update1 =  DB::connection('mysql')->table('baseline')->where('id','=',$TripEnd->id)->update([
    
                   'TripTest' => null,
                   'Trip' => '2'
                  ]);

                  
                  $update2 =  DB::connection('mysql')->table('baseline')->where('id','=',$nexttrip->id)->update([
    
                    'TripTest' => null,
                    'Trip' => '2'
                   ]);

                 //  dd($minutes,$TripEnd,$nexttrip);        
                }
                
            }
    
            }

           }

          }

           Log::info('Finished trip test updated on', ['Truck' => $rows->Truck,  '#' => $truckCode]);

         }

        }


     public function sortDateTime()
     {
         ini_set('max_execution_time', 3600000); // 3600 seconds = 60 minutes
         set_time_limit(3600000);

         $times = DB::connection('mysql')->table('baselinetest')->where('id', '>', 0)->get(); 
         
         foreach($times as $trip){
      
          
          $timeParts = explode(':', $trip->EventDuration);
          $totalSeconds = $timeParts[0] * 3600 + $timeParts[1] * 60 + $timeParts[2];

        //  dd($totalSeconds,$trip->EventDuration,$trip);

          $createTrip = DB::connection('mysql')->table('baselinetest')->where('id','=', $trip->id)->update([
         
 
            'Longitude' => $trip->Longitude,  
            'Latitude' => $trip->Latitude,
            'EventDuration' => $totalSeconds

  
          ]);
  
          }

          dd('done..');

    }
  
   
    public function tripEnd()
    {
        ini_set('max_execution_time', 36000000000); // 3600 seconds = 60 minutes
        set_time_limit(36000000000);
       // dd('testing');
       
         $truckData = DB::connection('mysql')->table('baseline')->whereBetween('Date', ['2023-10-01' , '2023-10-31'])->groupBy('Truck')->orderBy('id')->get();

      //dd($truckData);
 
       foreach ($truckData as $truckCode => $rows) {

        Log::info('Started trip end  on', ['Truck' => $rows->Truck,  '#' => $truckCode]);
        $startDate = '2023-10-01'; // Replace with your start date
        $endDate = '2023-10-31';   // Replace with your end date

        // Convert to DateTime objects
        $startDateTime = new DateTime($startDate);
        $endDateTime = new DateTime($endDate);
   
        $count =  DB::connection('mysql')->table('baseline')->whereBetween('Date', [$startDateTime, $endDateTime])->where('Truck', '=', $rows->Truck)->orderBy('Date')->orderBy('Time')->count();
        if($count > 0){
        $trucks =  DB::connection('mysql')->table('baseline')->whereBetween('Date', [$startDateTime, $endDateTime])->where('Truck', '=', $rows->Truck)->orderBy('Date')->orderBy('Time')->skip(1)->take($count - 1)->get();
       // $prevTruck =  DB::connection('mysql')->table('baseline')->where('Truck', '=', $rows->Truck)->whereBetween('Date', [$startDateTime, $endDateTime])->orderBy('Date')->orderBy('Time')->first();
           //dd($trucks);
        foreach ($trucks as $truckrows => $trip) {

         // dd($count,$trucks);

          if($truckrows != ($count - 2)){       
        
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

      }
      Log::info('Finished trip end  on', ['Truck' => $rows->Truck,  '#' => $truckCode]);

      }
   
    }

 
    public function geofence()
    {
        ini_set('max_execution_time', 3600000000000); // 3600 seconds = 60 minutes
        set_time_limit(3600000000000);
    
        $truckData = DB::connection('mysql')->table('baseline')->whereBetween('Date', ['2023-10-01' , '2023-10-31'])->groupBy('Truck')->orderBy('id')->get();
  
         foreach ($truckData as $truckCode => $rows) {
          Log::info('Started geofence  on', ['Truck' => $rows->Truck,  '#' => $truckCode]);
          $startDate = '2023-10-01'; // Replace with your start date
          $endDate = '2023-10-31';   // Replace with your end date
  
          // Convert to DateTime objects
          $startDateTime = new DateTime($startDate);
          $endDateTime = new DateTime($endDate);
     
          //$count =  DB::connection('mysql')->table('baseline')->where('Truck', '=', $rows->Truck)->whereBetween('Date', [$startDateTime, $endDateTime])->orderBy('Date')->orderBy('Time')->count();
      //   $trucks =  DB::connection('mysql')->table('baseline')->where('Truck', '=', $rows->Truck)->whereBetween('Date', [$startDateTime, $endDateTime])->orderBy('Date')->orderBy('Time')->skip(1)->take($count - 1)->get();
     
         $trucks =  DB::connection('mysql')->table('baseline')->where('Truck', '=', $rows->Truck)->whereBetween('Date', [$startDateTime, $endDateTime])->where('TripTest', '=', 'Trip Start')->orwhere('TripTest', '=', 'Trip Ended')->where('Truck', '=', $rows->Truck)->orderBy('Date')->orderBy('Time')->get();
          //   dd($trucks);
        foreach ($trucks as  $truckrows => $trip) {
        
            
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
        if($shortestDistance < 2500){

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
        ini_set('max_execution_time', 360000000); // 3600 seconds = 60 minutes
         set_time_limit(3600000000);
        // Given test point
        $lat1 = $trip->Latitude;
        $lon1 = $trip->Longitude;
        // Other points to compare
        $lat2 = $geofence->LowLat;
        $lon2 = $geofence->LowLong;

        // Calculate distance for each point and find the shortest distance
        $distance = $this->haversineDistance($lat1, $lon1, $lat2, $lon2);
       // dd($distance);

        if($distance < 2500){

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
     Log::info('Finished geofence on', ['Truck' => $rows->Truck,  '#' => $truckCode]);

     }

     Log::info('Baseline Finished', ['Truck' => 'All']);
     dd("Finally done");
     die("Execution stopped.");
 

    }

    private function haversineDistance($lat1, $lon1, $lat2, $lon2)
    {

      ini_set('max_execution_time', 360000000); // 3600 seconds = 60 minutes
      set_time_limit(3600000000);


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

        ini_set('max_execution_time', 3600000000000); // 3600 seconds = 60 minutes
        set_time_limit(360000000000);

        $truckData = DB::connection('mysql')->table('baseline')->whereBetween('Date', ['2023-10-01' , '2023-10-31'])->groupBy('Truck')->orderBy('id')->get();
       // dd($truckData);

         foreach ($truckData as $truckCode => $rows) {

          Log::info('Started Time Difference on', ['Truck' => $rows->Truck,  '#' => $truckCode]);
          $startDate = '2023-10-01'; // Replace with your start date
          $endDate = '2023-10-31';   // Replace with your end date

          // Convert to DateTime objects
          $startDateTime = new DateTime($startDate);
          $endDateTime = new DateTime($endDate);
     
         $trucks =  DB::connection('mysql')->table('baseline')->where('Truck', '=', $rows->Truck)->whereBetween('Date', [$startDateTime, $endDateTime])->orderBy('Date')->orderBy('Time')->get();
         //dd($trucks);
          foreach ($trucks as  $truckrows => $trip) {

        $currentTrip = DB::connection('mysql')->table('baseline')->where('id', '=', $trip->id)->first(); 
  
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

        Log::info('Finished Time Difference on', ['Truck' => $rows->Truck,  '#' => $truckCode]);

      }

     // dd('done with time difference');
   
    }

    public function cycleTime()
    {
        
        ini_set('max_execution_time', 360000000000); // 3600 seconds = 60 minutes
        set_time_limit(360000000000);

        $truckData = DB::connection('mysql')->table('baseline')->whereBetween('Date', ['2023-10-01' , '2023-10-31'])->groupBy('Truck')->orderBy('id')->get();
   
         foreach ($truckData as $truckCode => $rows) {

          Log::info('Started cycleTime on', ['Truck' => $rows->Truck,  '#' => $truckCode]);
          $startDate = '2023-10-01'; // Replace with your start date
          $endDate = '2023-10-31';  // Replace with your end date
  
          // Convert to DateTime objects
          $startDateTime = new DateTime($startDate);
          $endDateTime = new DateTime($endDate);
    
    
        $prevTruck =  DB::connection('mysql')->table('baseline')->where('Truck', '=', $rows->Truck)->whereBetween('Date', [$startDateTime, $endDateTime])->where('Truck', '=', $rows->Truck)->orderBy('Date')->orderBy('Time')->first();
    
       $trucks = DB::connection('mysql')->table('baseline')->where('Truck', '=', $rows->Truck)->whereBetween('Date', [$startDateTime, $endDateTime])->where('TripTest', '=', 'Trip Start')->orWhere('TripTest', '=', 'Trip Ended')->where('Truck', '=', $rows->Truck)->orderBy('Date')->orderBy('Time')->get();
      
       if($prevTruck != null){
      
        $previousId =  $prevTruck->id;
       }

     
      //  $trucks =  DB::connection('mysql')->table('baseline')->where('Truck', '=', $rows->Truck)->where('id', '>', $rows->id)->get();
           //  dd($trucks->Truck);
        foreach ($trucks as  $truckrows => $trip) {

        $currentTrip = DB::connection('mysql')->table('baseline')->where('id', '=', $trip->id)->first(); 
            
        $previousTrip = DB::connection('mysql')->table('baseline')->where('id', '=', $previousId )->first(); 

        $interval =  date_diff(date_create($currentTrip->Time),date_create($previousTrip->Time)); 
       // dd($interval);  
       
       if($trip->TripTest == 'Trip Start'){

        $cycle = 'Load/Offload Time';

       }elseif($trip->TripTest == 'Trip Ended'){

        $cycle = 'Travel Time';

       }else{

        $cycle = null;

       }


        $tripUpdate = DB::connection('mysql')->table('baseline')->where('id', '=', $trip->id)->update([

           'EventDuration' => $interval->format('%H:%I:%S'),
           'CycleTimeEvent' => $cycle

        ]); 

        $previousId = $currentTrip->id;

        }

        Log::info('Finished cycle Time on', ['Truck' => $rows->Truck,  '#' => $truckCode]);

       } 

     
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function movingStationary()
    {
        ini_set('max_execution_time', 3600000000000); // 3600 seconds = 60 minutes
        set_time_limit(360000000000);
       
        $truckData = DB::connection('mysql')->table('baseline')->whereBetween('Date', ['2023-10-01' , '2023-10-31'])->groupBy('Truck')->orderBy('id')->get();

   
         foreach ($truckData as $truckCode => $rows) {
          Log::info('started movingStationary on', ['Truck' => $rows->Truck,  '#' => $truckCode]);
          $startDate = '2023-10-01'; // Replace with your start date
          $endDate = '2023-10-31';  // Replace with your end date

          // Convert to DateTime objects
          $startDateTime = new DateTime($startDate);
          $endDateTime = new DateTime($endDate);
     
         $trucks =  DB::connection('mysql')->table('baseline')->where('Truck', '=', $rows->Truck)->whereBetween('Date', [$startDateTime, $endDateTime])->orderBy('Date')->orderBy('Time')->get();
      //  dd($trucks);
        foreach ($trucks as $truckrows =>$trip) {

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

        Log::info('Finished movingStationary on', ['Truck' => $rows->Truck,  '#' => $truckCode]);

      }

    }

    /**
     * Update the specified resource in storage.
     */
    public function truckLogic()
    {    
      
      ini_set('max_execution_time', 3600000000000); // 3600 seconds = 60 minutes
      set_time_limit(360000000000);
     
      $truckData = DB::connection('mysql')->table('baseline')->whereBetween('Date', ['2023-10-01' , '2023-10-31'])->groupBy('Truck')->orderBy('id')->get();

       foreach ($truckData as $truckCode => $rows) {
        Log::info('started transfer on', ['Truck' => $rows->Truck,  '#' => $truckCode]);
        $startDate = '2023-10-01'; // Replace with your start date
        $endDate = '2023-10-31';   // Replace with your end date

        // Convert to DateTime objects
        $startDateTime = new DateTime($startDate);
        $endDateTime = new DateTime($endDate);
   
        $trucks =  DB::connection('mysql')->table('baseline')->where('Truck', '=', $rows->Truck)->whereBetween('Date', [$startDateTime, $endDateTime])->where('TripTest', '=', 'Trip Start')->orwhere('TripTest', '=', 'Trip Ended')->whereBetween('Date', [$startDateTime, $endDateTime])->where('Truck', '=', $rows->Truck)->orderBy('Date')->orderBy('Time')->get();
    
      foreach ($trucks as $truckrows =>$trip) {

        $createTrip = DB::connection('mysql')->table('baselinetest')->insert([
         
          'Date' => $trip->Date,
          'BaseId' => $trip->id,
          'Truck' => $trip->Truck,
          'Time' => $trip->Time,
          'Distance' => $trip->Distance,
          'HighSpeed' => $trip->HighSpeed,
          'Latitude' => $trip->Latitude,
          'Longitude' => $trip->Longitude,
          'TimeDifference' => $trip->TimeDifference,
          'LongitudeDifference' => $trip->LongitudeDifference,
          'LatitudeDifference' => $trip->LatitudeDifference,
          'CoordinateTest' => $trip->CoordinateTest,
          'StationaryMoving' => $trip->StationaryMoving,
          'Count' => $trip->Count,
          'OnTheRoad' => $trip->OnTheRoad,
          'TripStart' => $trip->TripStart,
          'TripEnd' => $trip->TripEnd,
          'TripTest' => $trip->TripTest,
          'Trip' => $trip->Trip,
          'EventDuration' => $trip->EventDuration,
          'CycleTimeEvent' => $trip->CycleTimeEvent,
          'Geofence' => $trip->Geofence

        ]);

      }

      Log::info('finished transfer on', ['Truck' => $rows->Truck,  '#' => $truckCode]);

    }
             
    }

    public function newBase()
    {    
      
      ini_set('max_execution_time', 3600000000000); // 3600 seconds = 60 minutes
      set_time_limit(360000000000);
     
   
        $trucks =  DB::connection('mysql')->table('baselinetest')->where('id', '>', 0 )->orderBy('Truck')->orderBy('Date')->orderBy('Time')->get();
     //  dd($trucks);
      foreach ($trucks as $truckrows => $trip) {

        $count = DB::connection('mysql')->table('newbase')->where('BaseId', '=', $trip->BaseId)->count();
      // dd($count);
        if($count == 0){

        $createTrip = DB::connection('mysql')->table('newbase')->insert([
         
          'Date' => $trip->Date,
          'BaseId' => $trip->BaseId,
          'Truck' => $trip->Truck,
          'Time' => $trip->Time,
          'Distance' => $trip->Distance,
          'HighSpeed' => $trip->HighSpeed,
          'Latitude' => $trip->Latitude,
          'Longitude' => $trip->Longitude,
          'TimeDifference' => $trip->TimeDifference,
          'LongitudeDifference' => $trip->LongitudeDifference,
          'LatitudeDifference' => $trip->LatitudeDifference,
          'CoordinateTest' => $trip->CoordinateTest,
          'StationaryMoving' => $trip->StationaryMoving,
          'Count' => $trip->Count,
          'OnTheRoad' => $trip->OnTheRoad,
          'TripStart' => $trip->TripStart,
          'TripEnd' => $trip->TripEnd,
          'TripTest' => $trip->TripTest,
          'Trip' => $trip->Trip,
          'EventDuration' => $trip->EventDuration,
          'CycleTimeEvent' => $trip->CycleTimeEvent,
          'Geofence' => $trip->Geofence

        ]);

      }

      }
      dd('done');
      Log::info('finished transfer on', ['Truck' => $rows->Truck,  '#' => $truckCode]);
             
    }


    public function updateLong()
   {     
      
      ini_set('max_execution_time', 3600000000000); // 3600 seconds = 60 minutes
      set_time_limit(360000000000);

      //   dd('we here....');
       $truckData =  DB::connection('mysql')->table('baselinetest')->where('Truck','=','SL274 KTW871MP')->groupby('Truck')->orderBy('id')->get();

      // dd($truckData);

       foreach ($truckData as $truckCode => $rows) {
        Log::info('started fuel on', ['Truck' => $rows->Truck, 'row #' => $truckCode]);

        $string = $rows->Truck;
        $substring = 'Workshop';
        $substring1 = 'Parked';

      if (strpos($string, $substring) !== false) {

       $results = str_replace($substring, '', $string);
     //'The string contains the word "Workshop"'
      } elseif(strpos($string, $substring1) !== false) {

       $results = str_replace($substring1, '', $string);

      }else{

       $results = $rows->Truck;
      }

       $truckMap =  DB::connection('mysql')->table('truckmap')->where('Make' ,'=', 'MAN')->where('Truck','=', $results )->count();
     //  dd($truckMap,$results);
       
      if($truckMap > 0){

        $truckCount =  DB::connection('mysql')->table('baselinetest')->where('Truck' ,'=', $results )->orderby('DateUpdated')->orderBy('Time')->count();

        $trucks =  DB::connection('mysql')->table('baselinetest')->where('Truck' ,'=', $results  )->orderby('DateUpdated')->orderBy('Time')->get();
       // dd( $truckCount,$trucks);
       
      foreach ($trucks as $truckrows => $trip) {

        if($truckrows < $truckCount - 1){
          Log::info('started fuel-sub on', ['Truck' => $trip->Truck, 'row #' => $truckrows]);
        $endtrip = DB::connection('mysql')->table('baselinetest')->where('id', '=', $trucks[$truckrows+1]->id)->first();
       // dd($trip,$endtrip);

        $string2 = $trip->Truck;
        $substring2 = 'Workshop';
        $substring2 = 'Parked';

      if (strpos($string2, $substring2) !== false) {

       $result = str_replace($substring2, '', $string2);
     //'The string contains the word "Workshop"'
      } elseif(strpos($string2, $substring2) !== false) {

       $result = str_replace($substring2, '', $string2);

      }else{

       $result = $rows->Truck;
      }


        $time = substr($trip->Time, 0, 8);
        $start_timestamp = $trip->DateUpdated . ' ' .$time;
       
        $endtime = substr($endtrip->Time, 0, 8);
        $end_timestamp =  $endtrip->DateUpdated . ' ' .$endtime ;
       // dd($start_timestamp,$end_timestamp);
        $truck = $result;
        $truck = str_replace(' ', '-', $truck);
       // $truck = 'SL235-KST829MP';
      //  dd($start_timestamp,$end_timestamp,$truck);
    
        $endpoint1 = 'https://fleetapi-za.cartrack.com/rest/fuel/consumed/'.urlencode($truck);
        $endpoint2 = 'https://fleetapi-za.cartrack.com/rest/vehicles/'.urlencode($truck).'/odometer'; // Change this to your second endpoint
    
        $url1 = $endpoint1 . '?start_timestamp=' . urlencode($start_timestamp) . '&end_timestamp=' . urlencode($end_timestamp);
        $url2 = $endpoint2 . '?start_timestamp=' . urlencode($start_timestamp) . '&end_timestamp=' . urlencode($end_timestamp);
    
        $token = "TUFOVDAwMjI2OmFiODQ5YTNjMDVlZmYzOWM2ZDgzMDkzMTNhNWRhYWFhYjNjOWQ2NzMyYWQ4MTkxYjI5NmQ3OWRhY2FmZGQ3NTE=";
    
        // First cURL request
        $ch1 = curl_init($url1);
        curl_setopt($ch1, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch1, CURLOPT_HTTPHEADER, [
            'Authorization: Basic ' . $token,
            'Content-Type: application/json'
        ]);
        $response1 = curl_exec($ch1);
        $http_code1 = curl_getinfo($ch1, CURLINFO_HTTP_CODE);
        curl_close($ch1);
    
        // Second cURL request
        $ch2 = curl_init($url2);
        curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch2, CURLOPT_HTTPHEADER, [
            'Authorization: Basic ' . $token,
            'Content-Type: application/json'
        ]);
        $response2 = curl_exec($ch2);
        $http_code2 = curl_getinfo($ch2, CURLINFO_HTTP_CODE);
        curl_close($ch2);
    
        $data1 = json_decode($response1);
        $data2 = json_decode($response2);

         // dd($data1,$data2);
       if($http_code1 == 200 && $http_code2 == 200 ){

        if($data1->data->fuel_consumed != 0 && $data2->data->distance != 0){

          $endtrip = DB::connection('mysql')->table('baselinetest')->where('id', '=',  $trip->id)->update([

            'fuelUsed' => $data1->data->fuel_consumed,
            'distanceCovered' => $data2->data->distance,
            'fuelConsumption' => 1/($data1->data->fuel_consumed/($data2->data->distance/1000))
          ]);

        }else{

          $endtrip = DB::connection('mysql')->table('baselinetest')->where('id', '=',  $trip->id)->update([

            'fuelUsed' => $data1->data->fuel_consumed,
            'distanceCovered' => $data2->data->distance,
            'fuelConsumption' => 0
          ]);
        }

     

        }

      //  dd($start_timestamp,$end_timestamp,$data1,$data2);

       }

      }

     }

     }
      Log::info('finished transfer on', ['Truck' => 'All']);
      dd('done');

   }             
 
     //1
    public function fleetboard()
    {

      ini_set('max_execution_time', 3600000000000); // 3600 seconds = 60 minutes
      set_time_limit(360000000000);

      // dd('we here....');
       $truckData =  DB::connection('mysql')->table('newbase')->where('id' ,'>', 0)->groupby('Truck')->orderBy('id')->get();
      // dd($truckData);

       foreach ($truckData as $truckCode => $rows) {

        Log::info('started fuel on', ['Truck' => $rows->Truck, 'row #' => $truckCode]);

        $truckMap =  DB::connection('mysql')->table('truckmap')->where('Make' ,'=', 'MAN')->where('Truck','=', $rows->Truck)->count();
       
        if($truckMap > 0){

        $truckCount =  DB::connection('mysql')->table('newbase')->where('Truck' ,'=', $rows->Truck )->orderby('Date')->orderBy('Time')->count();

        $trucks =  DB::connection('mysql')->table('newbase')->where('Truck' ,'=', $rows->Truck )->orderby('Date')->orderBy('Time')->get();
       // dd( $truckCount,$trucks);
       
        foreach ($trucks as $truckrows => $trip) {

        if($truckrows < $truckCount - 1){

        Log::info('started fuel-sub on', ['Truck' => $trip->Truck, 'row #' => $truckrows]);
        $endtrip = DB::connection('mysql')->table('newbase')->where('id', '=', $trucks[$truckrows+1]->id)->first();

        if($trip->TripTest == 'Trip Start' && $endtrip->TripTest == 'Trip Ended'){

        $time = substr($trip->Time, 0, 8);
        $start_timestamp = $trip->Date . ' ' .$time;
       
        $endtime = substr($endtrip->Time, 0, 8);
        $end_timestamp =  $endtrip->Date . ' ' .$endtime ;
       // dd($start_timestamp,$end_timestamp);
        $truck = $trip->Truck;
        $truck = str_replace(' ', '-', $truck);
       // $truck = 'SL235-KST829MP';
    
        $endpoint1 = 'https://fleetapi-za.cartrack.com/rest/fuel/consumed/'.urlencode($truck);
        $endpoint2 = 'https://fleetapi-za.cartrack.com/rest/vehicles/'.urlencode($truck).'/odometer'; // Change this to your second endpoint
    
        $url1 = $endpoint1 . '?start_timestamp=' . urlencode($start_timestamp) . '&end_timestamp=' . urlencode($end_timestamp);
        $url2 = $endpoint2 . '?start_timestamp=' . urlencode($start_timestamp) . '&end_timestamp=' . urlencode($end_timestamp);
    
        $token = "TUFOVDAwMjI2OmFiODQ5YTNjMDVlZmYzOWM2ZDgzMDkzMTNhNWRhYWFhYjNjOWQ2NzMyYWQ4MTkxYjI5NmQ3OWRhY2FmZGQ3NTE=";
    
        // First cURL request
        $ch1 = curl_init($url1);
        curl_setopt($ch1, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch1, CURLOPT_HTTPHEADER, [
            'Authorization: Basic ' . $token,
            'Content-Type: application/json'
        ]);

        $response1 = curl_exec($ch1);
        $http_code1 = curl_getinfo($ch1, CURLINFO_HTTP_CODE);
        curl_close($ch1);
    
        // Second cURL request
        $ch2 = curl_init($url2);
        curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch2, CURLOPT_HTTPHEADER, [
            'Authorization: Basic ' . $token,
            'Content-Type: application/json'
        ]);

        $response2 = curl_exec($ch2);
        $http_code2 = curl_getinfo($ch2, CURLINFO_HTTP_CODE);
        curl_close($ch2);
    
        $data1 = json_decode($response1);
        $data2 = json_decode($response2);

       if($http_code1 == 200 && $http_code2 == 200 ){

        $endtrip = DB::connection('mysql')->table('newbase')->where('id', '=',  $endtrip->id)->update([

          'fuelUsed' => $data1->data->fuel_consumed,
          'distanceCovered' => $data2->data->distance 
        ]);

         }

        }

       }

      }

     }

     }

      Log::info('finished transfer on', ['Truck' => 'All']);
      dd('done');
    }

      //2
    public function BiTripEnd(){

      ini_set('max_execution_time', 3600000000000); // 3600 seconds = 60 minutes
      set_time_limit(360000000000);
     
      $truckData = DB::connection('mysql')->table('baselinetest')->groupBy('Truck')->orderBy('id')->get();
       //  dd($truckData);

       foreach ($truckData as $truckCode => $rows) {

       $trucks =  DB::connection('mysql')->table('baselinetest')->where('Truck', '=', $rows->Truck)->orderBy('DateUpdated')->orderBy('Time')->get();

      foreach ($trucks as $truckrows =>$trip) {
       
        Log::info('started trip analysis on', ['Truck' => $rows->Truck,  '#' => $truckrows, 'id' => $trip->id] );

          $nextTripId = $trip->id + 1;
         $nextTrip =  DB::connection('mysql')->table('baselinetest')->where('id', '=', $nextTripId)->first();

       //  Trip End
        if($trip->TripClassification == 'Offloading Time' && $nextTrip->TripClassification  != 'to Witbank Yard' && $nextTrip->TripClassification  != 'Offloading Time'){

          $trucks =  DB::connection('mysql')->table('baselinetest')->where('id', '=', $trip->id)->update([

            'TripClassificationv2' => 'Trip End'
    
           ]);

        }

        if($trip->TripClassification == "Offloading Time" && $nextTrip->TripClassification  == "to Witbank Yard" ){
        //  dd($nextTrip);
          $tripUpdate =  DB::connection('mysql')->table('baselinetest')->where('id', '=', $nextTrip->id )->update([

            'TripClassificationv2' => 'Trip End'
    
           ]);

        }


        if($trip->TripClassification != 'Offloading Time' && $nextTrip->TripClassification  == 'to Witbank Yard' ){

        //   dd($trip,$trucks[$truckrows+1]);
           $tripUpdate =  DB::connection('mysql')->table('baselinetest')->where('id', '=', $nextTrip->id )->update([
 
             'TripClassificationv2' => 'Trip End'
     
            ]);
 
         }

  

         if($trip->TripClassification == 'Left Witbank Yard (Loading Trip)'){

          $tripUpdate =  DB::connection('mysql')->table('baselinetest')->where('id', '=', $trip->id )->update([
  
            'TripClassificationv2' => 'Trip Start'
    
           ]);
  
      }
  
      
      if($trip->TripClassification == 'Offloading Time' && $trip->TripClassificationv2  == 'Trip End'){
  
       $tripUpdate =  DB::connection('mysql')->table('baselinetest')->where('id', '=', $nextTrip->id )->update([
  
         'TripClassificationv2' => 'Trip Start'
  
        ]);
  
       }
      }

      }

     Log::info('Finished trip analysis on', ['Truck' => $rows->Truck,  '#' => $truckCode]);
   

    }

    //3
    public function BiTripStart(){

        ini_set('max_execution_time', 3600000000000); // 3600 seconds = 60 minutes
        set_time_limit(360000000000);
       
        $truckData = DB::connection('mysql')->table('baselinetest')->groupBy('Truck')->orderBy('id')->get();
           //  dd($truckData);
  
         foreach ($truckData as $truckCode => $rows) {
  
         $trucks =  DB::connection('mysql')->table('baselinetest')->where('Truck', '=', $rows->Truck)->orderBy('DateUpdated')->orderBy('Time')->get();
  
        foreach ($trucks as $truckrows =>$trip) {
         
          Log::info('started powerBI trip start on', ['Truck' => $rows->Truck,  '#' => $truckrows, 'id' => $trip->id] );
  
            $nextTripId = $trip->id + 1;
           $nextTrip =  DB::connection('mysql')->table('baselinetest')->where('id', '=', $nextTripId)->first();
   
  
           if($trip->TripClassification == 'Left Witbank Yard (Loading Trip)'){
  
            $tripUpdate =  DB::connection('mysql')->table('baselinetest')->where('id', '=', $trip->id )->update([
    
              'TripClassificationv2' => 'Trip Start'
      
             ]);
    
           }
    
        
          if($trip->TripClassification == 'Offloading Time' && $trip->TripClassificationv2  == 'Trip End'){
    
          $tripUpdate =  DB::connection('mysql')->table('baselinetest')->where('id', '=', $nextTrip->id )->update([
    
           'TripClassificationv2' => 'Trip Start'
    
          ]);
    
         }

        }
  
      }
  
      Log::info('Finished powerBI trip start', ['Truck' => $rows->Truck,  '#' => $truckCode]);
  
  
    }

    //4
    public function BiTripStart2(){

      ini_set('max_execution_time', 3600000000000); // 3600 seconds = 60 minutes
      set_time_limit(360000000000);
     
      $truckData = DB::connection('mysql')->table('baselinetest')->groupBy('Truck')->orderBy('id')->get();
       //  dd($truckData);

       foreach ($truckData as $truckCode => $rows) {

       $trucks =  DB::connection('mysql')->table('baselinetest')->where('Truck', '=', $rows->Truck)->orderBy('DateUpdated')->orderBy('Time')->get();

      foreach ($trucks as $truckrows =>$trip) {
       
        Log::info('started powerBI trip start 2 on', ['Truck' => $rows->Truck,  '#' => $truckrows, 'id' => $trip->id] );

          $nextTripId = $trip->id - 1;
         $prevTrip =  DB::connection('mysql')->table('baselinetest')->where('id', '=', $nextTripId)->first();
 
         if($trip->TripClassification == 'Return from RBay Start'){

          $tripUpdate =  DB::connection('mysql')->table('baselinetest')->where('id', '=', $trip->id )->update([
  
            'TripClassificationv2' => 'Trip Start'
    
           ]);


           $tripUpdated =  DB::connection('mysql')->table('baselinetest')->where('id', '=', $prevTrip->id )->update([
  
            'TripClassificationv2' => 'Trip End'
     
           ]);
  
         }
  
      }

     }

     Log::info('Finished powerBI trip start 2', ['Truck' => $rows->Truck, '#' => $truckCode]);


    }

    //5 (any order will do for this function
    public function BiTimeCalculation()
    {
      ini_set('max_execution_time', 360000000); // 3600 seconds = 60 minutes
      set_time_limit(360000000);
 
     $truckData = DB::connection('mysql')->table('baselinetest')->groupBy('Truck')->orderBy('id')->get();

      foreach ($truckData as $truckCode => $rows) {

        Log::info('Started power BI time calculation', ['Truck' => $rows->Truck,  '#' => $truckCode]);

        $count =  DB::connection('mysql')->table('baselinetest')->where('Truck', '=', $rows->Truck)->orderBy('DateUpdated')->orderBy('Time')->count();
        if($count > 0){
       $trucks =  DB::connection('mysql')->table('baselinetest')->where('Truck', '=', $rows->Truck)->orderBy('DateUpdated')->orderBy('Time')->skip(1)->take($count - 1)->get();
       $prevTruck =  DB::connection('mysql')->table('baselinetest')->where('Truck', '=', $rows->Truck)->orderBy('DateUpdated')->orderBy('Time')->first();

      foreach ($trucks as  $trip) {

       $prev = $prevTruck->id;

      $currentTrip = $trip->TripClassification;

      $next = $trip->id + 1;
      $previousFullTrip = DB::connection('mysql')->table('baselinetest')->where('id', '=', $prev)->first();


      //step 1
      if($trip->TripClassification == 'Offloading Time' &&  $previousFullTrip->TripClassification == 'Offloading Time'){
   
       if($previousFullTrip->CumulativeTripClassification == null){
               
        $cumulativeTime = $previousFullTrip->TimeDifferenceMins;

        }else{

        $cumulativeTime = $previousFullTrip->CumulativeTripClassification;

        }
                
          $result = $cumulativeTime + $trip->TimeDifferenceMins;

         $updateCount = DB::connection('mysql')->table('baselinetest')->where('id', '=', $trip->id)->update([

             'CumulativeTripClassification' =>  $result
         ]);


         $prevUpdateCount = DB::connection('mysql')->table('baselinetest')->where('id', '=', $prev)->update([

          'CumulativeTripClassification' =>  NULL

        ]);


       }

          //step 2
        if($trip->TripClassification == 'Loading Time' &&  $previousFullTrip->TripClassification == 'Loading Time'){
    
          if($previousFullTrip->CumulativeTripClassification == null){
                  
           $cumulativeTime = $previousFullTrip->TimeDifferenceMins;
   
           }else{
   
           $cumulativeTime = $previousFullTrip->CumulativeTripClassification;
   
           }
                   
             // Get the result
             $result = $cumulativeTime + $trip->TimeDifferenceMins;
   
            $updateCount = DB::connection('mysql')->table('baselinetest')->where('id', '=', $trip->id)->update([
   
                'CumulativeTripClassification' =>  $result
            ]);
   
   
            $prevUpdateCount = DB::connection('mysql')->table('baselinetest')->where('id', '=', $prev)->update([
   
             'CumulativeTripClassification' =>  NULL
   
           ]);
   
   
          }

          
          //step 2.1
        if($trip->TripClassification == 'at Depot Trip' &&  $previousFullTrip->TripClassification == 'at Depot Trip'){
    
          if($previousFullTrip->CumulativeTripClassification == null){
                  
           $cumulativeTime = $previousFullTrip->TimeDifferenceMins;
   
           }else{
   
           $cumulativeTime = $previousFullTrip->CumulativeTripClassification;
   
           }
                   
             // Get the result
             $result = $cumulativeTime + $trip->TimeDifferenceMins;
   
            $updateCount = DB::connection('mysql')->table('baselinetest')->where('id', '=', $trip->id)->update([
   
                'CumulativeTripClassification' =>  $result
            ]);
   
   
            $prevUpdateCount = DB::connection('mysql')->table('baselinetest')->where('id', '=', $prev)->update([
   
             'CumulativeTripClassification' =>  NULL
   
           ]);
   
   
          }

          
          //step 2.2
        if($trip->TripClassification == 'at Repair' &&  $previousFullTrip->TripClassification == 'at Repair'){
    
          if($previousFullTrip->CumulativeTripClassification == null){
                  
           $cumulativeTime = $previousFullTrip->TimeDifferenceMins;
   
           }else{
   
           $cumulativeTime = $previousFullTrip->CumulativeTripClassification;
   
           }
                   
             // Get the result
             $result = $cumulativeTime + $trip->TimeDifferenceMins;
   
            $updateCount = DB::connection('mysql')->table('baselinetest')->where('id', '=', $trip->id)->update([
   
                'CumulativeTripClassification' =>  $result
            ]);
   
   
            $prevUpdateCount = DB::connection('mysql')->table('baselinetest')->where('id', '=', $prev)->update([
   
             'CumulativeTripClassification' =>  NULL
   
           ]);
   
   
          }

          
          //step 2.3
        if($trip->TripClassification == 'at Stop' &&  $previousFullTrip->TripClassification == 'at Stop'){
    
          if($previousFullTrip->CumulativeTripClassification == null){
                  
           $cumulativeTime = $previousFullTrip->TimeDifferenceMins;
   
           }else{
   
           $cumulativeTime = $previousFullTrip->CumulativeTripClassification;
   
           }
                   
             // Get the result
             $result = $cumulativeTime + $trip->TimeDifferenceMins;
   
            $updateCount = DB::connection('mysql')->table('baselinetest')->where('id', '=', $trip->id)->update([
   
                'CumulativeTripClassification' =>  $result
            ]);
   
   
            $prevUpdateCount = DB::connection('mysql')->table('baselinetest')->where('id', '=', $prev)->update([
   
             'CumulativeTripClassification' =>  NULL
   
           ]);
   
   
          }



           //step 2.4
        if($trip->TripClassification == 'at Theft' &&  $previousFullTrip->TripClassification == 'at Theft'){
    
          if($previousFullTrip->CumulativeTripClassification == null){
                  
           $cumulativeTime = $previousFullTrip->TimeDifferenceMins;
   
           }else{
   
           $cumulativeTime = $previousFullTrip->CumulativeTripClassification;
   
           }
                   
             // Get the result
             $result = $cumulativeTime + $trip->TimeDifferenceMins;
   
            $updateCount = DB::connection('mysql')->table('baselinetest')->where('id', '=', $trip->id)->update([
   
                'CumulativeTripClassification' =>  $result
            ]);
   
   
            $prevUpdateCount = DB::connection('mysql')->table('baselinetest')->where('id', '=', $prev)->update([
   
             'CumulativeTripClassification' =>  NULL
   
           ]);
   
   
          }

        //step 3
       if($trip->TripClassification != 'Offloading Time' &&  $trip->TripClassification != 'Loading Time'){

        $updateCount = DB::connection('mysql')->table('baselinetest')->where('id', '=', $trip->id)->update([

          'CumulativeTripClassification' =>  $trip->TimeDifferenceMins
      ]);

       }

       //step 4 
       $count = DB::connection('mysql')->table('baselinetest')->where('id', '=', $next)->count();
       if($count > 0){
        
       $nexttrip = DB::connection('mysql')->table('baselinetest')->where('id', '=', $next)->first();

       if($trip->TripClassification == 'Offloading Time' &&  $previousFullTrip->TripClassification != 'Offloading Time' && $nexttrip->TripClassification != 'Offloading Time'){

        $updateCount = DB::connection('mysql')->table('baselinetest')->where('id', '=', $trip->id)->update([

          'CumulativeTripClassification' =>  $trip->TimeDifferenceMins
      ]);

       }

       if($trip->TripClassification == 'Loading Time' &&  $previousFullTrip->TripClassification != 'Loading Time' && $nexttrip->TripClassification != 'Loading Time'){

        $updateCount = DB::connection('mysql')->table('baselinetest')->where('id', '=', $trip->id)->update([

          'CumulativeTripClassification' =>  $trip->TimeDifferenceMins
      ]);

       }

      }

       $prevTruck = $trip;
 
      }

     }

     Log::info('Finished CumulativeTime on', ['Truck' => $rows->Truck,  '#' => $truckCode]);
  
     }

    }

    //8
    public function TripTime()
    {
        
        ini_set('max_execution_time', 360000000000); // 3600 seconds = 60 minutes
        set_time_limit(360000000000);

        $truckData = DB::connection('mysql')->table('baselinetest')->groupBy('Truck')->orderBy('id')->get();
   
        foreach ($truckData as $truckCode => $rows) {

        Log::info('Started triptime on', ['Truck' => $rows->Truck,  '#' => $truckCode]);
    
        $trucks = DB::connection('mysql')->table('baselinetest')->where('Truck', '=', $rows->Truck)->where('TripClassificationv2', '=', 'Trip Start')->orderBy('DateUpdated')->orderBy('Time')->get();
     
        foreach ($trucks as  $truckrows => $trip) {
            
        $nextTrip = DB::connection('mysql')->table('baselinetest')->where('id', '>', $trip->id )->where('TripClassificationv2','=', 'Trip End')->first(); 
    
        if($nextTrip != null){
 
        $interval =  DB::connection('mysql')->table('baselinetest')->whereBetween('id', [$trip->id, $nextTrip->id])
        ->sum('PositiveEventDuration');
        //  dd($interval,$trip,$nextTrip); 
        $tripUpdate = DB::connection('mysql')->table('baselinetest')->where('id', '=', $nextTrip->id)->update([

           'TripTime' => $interval

        ]); 

         }


        }

        Log::info('Finished trip Time on', ['Truck' => $rows->Truck,  '#' => $truckCode]);

       } 

 
     
    }

    //6
    public function LoadingTimes()
    {
        
        ini_set('max_execution_time', 360000000000); // 3600 seconds = 60 minutes
        set_time_limit(360000000000);

        $truckData = DB::connection('mysql')->table('baselinetest')->groupBy('Truck')->orderBy('id')->get();
   
        foreach ($truckData as $truckCode => $rows) {

        Log::info('Started loading Times on', ['Truck' => $rows->Truck,  '#' => $truckCode]);
    
        $trucks = DB::connection('mysql')->table('baselinetest')->where('Truck', '=', $rows->Truck)->where('TripClassificationv2', '=', 'Trip Start')->orderBy('DateUpdated')->orderBy('Time')->get();
     
        foreach ($trucks as  $truckrows => $trip) {
            
        $nextTrip = DB::connection('mysql')->table('baselinetest')->where('id', '>', $trip->id )->where('TripClassificationv2','=', 'Trip End')->first(); 
    
        if($nextTrip != null){

        //loading times on trip start
        $interval =  DB::connection('mysql')->table('baselinetest')->whereBetween('id', [$trip->id, $nextTrip->id])
        ->where('TripClassification', 'Loading Time')
        ->first();

        if($interval != null){

          $loading = $interval->GFupdated1;

        }else{

          $loading = 'none';
        }

        $tripUpdate = DB::connection('mysql')->table('baselinetest')->where('id', '=', $trip->id)->update([

           'LoadingPoint' => $loading

        ]); 



         //offloading times on trip ended
         $offload =  DB::connection('mysql')->table('baselinetest')->whereBetween('id', [$trip->id, $nextTrip->id])
         ->where('TripClassification', 'Offloading Time')
         ->first();
 
         if($offload != null){
 
           $loading = $offload->GFupdated1;
 
         }else{
 
           $loading = 'none';
           
         }
 
         $tripUpdate = DB::connection('mysql')->table('baselinetest')->where('id', '=', $nextTrip->id)->update([
 
            'OffloadingPoint' => $loading
 
         ]); 

         }

        }

        Log::info('Finished loading Times on', ['Truck' => $rows->Truck,  '#' => $truckCode]);

       } 



     
    }

    //9
    public function LoadingTimesv2(){

      ini_set('max_execution_time', 360000000000); // 3600 seconds = 60 minutes
      set_time_limit(360000000000);

       $truckData = DB::connection('mysql')->table('baselinetest')->groupBy('Truck')->orderBy('id')->get();
 
       foreach ($truckData as $truckCode => $rows) {

       Log::info('Started loading Times v2 on', ['Truck' => $rows->Truck,  '#' => $truckCode]);
  
       $trucks = DB::connection('mysql')->table('baselinetest')->where('Truck', '=', $rows->Truck)->where('TripClassificationv2', '=', 'Trip Start')->orderBy('DateUpdated')->orderBy('Time')->get();
   
       foreach ($trucks as  $truckrows => $trip) {
          
       $nextTrip = DB::connection('mysql')->table('baselinetest')->where('id', '>', $trip->id )->where('TripClassificationv2','=', 'Trip End')->first(); 
  
       if($nextTrip != null){

      //loading times on trip start
       $interval =  DB::connection('mysql')->table('baselinetest')->whereBetween('id', [$trip->id, $nextTrip->id])
       ->where('TripClassification', 'Loading Time')
       ->first();

       $interval2 =  DB::connection('mysql')->table('baselinetest')->whereBetween('id', [$trip->id, $nextTrip->id])
       ->where('TripClassification', 'Loading Trip')
       ->first();

       if($interval == null && $interval2 != null){
 
       $tripUpdate = DB::connection('mysql')->table('baselinetest')->where('id', '=', $trip->id)->update([

         'LoadingPoint' => $interval2->GFupdated1

       ]); 

       }


       //offloading times on trip ended
       $offload =  DB::connection('mysql')->table('baselinetest')->whereBetween('id', [$trip->id, $nextTrip->id])
       ->where('TripClassification', 'Offloading Time')
       ->first();


       $offload2 =  DB::connection('mysql')->table('baselinetest')->whereBetween('id', [$trip->id, $nextTrip->id])
       ->where('TripClassification', 'Offloading Trip')
       ->first();

       if($offload == null && $offload2 != null){

        $tripUpdate = DB::connection('mysql')->table('baselinetest')->where('id', '=', $nextTrip->id)->update([

          'OffloadingPoint' => $offload2->GFupdated1

       ]); 
         
       }

       
       $todepot =  DB::connection('mysql')->table('baselinetest')->whereBetween('id', [$trip->id, $nextTrip->id])
       ->where('TripClassification', 'to Depot Trip')
       ->first();

       //all aspects
       if($offload == null && $offload2 == null && $interval == null && $interval2 == null &&   $todepot != null){

        $tripUpdate = DB::connection('mysql')->table('baselinetest')->where('id', '=', $nextTrip->id)->update([

        'TripF1' => 'Depot trip only'
      ]);

       }
  

       }

      }

      Log::info('Finished loading Times v2 on', ['Truck' => $rows->Truck,  '#' => $truckCode]);

     } 

   
    }

    //10
    public function TripF1()
    {
        
        ini_set('max_execution_time', 360000000000); // 3600 seconds = 60 minutes
        set_time_limit(360000000000);

        $truckData = DB::connection('mysql')->table('baselinetest')->groupBy('Truck')->orderBy('id')->get();
   
        foreach ($truckData as $truckCode => $rows) {

        Log::info('Started Trip F1 on', ['Truck' => $rows->Truck,  '#' => $truckCode]);
    
        $trucks = DB::connection('mysql')->table('baselinetest')->where('Truck', '=', $rows->Truck)->where('TripClassificationv2', '=', 'Trip Start')->orderBy('DateUpdated')->orderBy('Time')->get();
     
        foreach ($trucks as  $truckrows => $trip) {
            
        $nextTrip = DB::connection('mysql')->table('baselinetest')->where('id', '>', $trip->id )->where('TripClassificationv2','=', 'Trip End')->first(); 
    
        if($nextTrip != null){

        //loading times on trip start
         if($trip->LoadingPoint != 'none' && $nextTrip->OffloadingPoint != 'none'){

          $tripUpdate = DB::connection('mysql')->table('baselinetest')->where('id', '=', $nextTrip->id)->update([

            'TripF1' => 'Full Trip: loading at ' . $trip->LoadingPoint. ' and offloading  at ' . $nextTrip->OffloadingPoint
 
         ]); 

         }elseif($trip->LoadingPoint != 'none' && $nextTrip->OffloadingPoint == 'none'){

          $tripUpdate = DB::connection('mysql')->table('baselinetest')->where('id', '=', $nextTrip->id)->update([

            'TripF1' => 'Loading Trip  at '. $trip->LoadingPoint
 
         ]); 

        }elseif($trip->LoadingPoint == 'none' && $nextTrip->OffloadingPoint != 'none'){

          $tripUpdate = DB::connection('mysql')->table('baselinetest')->where('id', '=', $nextTrip->id)->update([

            'TripF1' => 'Offloading Trip  at ' . $nextTrip->OffloadingPoint
 
         ]); 

         }else{

          $tripUpdate = DB::connection('mysql')->table('baselinetest')->where('id', '=', $nextTrip->id)->update([

            'TripF1' => 'No Trip' 
 
         ]);

          }

         }

        }

        Log::info('Finished Trip F1 on', ['Truck' => $rows->Truck,  '#' => $truckCode]);

       } 


     
    }

    //7
    public function RbayTrips(){

      ini_set('max_execution_time', 360000000000); // 3600 seconds = 60 minutes
      set_time_limit(360000000000);

      $truckData = DB::connection('mysql')->table('baselinetest')->groupBy('Truck')->orderBy('id')->get();
      //  dd($truckData);
      foreach ($truckData as $truckCode => $rows) {

      Log::info('Started RBay Trip on', ['Truck' => $rows->Truck,  '#' => $truckCode]);
  
      $trucks = DB::connection('mysql')->table('baselinetest')->where('Truck', '=', $rows->Truck)->orderBy('DateUpdated')->orderBy('Time')->get();
        //  dd($trucks);
      foreach ($trucks as  $truckrows => $trip) {

        $prev = DB::connection('mysql')->table('baselinetest')->where('id', '=', $trip->id - 1)->first();
        $prevv =  DB::connection('mysql')->table('baselinetest')->where('id', '=', $trip->id - 3)->first();
        $next = DB::connection('mysql')->table('baselinetest')->where('id', '=', $trip->id + 1)->first();
        $nexttt = DB::connection('mysql')->table('baselinetest')->where('id', '=', $trip->id + 2)->first();

        //step 1
        if($trip->GFnew == 'Richards Bay Route to RICHARDSBAY TRUCK STOP' && $prev->GFnew != 'Richards Bay to Richards Bay' && $next->GFnew != 'Richards Bay to Richards Bay' && $prevv->GFnew != 'Richards Bay to Richards Bay' && $nexttt->GFnew != 'Richards Bay to Richards Bay'){

          $tripUpdate = DB::connection('mysql')->table('baselinetest')->where('id', '=', $trip->id)->update([

            'TripClassificationv2' => 'Trip End',
            'OffloadingPoint' => 'Richards Bay'
 
         ]); 

           $tripUpdate = DB::connection('mysql')->table('baselinetest')->where('id', '=', $next->id)->update([

            'TripClassificationv2' => 'Trip Start',

          ]); 

        }

        // step 2
        if($trip->GFnew == 'Richards Bay Route to Pongola Truck Stop'){

          $tripUpdate = DB::connection('mysql')->table('baselinetest')->where('id', '=', $trip->id)->update([

            'TripClassificationv2' => 'Trip Start',
 
         ]); 

           $tripUpdate = DB::connection('mysql')->table('baselinetest')->where('id', '=', $prev->id)->update([

            'TripClassificationv2' => 'Trip End',
            'OffloadingPoint' => 'Richards Bay'

          ]); 

        }


          //step 3
        if($trip->TripClassification == 'RBay Trip End'){

          $tripUpdate = DB::connection('mysql')->table('baselinetest')->where('id', '=', $trip->id)->update([

            'TripClassificationv2' => 'Trip End',
            'OffloadingPoint' => 'Richards Bay'
 
          ]); 

           $tripUpdate = DB::connection('mysql')->table('baselinetest')->where('id', '=', $next->id)->update([

            'TripClassificationv2' => 'Trip Start'

          ]); 

        }

        //step 4
        if($trip->GFnew == 'Richards Bay Route to UITKOMST MINE'){

          $tripUpdate = DB::connection('mysql')->table('baselinetest')->where('id', '=', $trip->id)->update([

            'TripClassificationv2' => 'Trip End',
            'OffloadingPoint' => 'Richards Bay'
 
          ]); 


           if($next->GFnew == 'UITKOMST MINE to UITKOMST MINE'){
         
           $nextt = $next->id + 1;

           $tripUpdate = DB::connection('mysql')->table('baselinetest')->where('id', '=', $nextt)->update([

            'TripClassificationv2' => 'Trip Start'

          ]);

         }else{

          $tripUpdate = DB::connection('mysql')->table('baselinetest')->where('id', '=', $next->id)->update([

            'TripClassificationv2' => 'Trip Start'

          ]);

         }

        }


      }

      Log::info('Finished RBay Trip on', ['Truck' => $rows->Truck,  '#' => $truckCode]);

     } 

    

    }

     //11
    public function ShiftClass()
    {

      ini_set('max_execution_time', 360000000000); // 3600 seconds = 60 minutes
      set_time_limit(360000000000);

      $truckData = DB::connection('mysql')->table('baselinetest')->groupBy('Truck')->orderBy('id')->get();
 
      foreach ($truckData as $truckCode => $rows) {

      Log::info('Started shift class on', ['Truck' => $rows->Truck,  '#' => $truckCode]);
  
      $trucks = DB::connection('mysql')->table('baselinetest')->where('Truck', '=', $rows->Truck)->orderBy('DateUpdated')->orderBy('Time')->get();
   
      foreach ($trucks as  $truckrows => $trip)
      {
          if($trip->Time > '06:00:00' && $trip->Time < '18:00:00'){
           
            $tripUpdate = DB::connection('mysql')->table('baselinetest')->where('id', '=', $trip->id)->update([

              'ShiftClassification' => 'Day Shift',
   
           ]); 

          }else{

            $tripUpdate = DB::connection('mysql')->table('baselinetest')->where('id', '=', $trip->id)->update([

              'ShiftClassification' => 'Night Shift',
   
           ]); 

          }

  
      }

      Log::info('Finished shift class on', ['Truck' => $rows->Truck,  '#' => $truckCode]);

     } 

    }


    public function TonnesMoved()
    {

       ini_set('max_execution_time', 360000000000); // 3600 seconds = 60 minutes
       set_time_limit(360000000000);

       $truckData = DB::connection('mysql')->table('baselinetest')->groupBy('Truck')->orderBy('id')->get();
 
       foreach ($truckData as $truckCode => $rows) {

        Log::info('Started tonnes Moved on', ['Truck' => $rows->Truck,  '#' => $truckCode]);

        
        $string = $rows->Truck;
        $substring = 'Workshop';
        $substring1 = 'Parked';

      if (strpos($string, $substring) !== false) {

       $results = str_replace($substring, '', $string);
     //'The string contains the word "Workshop"'
      } elseif(strpos($string, $substring1) !== false) {

       $results = str_replace($substring1, '', $string);

      }else{

       $results = $rows->Truck;
      }

  
       $trucks = DB::connection('mysql')->table('baselinetest')->where('Truck', '=', $rows->Truck)->orderBy('DateUpdated')->orderBy('Time')->get();
   
       foreach ($trucks as  $truckrows => $trip)
        {
         $getTruckDetails = DB::connection('mysql')->table('truckmap')->where('Registration', '=', $trip->Truck)->orwhere('Truck', '=', $trip->Truck)->first();

         // update all lines and update the truck type (PBS or Interlink)
         $updateTrip = DB::connection('mysql')->table('baselinetest')->where('id', '=', $trip->id)->update([

          'TruckType' => $getTruckDetails->TruckType

         ]);

         // only update the row if its Trip End
         if($trip->TripClassificationv2 == 'Trip End'){
    
          $updateTripLoad = DB::connection('mysql')->table('baselinetest')->where('id', '=', $trip->id)->update([

            'TonnesMoved' => $getTruckDetails->loadCapacity
  
           ]);

         }
       }

       Log::info('Finished tonnes Moved on', ['Truck' => $rows->Truck,  '#' => $truckCode]);

      } 

     dd('done');
    }
 
    //12
    public function TripRoute()
    {
        
        ini_set('max_execution_time', 360000000000); // 3600 seconds = 60 minutes
        set_time_limit(360000000000);

        $truckData = DB::connection('mysql')->table('baselinetest')->groupBy('Truck')->orderBy('id')->get();
   
        foreach ($truckData as $truckCode => $rows) {

        Log::info('Started Trip Route on', ['Truck' => $rows->Truck,  '#' => $truckCode]);
    
        $trucks = DB::connection('mysql')->table('baselinetest')->where('Truck', '=', $rows->Truck)->where('TripClassificationv2', '=', 'Trip Start')->orderBy('DateUpdated')->orderBy('Time')->get();
     
        foreach ($trucks as  $truckrows => $trip) {
            
        $nextTrip = DB::connection('mysql')->table('baselinetest')->where('id', '>', $trip->id )->where('TripClassificationv2','=', 'Trip End')->first(); 
    
        if($nextTrip != null){

        //loading times on trip start
         if($trip->LoadingPoint != 'none' && $nextTrip->OffloadingPoint != 'none'){

          $tripUpdate = DB::connection('mysql')->table('baselinetest')->where('id', '=', $nextTrip->id)->update([

            'TripRoute' => $trip->LoadingPoint. ' to ' . $nextTrip->OffloadingPoint
 
         ]); 

         }elseif($trip->LoadingPoint != 'none' && $nextTrip->OffloadingPoint == 'none'){

          $tripUpdate = DB::connection('mysql')->table('baselinetest')->where('id', '=', $nextTrip->id)->update([

            'TripRoute' =>  $trip->LoadingPoint .' to '
 
         ]); 

        }elseif($trip->LoadingPoint == 'none' && $nextTrip->OffloadingPoint != 'none'){

          $tripUpdate = DB::connection('mysql')->table('baselinetest')->where('id', '=', $nextTrip->id)->update([

            'TripRoute' => 'to ' . $nextTrip->OffloadingPoint
 
         ]); 

         }else{

          }

         }

        }

        Log::info('Finished Trip Route on', ['Truck' => $rows->Truck,  '#' => $truckCode]);

       } 

       dd('done');

     
    }


    public function truckmap()
    {    
      
      ini_set('max_execution_time', 3600000000000); // 3600 seconds = 60 minutes
      set_time_limit(360000000000);
       
        $trucks =  DB::connection('mysql')->table('truckmap')->where('id', '>', 0 )->get();
     //  dd($trucks);
        foreach ($trucks as $truckrows => $trip) {

        $count = DB::connection('mysql')->table('fleetlist')->where('fleetNumber', '=', $trip->Fleet)->count();
     //   dd($count,$trip);
        if($count > 0){

        $truck = DB::connection('mysql')->table('fleetlist')->where('fleetNumber', '=', $trip->Fleet)->first();
          // dd($truck);
        $updatefleet = DB::connection('mysql')->table('truckmap')->where('id', '=', $trip->id )->update([
         
          'capacity' => $truck->capacity,
          'type' => $truck->type

        ]);

      }

      }
      
      dd('done');
             
    }

    public function loadCapacity()
    {    
      
      ini_set('max_execution_time', 360000000000); // 3600 seconds = 60 minutes
        set_time_limit(360000000000);

        $truckData = DB::connection('mysql')->table('baselinetest')->groupBy('Truck')->orderBy('id')->get();
   
        foreach ($truckData as $truckCode => $rows) {

        Log::info('Started Load Capacity on', ['Truck' => $rows->Truck,  '#' => $truckCode]);
    
        $trucks = DB::connection('mysql')->table('baselinetest')->where('TripRoute', '!=', null )->where('Truck', '=', $rows->Truck)->get();
     
        foreach ($trucks as  $truckrows => $trip) {

         $string = $trip->Truck;
         $substring = 'Workshop';
         $substring1 = 'Parked';

       if (strpos($string, $substring) !== false) {

        $result = str_replace($substring, '', $string);
      //'The string contains the word "Workshop"'
       } elseif(strpos($string, $substring1) !== false) {

        $result = str_replace($substring1, '', $string);

       }else{

        $result = $trip->Truck;
       }

       $string = $result;
       $parts = explode(" ", $string);
       $results = $parts[0];

       $count = DB::connection('mysql')->table('fleetlist')->where('fleetNumber', '=',  $results)->count();

        if($count > 0){

        $truckData =  DB::connection('mysql')->table('fleetlist')->where('fleetNumber', '=',  $results)->first();
        // dd($truckData);
        $numericString = preg_replace("/[^0-9]/", "", $truckData->capacity);
        $capacity = (int)$numericString;

        $updatefleet = DB::connection('mysql')->table('baselinetest')->where('id', '=', $trip->id )->update([
         
          'Trucktype' => $truckData->type,
          'TonnesMoved' => $capacity * 0.944

        ]);

         }

        }

        Log::info('Finished Load Capacity on', ['Truck' => $rows->Truck,  '#' => $truckCode]);

       } 

       dd('done');

             
    }


    public function fleetboardfuel()
    {    
      
        ini_set('max_execution_time', 360000000000); // 3600 seconds = 60 minutes
        set_time_limit(360000000000);

        $truckData = DB::connection('mysql')->table('baselinetest')->groupBy('Truck')->orderBy('id')->get();
   
        foreach ($truckData as $truckCode => $rows) {

        Log::info('Started fleetboard Capacity on', ['Truck' => $rows->Truck,  '#' => $truckCode]);
    
        $trucks = DB::connection('mysql')->table('baselinetest')->where('Truck', '=',  $rows->Truck)->get();
     
        foreach ($trucks as  $truckrows => $trip) {

          $string = $trip->Truck;
          $substring = 'Workshop';
          $substring1 = 'Parked';
 
        if (strpos($string, $substring) !== false) {
 
         $results = str_replace($substring, '', $string);
       //'The string contains the word "Workshop"'
        } elseif(strpos($string, $substring1) !== false) {
 
         $results = str_replace($substring1, '', $string);
 
        }else{
 
         $results = $trip->Truck;
        }

        $string = $results;
        $parts = explode(" ", $string);
        $result = $parts[0];
        //dd($result);

        $count = DB::connection('mysql')->table('fleetlist')->where('fleetNumber', '=',  $result)->count();
         
        if($count > 0){
          
          $truckmap = DB::connection('mysql')->table('fleetlist')->where('fleetNumber', '=',  $result)->first();

          //  dd($count,$result,$truckmap);
          $dateString = $trip->DateUpdated;
          $month = date("m", strtotime($dateString));
           // dd($month);
          if($month == "10"){

            $oct = DB::connection('mysql')->table('octconsumption')->where('Fleet', '=', $truckmap->fleetNumber )->count();

            if($oct > 0){

              $octData = DB::connection('mysql')->table('octconsumption')->where('Fleet', '=', $truckmap->fleetNumber )->first();

              $tripUpdate = DB::connection('mysql')->table('baselinetest')->where('id', '=', $trip->id)->update([

                'fuelConsumption' => $octData->AveConsumptionPerKm
     
             ]); 

            }else{

              $quartCount = DB::connection('mysql')->table('quarterlyconsumption')->where('Fleet', '=', $truckmap->fleetNumber )->count();

              if($quartCount > 0){

                $octData = DB::connection('mysql')->table('quarterlyconsumption')->where('Fleet', '=', $truckmap->fleetNumber )->first();

                $tripUpdate = DB::connection('mysql')->table('baselinetest')->where('id', '=', $trip->id)->update([

                  'fuelConsumption' => $octData->AveConsumptionPerKm
       
               ]); 

              }

            }

          }elseif($month == "11"){

            
            $nov = DB::connection('mysql')->table('novconsumption')->where('Fleet', '=', $truckmap->fleetNumber )->count();

            if($nov > 0){

              $octData = DB::connection('mysql')->table('novconsumption')->where('Fleet', '=', $truckmap->fleetNumber )->first();

              $tripUpdate = DB::connection('mysql')->table('baselinetest')->where('id', '=', $trip->id)->update([

                'fuelConsumption' => $octData->AveConsumptionPerKm
     
             ]); 

            }else{

               $quartCount = DB::connection('mysql')->table('quarterlyconsumption')->where('Fleet', '=', $truckmap->fleetNumber )->count();

              if($quartCount > 0){

                $octData = DB::connection('mysql')->table('quarterlyconsumption')->where('Fleet', '=', $truckmap->fleetNumber )->first();
                
                $tripUpdate = DB::connection('mysql')->table('baselinetest')->where('id', '=', $trip->id)->update([

                  'fuelConsumption' => $octData->AveConsumptionPerKm
       
               ]); 

              }

            }

          }else{

            $dec = DB::connection('mysql')->table('decconsumption')->where('Fleet', '=', $truckmap->fleetNumber )->count();

            if($dec > 0){

              $octData = DB::connection('mysql')->table('decconsumption')->where('Fleet', '=', $truckmap->fleetNumber )->first();

              $tripUpdate = DB::connection('mysql')->table('baselinetest')->where('id', '=', $trip->id)->update([

                'fuelConsumption' => $octData->AveConsumptionPerKm
     
             ]); 

            }else{

              $quartCount = DB::connection('mysql')->table('quarterlyconsumption')->where('Fleet', '=', $truckmap->fleetNumber )->count();

              if($quartCount > 0){

                $octData = DB::connection('mysql')->table('quarterlyconsumption')->where('Fleet', '=', $truckmap->fleetNumber )->first();
                
                $tripUpdate = DB::connection('mysql')->table('baselinetest')->where('id', '=', $trip->id)->update([

                  'fuelConsumption' => $octData->AveConsumptionPerKm
       
               ]); 

              }
            }
          }

      

        }

        }

        Log::info('Finished fleetboard Capacity on', ['Truck' => $rows->Truck,  '#' => $truckCode]);

       } 

       dd('done');

             
    }



}
