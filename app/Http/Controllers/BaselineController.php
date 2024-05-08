<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use DateTime;
use GuzzleHttp\Client;
use DateInterval;
use App\Imports\BaselineImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Log;

class BaselineController extends Controller
{




 
  public function RunBaseline()
  {

     // first phase baseline

      // $this->timeDifference();
      // $this->LongDifference();
      // $this->LatDifference();
     // $this->CoordinateTest();
     // $this->movingStationary();
      $this->Count();
      $this->OnTheRoad();
      $this->TripStart();
     $this->tripEnd();
     $this->TripTest();
     $this->TripTestUpdated();
     $this->cycleTime();
     $this->geofence();

    //second phase baseline
    
      //  $this->BiTripEnd();
      //  $this->BiTripStart();
      //  $this->BiTripStart2();
      //  $this->BiTimeCalculation();
      //  $this->LoadingTimes();
      //  $this->RbayTrips();
      //  $this->TripTime();
      //  $this->LoadingTimesv2();
      // $this->TripF1();
      // $this->ShiftClass();
      // $this->TripRoute();
   

  }


///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

//First Baseline

      public function LongDifference()
    {

        ini_set('max_execution_time', 3600000000); // 3600 seconds = 60 minutes
        set_time_limit(3600000000);
           
        $truckData = DB::connection('mysql')->table('baselinev2')->whereBetween('Date', ['2024-01-01' , '2024-01-31'])->groupBy('Truck')->orderBy('id')->get();
        // $truckData = $truckData->take(2);
        //   dd($truckData);

         foreach ($truckData as $truckCode => $rows) {

          Log::info('Started Longitude Difference on', ['Truck' => $rows->Truck, '#' => $truckCode]);
          $startDate = '2024-01-01'; // Replace with your start date
          $endDate = '2024-01-31';   // Replace with your end date

          // Convert to DateTime objects
          $startDateTime = new DateTime($startDate);
          $endDateTime = new DateTime($endDate);
     
         $trucks =  DB::connection('mysql')->table('baselinev2')->where('Truck', '=', $rows->Truck)->whereBetween('Date', [$startDateTime, $endDateTime])->orderBy('Date')->orderBy('Time')->get();
        // dd($trucks);
        foreach ($trucks as  $truckrows => $trip) {
        
         $currentTrip = DB::connection('mysql')->table('baselinev2')->where('id', '=', $trip->id)->first(); 

         if($truckrows  > 0){

          $nextIndex = $truckrows - 1;
        }else{
          $nextIndex = 0;
        }
                    
         $previousTrip = DB::connection('mysql')->table('baselinev2')->where('id', '=',  $trucks[$nextIndex]->id)->first();          

         $interval =  $currentTrip->Longitude - $previousTrip->Longitude;        
        //  dd(number_format($interval,));
         $tripUpdate = DB::connection('mysql')->table('baselinev2')->where('id', '=', $trip->id)->update([

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

        $truckData = DB::connection('mysql')->table('baselinev2')->whereBetween('Date', ['2024-01-01' , '2024-01-31'])->groupBy('Truck')->orderBy('id')->get();
        // $truckData = $truckData->take(2);
        //   dd($truckData);

         foreach ($truckData as $truckCode => $rows) {

          Log::info('Started Latitude Difference on', ['Truck' => $rows->Truck, '#' => $truckCode]);
          $startDate = '2024-01-01'; // Replace with your start date
          $endDate = '2024-01-31';   // Replace with your end date

          // Convert to DateTime objects
          $startDateTime = new DateTime($startDate);
          $endDateTime = new DateTime($endDate);
     
         $trucks =  DB::connection('mysql')->table('baselinev2')->where('Truck', '=', $rows->Truck)->whereBetween('Date', [$startDateTime, $endDateTime])->orderBy('Date')->orderBy('Time')->get();
        // dd($trucks);
        foreach ($trucks as $truckrows => $trip) {

         $currentTrip = DB::connection('mysql')->table('baselinev2')->where('id', '=', $trip->id)->first(); 

         if($truckrows  > 0){

          $nextIndex = $truckrows - 1;
          
        }else{

          $nextIndex = 0;
        }

        $previousTrip = DB::connection('mysql')->table('baselinev2')->where('id', '=',  $trucks[$nextIndex]->id)->first();   

         $interval =  $currentTrip->Latitude - $previousTrip->Latitude;        
       //  dd($interval);
         $tripUpdate = DB::connection('mysql')->table('baselinev2')->where('id', '=', $trip->id)->update([

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

      
        $truckData = DB::connection('mysql')->table('baselinev2')->whereBetween('Date', ['2024-01-01' , '2024-01-31'])->groupBy('Truck')->orderBy('id')->get();
        // $truckData = $truckData->take(2);
        //   dd($truckData);

         foreach ($truckData as $truckCode => $rows) {

          Log::info('Started coordinate test on', ['Truck' => $rows->Truck, '#' => $truckCode]);
          $startDate = '2024-01-01'; // Replace with your start date
          $endDate = '2024-01-31';   // Replace with your end date
         if($truckCode > 136 ){
          // Convert to DateTime objects
          $startDateTime = new DateTime($startDate);
          $endDateTime = new DateTime($endDate);
     
         $trucks =  DB::connection('mysql')->table('baselinev2')->whereBetween('Date', [$startDateTime, $endDateTime])->where('Truck', '=', $rows->Truck)->orderBy('Date')->orderBy('Time')->get();
       //  dd($trucks);

        foreach ($trucks as $trip) {
        
         $currentTrip = DB::connection('mysql')->table('baselinev2')->where('id', '=', $trip->id)->first(); 
            
          if($currentTrip->LongitudeDifference < 0.0001 || $currentTrip->LatitudeDifference < 0.0001 ){

            $test = 1;

          }else{

            $test = 0;
          }
       
         $tripUpdate = DB::connection('mysql')->table('baselinev2')->where('id', '=', $trip->id)->update([

            'CoordinateTest' => $test
         ]); 
   

        }

      }

        Log::info('Finished Coordinate Test on', ['Truck' => $rows->Truck,  '#' => $truckCode]);

       }
   
    }
 
     public function Count(){

        ini_set('max_execution_time', 360000000); // 3600 seconds = 60 minutes
        set_time_limit(360000000);

        $truckData = DB::connection('mysql')->table('baselinev2')->whereBetween('Date', ['2024-01-01' , '2024-01-31'])->groupBy('Truck')->orderBy('id')->get();
        // $truckData = $truckData->take(2);
        //   dd($truckData);

         foreach ($truckData as $truckCode => $rows) {

          Log::info('Started count on', ['Truck' => $rows->Truck, '#' => $truckCode]);
          $startDate = '2024-01-01'; // Replace with your start date
          $endDate = '2024-01-31';   // Replace with your end date
          if($truckCode > 136 ){
          // Convert to DateTime objects
          $startDateTime = new DateTime($startDate);
          $endDateTime = new DateTime($endDate);
     
          $count =  DB::connection('mysql')->table('baselinev2')->whereBetween('Date', [$startDateTime, $endDateTime])->where('Truck', '=', $rows->Truck)->orderBy('Date')->orderBy('Time')->count();
          if($count > 0){
         $trucks =  DB::connection('mysql')->table('baselinev2')->whereBetween('Date', [$startDateTime, $endDateTime])->where('Truck', '=', $rows->Truck)->orderBy('Date')->orderBy('Time')->skip(1)->take($count - 1)->get();
       //  $trucks =  DB::connection('mysql')->table('baseline')->where('Truck', '=', $rows->Truck)->where('id', '!=', $rows->id)->orderBy('Date')->orderBy('Time')->get();
         $prevTruck =  DB::connection('mysql')->table('baselinev2')->whereBetween('Date', [$startDateTime, $endDateTime])->where('Truck', '=', $rows->Truck)->orderBy('Date')->orderBy('Time')->first();
             //dd($trucks);
        foreach ($trucks as  $trip) {

           $prev = $prevTruck->id;
          // dd($trip);
        //$columnName = "Stationary/Moving";
        $currentTrip = $trip->StationaryMoving;
      //  dd($currentTrip);
        $previousFullTrip = DB::connection('mysql')->table('baselinev2')->where('id', '=', $prev)->first();
      //  dd($previousFullTrip->);
        if($trip->StationaryMoving == $previousFullTrip->StationaryMoving){
       //  dd($trip->StationaryMoving,$previousFullTrip->StationaryMoving);
           $currentCount =  $previousFullTrip->Count + 1;
           $updateCount = DB::connection('mysql')->table('baselinev2')->where('id', '=', $trip->id)->update([

               'Count' => $currentCount
           ]);
        }else{

           $updateCount = DB::connection('mysql')->table('baselinev2')->where('id', '=', $trip->id)->update([

               'Count' => 1
           ]);
         }

         $prevTruck = $trip;
   
       }

      }

    }

       Log::info('Finished Count on', ['Truck' => $rows->Truck,  '#' => $truckCode]);
    
      }

  
     }


   public function CumulativeTime(){

      ini_set('max_execution_time', 360000000); // 3600 seconds = 60 minutes
      set_time_limit(360000000);
 
      $truckData = DB::connection('mysql')->table('baselinev2')->whereBetween('Date', ['2024-01-01' , '2024-01-31'])->groupBy('Truck')->orderBy('id')->get();
      // $truckData = $truckData->take(2);
      //   dd($truckData);

       foreach ($truckData as $truckCode => $rows) {

        Log::info('Started cummulative count on', ['Truck' => $rows->Truck, '#' => $truckCode]);
        $startDate = '2024-01-01'; // Replace with your start date
        $endDate = '2024-01-31';   // Replace with your end date

        // Convert to DateTime objects
        $startDateTime = new DateTime($startDate);
        $endDateTime = new DateTime($endDate);
   
        $count =  DB::connection('mysql')->table('baselinev2')->whereBetween('Date', [$startDateTime, $endDateTime])->where('Truck', '=', $rows->Truck)->orderBy('Date')->orderBy('Time')->count();
        if($count > 0){
       $trucks =  DB::connection('mysql')->table('baselinev2')->whereBetween('Date', [$startDateTime, $endDateTime])->where('Truck', '=', $rows->Truck)->orderBy('Date')->orderBy('Time')->skip(1)->take($count - 1)->get();
     //  $trucks =  DB::connection('mysql')->table('baseline')->where('Truck', '=', $rows->Truck)->where('id', '!=', $rows->id)->orderBy('Date')->orderBy('Time')->get();
       $prevTruck =  DB::connection('mysql')->table('baselinev2')->whereBetween('Date', [$startDateTime, $endDateTime])->where('Truck', '=', $rows->Truck)->orderBy('Date')->orderBy('Time')->first();
        //  dd($trucks);

      foreach ($trucks as  $trip) {

         $prev = $prevTruck->id;

      $currentTrip = $trip->StationaryMoving;

      $previousFullTrip = DB::connection('mysql')->table('baselinev2')->where('id', '=', $prev)->first();
 
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
         $updateCount = DB::connection('mysql')->table('baselinev2')->where('id', '=', $trip->id)->update([

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
    
            $truckData = DB::connection('mysql')->table('baselinev2')->whereBetween('Date', ['2024-01-01' , '2024-01-31'])->groupBy('Truck')->orderBy('id')->get();
            // $truckData = $truckData->take(2);
            //   dd($truckData);
    
             foreach ($truckData as $truckCode => $rows) {
    
              Log::info('Started on the road on', ['Truck' => $rows->Truck, '#' => $truckCode]);
              $startDate = '2024-01-01'; // Replace with your start date
              $endDate = '2024-01-31';   // Replace with your end date
              if($truckCode > 47 ){
          // Convert to DateTime objects
          $startDateTime = new DateTime($startDate);
          $endDateTime = new DateTime($endDate);
     
         $trucks =  DB::connection('mysql')->table('baselinev2')->whereBetween('Date', [$startDateTime, $endDateTime])->where('Truck', '=', $rows->Truck)->orderBy('Date')->orderBy('Time')->get();
      //  dd($trucks);
            foreach ($trucks as $trip) {

            if($trip->Count > 17 AND $trip->StationaryMoving == 'Moving'){
              
            $updateCount = DB::connection('mysql')->table('baselinev2')->where('id', '=', $trip->id)->update([

                'OnTheRoad' => 'on the road'
            ]);

            }
            else{

                $updateCount = DB::connection('mysql')->table('baselinev2')->where('id', '=', $trip->id)->update([

                    'OnTheRoad' => 'False'
                ]);
             }
          }

        }

          Log::info('Finished ontheroad on', ['Truck' => $rows->Truck,  '#' => $truckCode]);

       }

   
     }

     public function TripStart(){
      
      ini_set('max_execution_time', 3600000000); // 3600 seconds = 60 minutes
      set_time_limit(3600000000);

     
      $truckData = DB::connection('mysql')->table('baselinev2')->whereBetween('Date', ['2024-01-01' , '2024-01-31'])->groupBy('Truck')->orderBy('id')->get();
      // $truckData = $truckData->take(2);
      //   dd($truckData);

       foreach ($truckData as $truckCode => $rows) {

        Log::info('Started trip start on', ['Truck' => $rows->Truck, '#' => $truckCode]);
        $startDate = '2024-01-01'; // Replace with your start date
        $endDate = '2024-01-31';   // Replace with your end date

        // Convert to DateTime objects
        $startDateTime = new DateTime($startDate);
        $endDateTime = new DateTime($endDate);
   
        $count =  DB::connection('mysql')->table('baselinev2')->whereBetween('Date', [$startDateTime, $endDateTime])->where('Truck', '=', $rows->Truck)->orderBy('Date')->orderBy('Time')->count();

        if($count > 0){
       $trucks =  DB::connection('mysql')->table('baselinev2')->whereBetween('Date', [$startDateTime, $endDateTime])->where('Truck', '=', $rows->Truck)->orderBy('Date')->orderBy('Time')->skip(1)->take($count - 1)->get();
       $prevTruck =  DB::connection('mysql')->table('baselinev2')->whereBetween('Date', [$startDateTime, $endDateTime])->where('Truck', '=', $rows->Truck)->orderBy('Date')->orderBy('Time')->first();
        //   dd($trucks);
      foreach ($trucks as $truckrows => $trip) {
   
          $currentTrip = $trip->OnTheRoad;
          $prev = $prevTruck->id;
    
         $previousFullTrip = DB::connection('mysql')->table('baselinev2')->where('id', '=',  $prev)->first();

        if($currentTrip == 'on the road' AND $previousFullTrip->OnTheRoad == 'False'){

          // $trucksArray = $trucks->toArray(); 
          // $seventeenth = array_slice($trucksArray,$truckrows - 17, 1);
          // $seventeenthRow = end($seventeenth);
          $seven = $truckrows - 17;
         // dd($truckrows,$seventeenthRow->id,$trucks[$seven]->id);
          $updatetripstart = DB::connection('mysql')->table('baselinev2')->where('id', '=', $trucks[$seven]->id)->where('Truck', '=', $rows->Truck)->update([

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

        $truckData = DB::connection('mysql')->table('baselinev2')->whereBetween('Date', ['2024-01-01' , '2024-01-31'])->groupBy('Truck')->orderBy('id')->get();
        // $truckData = $truckData->take(2);
        //   dd($truckData);

         foreach ($truckData as $truckCode => $rows) {

          Log::info('Started trip test on', ['Truck' => $rows->Truck, '#' => $truckCode]);
          $startDate = '2024-01-01'; // Replace with your start date
          $endDate = '2024-01-31';   // Replace with your end date
  
          // Convert to DateTime objects
          $startDateTime = new DateTime($startDate);
          $endDateTime = new DateTime($endDate);
     
          $count =  DB::connection('mysql')->table('baselinev2')->where('Truck', '=', $rows->Truck)->whereBetween('Date', [$startDateTime, $endDateTime])->where('Truck', '=', $rows->Truck)->orderBy('Date')->orderBy('Time')->count();

          if($count > 0){

         $trucks =  DB::connection('mysql')->table('baselinev2')->where('Truck', '=', $rows->Truck)->whereBetween('Date', [$startDateTime, $endDateTime])->where('Truck', '=', $rows->Truck)->orderBy('Date')->orderBy('Time')->skip(1)->take($count - 1)->get();
           //  dd($trucks);
        foreach ($trucks as  $truckrows => $trip) {
         
            if($trip->TripStart == 'Trip Start'){

                $updatetriptest = DB::connection('mysql')->table('baselinev2')->where('id', '=', $trip->id)->update([

                    'TripTest' => 'Trip Start'
    
                   ]);
                    
            }

            if($trip->TripEnd == 'Trip Ended'){

                $updatetriptest = DB::connection('mysql')->table('baselinev2')->where('id', '=', $trip->id)->update([

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
           
            
            $truckData = DB::connection('mysql')->table('baselinev2')->whereBetween('Date', ['2024-01-01' , '2024-01-31'])->groupBy('Truck')->orderBy('id')->get();
            // $truckData = $truckData->take(2);
            //   dd($truckData);
    
             foreach ($truckData as $truckCode => $rows) {
    
              Log::info('Started trip test updated on', ['Truck' => $rows->Truck, '#' => $truckCode]);
              $startDate = '2024-01-01'; // Replace with your start date
              $endDate = '2024-01-31';   // Replace with your end date
  
          // Convert to DateTime objects
          $startDateTime = new DateTime($startDate);
          $endDateTime = new DateTime($endDate);
     
          $count =  DB::connection('mysql')->table('baselinev2')->where('Truck', '=', $rows->Truck)->whereBetween('Date', [$startDateTime, $endDateTime])->where('TripTest', '=', 'Trip Start')->orWhere('TripTest', '=', 'Trip Ended')->where('Truck', '=', $rows->Truck)->orderBy('Date')->orderBy('Time')->count();

          if($count > 0){

          $trucks =  DB::connection('mysql')->table('baselinev2')->where('Truck', '=', $rows->Truck)->whereBetween('Date', [$startDateTime, $endDateTime])->where('TripTest', '=', 'Trip Start')->orWhere('TripTest', '=', 'Trip Ended')->where('Truck', '=', $rows->Truck)->orderBy('Date')->orderBy('Time')->skip(1)->take($count - 1)->get();
    
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
                 
                  $update1 =  DB::connection('mysql')->table('baselinev2')->where('id','=',$TripEnd->id)->update([
    
                   'TripTest' => null,
                   'Trip' => '2'
                  ]);

                  
                  $update2 =  DB::connection('mysql')->table('baselinev2')->where('id','=',$nexttrip->id)->update([
    
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
         ini_set('max_execution_time', 36000000); // 3600 seconds = 60 minutes
         set_time_limit(36000000);

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
       
       $truckData = DB::connection('mysql')->table('baselinev2')->whereBetween('Date', ['2024-01-01' , '2024-01-31'])->groupBy('Truck')->orderBy('id')->get();
       // $truckData = $truckData->take(2);
       //   dd($truckData);

        foreach ($truckData as $truckCode => $rows) {

         Log::info('Started trip end  on', ['Truck' => $rows->Truck, '#' => $truckCode]);
         $startDate = '2024-01-01'; // Replace with your start date
         $endDate = '2024-01-31';   // Replace with your end date

        // Convert to DateTime objects
        $startDateTime = new DateTime($startDate);
        $endDateTime = new DateTime($endDate);
   
        $count =  DB::connection('mysql')->table('baselinev2')->whereBetween('Date', [$startDateTime, $endDateTime])->where('Truck', '=', $rows->Truck)->orderBy('Date')->orderBy('Time')->count();
        if($count > 0){
        $trucks =  DB::connection('mysql')->table('baselinev2')->whereBetween('Date', [$startDateTime, $endDateTime])->where('Truck', '=', $rows->Truck)->orderBy('Date')->orderBy('Time')->skip(1)->take($count - 1)->get();
       // $prevTruck =  DB::connection('mysql')->table('baseline')->where('Truck', '=', $rows->Truck)->whereBetween('Date', [$startDateTime, $endDateTime])->orderBy('Date')->orderBy('Time')->first();
           //dd($trucks);
        foreach ($trucks as $truckrows => $trip) {

         // dd($count,$trucks);

          if($truckrows != ($count - 2)){       
        
          $nextIndex = $truckrows + 1;
        
         $currentTrip = DB::connection('mysql')->table('baselinev2')->where('id', '=', $trip->id)->first(); 
            
         $nextTrip = DB::connection('mysql')->table('baselinev2')->where('id', '=', $trucks[$nextIndex]->id)->first(); 
           //dd($trucks,$nextTrip,$truckrows);  
         if($currentTrip->OnTheRoad == "on the road" && $nextTrip->OnTheRoad == "False"){

            $test = "Trip Ended";

          }else{

           $test = "N/A";

          }

       //  dd($test);

         $tripUpdate = DB::connection('mysql')->table('baselinev2')->where('id', '=', $trip->id)->update([

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
    
        $truckData = DB::connection('mysql')->table('baselinev2')->whereBetween('Date', ['2024-01-01' , '2024-01-31'])->groupBy('Truck')->orderBy('id')->get();
        // $truckData = $truckData->take(2);
        //   dd($truckData);

         foreach ($truckData as $truckCode => $rows) {

          Log::info('Started geofence on', ['Truck' => $rows->Truck, '#' => $truckCode]);
          $startDate = '2024-01-01'; // Replace with your start date
          $endDate = '2024-01-31';   // Replace with your end date
  
          // Convert to DateTime objects
          $startDateTime = new DateTime($startDate);
          $endDateTime = new DateTime($endDate);
     
          //$count =  DB::connection('mysql')->table('baseline')->where('Truck', '=', $rows->Truck)->whereBetween('Date', [$startDateTime, $endDateTime])->orderBy('Date')->orderBy('Time')->count();
      //   $trucks =  DB::connection('mysql')->table('baseline')->where('Truck', '=', $rows->Truck)->whereBetween('Date', [$startDateTime, $endDateTime])->orderBy('Date')->orderBy('Time')->skip(1)->take($count - 1)->get();
     
         $trucks =  DB::connection('mysql')->table('baselinev2')->where('Truck', '=', $rows->Truck)->whereBetween('Date', [$startDateTime, $endDateTime])->where('TripTest', '=', 'Trip Start')->orwhere('TripTest', '=', 'Trip Ended')->where('Truck', '=', $rows->Truck)->orderBy('Date')->orderBy('Time')->get();
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
            $tripUpdate = DB::connection('mysql')->table('baselinev2')->where('id', '=', $trip->id)->update([

                'Geofence' => $location
        
               ]);  
            break;

        }else{

            $location = "Outside Geofence";
            $tripUpdate = DB::connection('mysql')->table('baselinev2')->where('id', '=', $trip->id)->update([

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
            $tripUpdate = DB::connection('mysql')->table('baselinev2')->where('id', '=', $trip->id)->update([

                'Geofence' => $location
        
               ]); 
               break;

        }else{

            $location = "Outside Geofence";
            $tripUpdate = DB::connection('mysql')->table('baselinev2')->where('id', '=', $trip->id)->update([

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

        $truckData = DB::connection('mysql')->table('baselinev2')->whereBetween('Date', ['2024-01-01' , '2024-01-31'])->groupBy('Truck')->orderBy('id')->get();
       // dd($truckData);

         foreach ($truckData as $truckCode => $rows) {

          Log::info('Started Time Difference on', ['Truck' => $rows->Truck,  '#' => $truckCode]);
          $startDate = '2024-01-01'; // Replace with your start date
          $endDate = '2024-01-31';   // Replace with your end date

          // Convert to DateTime objects
          $startDateTime = new DateTime($startDate);
          $endDateTime = new DateTime($endDate);
     
         $trucks =  DB::connection('mysql')->table('baselinev2')->where('Truck', '=', $rows->Truck)->whereBetween('Date', [$startDateTime, $endDateTime])->orderBy('Date')->orderBy('Time')->get();
         //dd($trucks);
          foreach ($trucks as  $truckrows => $trip) {

        $currentTrip = DB::connection('mysql')->table('baselinev2')->where('id', '=', $trip->id)->first(); 
  
        if($truckrows  > 0){
          $nextIndex = $truckrows - 1;
        }else{
          $nextIndex = 0;
        }
                  
         $previousTrip = DB::connection('mysql')->table('baselinev2')->where('id', '=',  $trucks[$nextIndex]->id)->first(); 

         $interval =  date_diff(date_create($currentTrip->Time),date_create($previousTrip->Time));        
        // dd($interval);
         $tripUpdate = DB::connection('mysql')->table('baselinev2')->where('id', '=', $trip->id)->update([

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

        $truckData = DB::connection('mysql')->table('baselinev2')->whereBetween('Date', ['2024-01-01' , '2024-01-31'])->groupBy('Truck')->orderBy('id')->get();
        // $truckData = $truckData->take(2);
        //   dd($truckData);

         foreach ($truckData as $truckCode => $rows) {

          Log::info('Started cycle time on', ['Truck' => $rows->Truck, '#' => $truckCode]);
          $startDate = '2024-01-01'; // Replace with your start date
          $endDate = '2024-01-31';   // Replace with your end date
  
          // Convert to DateTime objects
          $startDateTime = new DateTime($startDate);
          $endDateTime = new DateTime($endDate);
    
    
        $prevTruck =  DB::connection('mysql')->table('baselinev2')->where('Truck', '=', $rows->Truck)->whereBetween('Date', [$startDateTime, $endDateTime])->where('Truck', '=', $rows->Truck)->orderBy('Date')->orderBy('Time')->first();
    
       $trucks = DB::connection('mysql')->table('baselinev2')->where('Truck', '=', $rows->Truck)->whereBetween('Date', [$startDateTime, $endDateTime])->where('TripTest', '=', 'Trip Start')->orWhere('TripTest', '=', 'Trip Ended')->where('Truck', '=', $rows->Truck)->orderBy('Date')->orderBy('Time')->get();
      
       if($prevTruck != null){
      
        $previousId =  $prevTruck->id;
       }

     
      //  $trucks =  DB::connection('mysql')->table('baseline')->where('Truck', '=', $rows->Truck)->where('id', '>', $rows->id)->get();
           //  dd($trucks->Truck);
        foreach ($trucks as  $truckrows => $trip) {

        $currentTrip = DB::connection('mysql')->table('baselinev2')->where('id', '=', $trip->id)->first(); 
            
        $previousTrip = DB::connection('mysql')->table('baselinev2')->where('id', '=', $previousId )->first(); 

        $interval =  date_diff(date_create($currentTrip->Time),date_create($previousTrip->Time)); 
       // dd($interval);  
       
       if($trip->TripTest == 'Trip Start'){

        $cycle = 'Load/Offload Time';

       }elseif($trip->TripTest == 'Trip Ended'){

        $cycle = 'Travel Time';

       }else{

        $cycle = null;

       }


        $tripUpdate = DB::connection('mysql')->table('baselinev2')->where('id', '=', $trip->id)->update([

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
       
        $truckData = DB::connection('mysql')->table('baselinev2')->whereBetween('Date', ['2024-01-01' , '2024-01-31'])->groupBy('Truck')->orderBy('id')->get();
        // $truckData = $truckData->take(2);
        //   dd($truckData);

         foreach ($truckData as $truckCode => $rows) {

          Log::info('Started movingstationary on', ['Truck' => $rows->Truck, '#' => $truckCode]);
          $startDate = '2024-01-01'; // Replace with your start date
          $endDate = '2024-01-31';   // Replace with your end date
          // Convert to DateTime objects
          if($truckCode > 146 ){
          $startDateTime = new DateTime($startDate);
          $endDateTime = new DateTime($endDate);
     
         $trucks =  DB::connection('mysql')->table('baselinev2')->where('Truck', '=', $rows->Truck)->whereBetween('Date', [$startDateTime, $endDateTime])->orderBy('Date')->orderBy('Time')->get();
      //  dd($trucks);
        foreach ($trucks as $truckrows =>$trip) {

            if($trip->CoordinateTest == 0){

                $update = DB::connection('mysql')->table('baselinev2')->where('id', '=', $trip->id)->update([

                    'StationaryMoving' => 'Moving'
                ]);

            }elseif($trip->CoordinateTest == 1 && $trip->Distance > 0.0 && $trip->HighSpeed > 0){

                $update = DB::connection('mysql')->table('baselinev2')->where('id', '=', $trip->id)->update([

                    'StationaryMoving' => 'Moving'
                ]);

            }else{

                $update = DB::connection('mysql')->table('baselinev2')->where('id', '=', $trip->id)->update([

                    'StationaryMoving' => 'Stationary'
                ]);
            }
        }

      }

        Log::info('Finished movingStationary on', ['Truck' => $rows->Truck,  '#' => $truckCode]);

      }

    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //Second Baseline (Power BI logic converted to SQL)




    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////











   ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    public function truckLogic()
    {    
      
      ini_set('max_execution_time', 3600000000000); // 3600 seconds = 60 minutes
      set_time_limit(360000000000);
     
      $truckData = DB::connection('mysql')->table('baselinev2')->whereBetween('Date', ['2024-01-01' , '2024-01-31'])->groupBy('Truck')->orderBy('id')->get();
      // $truckData = $truckData->take(2);
      //   dd($truckData);

       foreach ($truckData as $truckCode => $rows) {

        Log::info('Started truck logic on', ['Truck' => $rows->Truck, '#' => $truckCode]);
        $startDate = '2024-01-01'; // Replace with your start date
        $endDate = '2024-01-31';   // Replace with your end date

        // Convert to DateTime objects
        $startDateTime = new DateTime($startDate);
        $endDateTime = new DateTime($endDate);
   
        $trucks =  DB::connection('mysql')->table('baselinev2')->where('Truck', '=', $rows->Truck)->whereBetween('Date', [$startDateTime, $endDateTime])->where('TripTest', '=', 'Trip Start')->orwhere('TripTest', '=', 'Trip Ended')->whereBetween('Date', [$startDateTime, $endDateTime])->where('Truck', '=', $rows->Truck)->orderBy('Date')->orderBy('Time')->get();
    
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
       $truckData =  DB::connection('mysql')->table('baselinetest')->groupby('Truck')->orderBy('id')->get();

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
       
      if($truckMap > 0){

        $trucks =  DB::connection('mysql')->table('baselinetest')->where('Truck' ,'=', $rows->Truck )->where('TripClassificationv2', '=', 'Trip Start')->orderby('DateUpdated')->orderBy('Time')->get();
       // dd( $truckCount,$trucks);
       
      foreach ($trucks as $truckrows => $trip) {

          Log::info('started fuel-sub on', ['Truck' => $trip->Truck, 'row #' => $truckrows]);
        $endtrip = DB::connection('mysql')->table('baselinetest')->where('id', '>', $trip->id )->where('TripClassificationv2','=', 'Trip End')->first();
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

         // dd($data1,$data2,$trip,$endtrip);
       if($http_code1 == 200 && $http_code2 == 200 ){

        if($data1->data->fuel_consumed != 0 && $data2->data->distance != 0){

          $endtrip = DB::connection('mysql')->table('baselinetest')->where('id', '=',  $endtrip->id)->update([

            'TotalFuelUsed' => $data1->data->fuel_consumed,
            'TotalDistance' => $data2->data->distance/1000,
            'TotalConsumption' => 1/($data1->data->fuel_consumed/($data2->data->distance/1000))
          ]);

        }else{

          $endtrip = DB::connection('mysql')->table('baselinetest')->where('id', '=',  $endtrip->id)->update([

            'TotalFuelUsed' => $data1->data->fuel_consumed,
            'TotalDistance' => $data2->data->distance/1000,
            'TotalConsumption' => 0
          ]);
        }
  
      }

      //  dd($start_timestamp,$end_timestamp,$data1,$data2);

  

      }

     }

     }
      Log::info('finished transfer on', ['Truck' => 'All']);
      dd('done');

   }             
 
     //1
     public function SoapFleetboard()
    {
        
        ini_set('max_execution_time', 360000000000); // 3600 seconds = 60 minutes
        set_time_limit(360000000000);

        $truckData = DB::connection('mysql')->table('baselinetest')->groupBy('Truck')->orderBy('id')->get();
   
        foreach ($truckData as $truckCode => $rows) {

        Log::info('Started total fleet board soap on', ['Truck' => $rows->Truck,  '#' => $truckCode]);
    
       // $trucks = DB::connection('mysql')->table('baselinetest')->where('id', '=', 18481)->get();
        $trucks = DB::connection('mysql')->table('baselinetest')->where('Truck', '=', $rows->Truck)->where('TripClassificationv2', '=', 'Trip Start')->orderBy('DateUpdated')->orderBy('Time')->get();
   
        foreach ($trucks as  $truckrows => $trip) {

          Log::info('Started sub total fleet board soap on', ['Truck' => $rows->Truck,  '#' => $trip->id]);
        $nextTrip = DB::connection('mysql')->table('baselinetest')->where('id', '>', $trip->id )->where('Truck','=', $rows->Truck)->where('TripClassificationv2','=', 'Trip End')->first(); 
    
        if($nextTrip != null){

        $string = $nextTrip->Truck;
        $substring = 'Workshop';
        $substring1 = 'Parked';

      if (strpos($string, $substring) !== false) {

       $results = str_replace($substring, '', $string);
     //'The string contains the word "Workshop"'
      } elseif(strpos($string, $substring1) !== false) {

       $results = str_replace($substring1, '', $string);

      }else{

       $results = $nextTrip->Truck;
      }

      $string = $results;
      $parts = explode(" ", $string);
      $result = $parts[0];
     // dd($result);

      $count = DB::connection('mysql')->table('decconsumption')->where('Fleet', '=',  $result)->count();
      //  dd($count,$result,$trip);
      if($count > 0){

        $fleettruck = DB::connection('mysql')->table('decconsumption')->where('Fleet', '=',  $result)->first();

      $time = substr($trip->Time, 0, 8);
      $start_timestamp = $trip->DateUpdated . ' ' .$time;
     
      $endtime = substr($nextTrip->Time, 0, 8);
      $end_timestamp =  $nextTrip->DateUpdated . ' ' .$endtime ;


      //  dd($fleettruck->VehicleID,$start_timestamp,$end_timestamp);
        
        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => 'https://soap.fleetboard.com/vmsoap_v1_1/services/TripRecordService',
          CURLOPT_RETURNTRANSFER => true,
        // CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'POST',
          CURLOPT_POSTFIELDS =>'<?xml version="1.0" encoding="UTF-8"?>
        <soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:data="http://www.fleetboard.com/data">
            <soapenv:Header/>
            <soapenv:Body>
                <data:getTripRecord>
                    <data:GetTripRecordRequest limit="100" offset="0">
                          <data:VehicleID>'.$fleettruck->VehicleID.'</data:VehicleID>
                        <data:TimeRange>
                            <data:Model>PERIOD</data:Model>
                            <data:Period>
                                <data:Begin>'.$start_timestamp.'</data:Begin>
                                <data:End>'. $end_timestamp.'</data:End>
                            </data:Period>
                        </data:TimeRange>
                    </data:GetTripRecordRequest>
                </data:getTripRecord>
            </soapenv:Body>
        </soapenv:Envelope>',
          CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json',
            'SOAPAction: getTripRecord',
            'Cookie: JSESSIONID=0001HiVta-RNmSQyR17Yuzh9lW6:prdwas03l3m2'
          ),
        ));

     $response = curl_exec($curl);

      curl_close($curl);
      $xml =  preg_replace("/(<\/?)(\w+):([^>]*>)/", "$1$2$3", $response);
      $xml = simplexml_load_string($xml);
      $json = json_encode($xml);
      $responseArray = json_decode($json,true);
     
      //    dd($responseArray,$trip);
     // dd($responseArray['soapenvBody']['p725getTripRecordResponse']['p725GetTripRecordResponse']['@attributes']['responseSize']);
      $checkResponse = $responseArray['soapenvBody']['p725getTripRecordResponse']['p725GetTripRecordResponse']['@attributes']['responseSize'];
      // dd($responseArray,$trip,$nextTrip);

       if($checkResponse == 0 || $checkResponse == 1){
       //  dd('hakuna');

      }else{

      $fleettrips = $responseArray['soapenvBody']['p725getTripRecordResponse']['p725GetTripRecordResponse']['p725TripRecordReport'];
     // dd($fleettrips);

      foreach($fleettrips as $fleettrip){

        $timestamp =  $fleettrip['p725Start']['p725VehicleTimestamp'];
        list($date, $time) = explode(" ", $timestamp);


        $trucks =  DB::connection('mysql')->table('novconsumption')->insert([

          'Truck' => $trip->Truck,
          'DateUpdated' => $date,
          'Time' => $time,
          'Mileage' => $fleettrip['p725Start']['p725Mileage'],
          'Kilometers' => $fleettrip['p725Start']['p725Position']['p725KM'],
          'State' => $fleettrip['p725TripRecordKind'],
         // 'consumption' => $fleettrip['p725Consumption'],
          'fuellevel' => $fleettrip['p725FuelLevel'],
          'trip' => 'Start',
  
         ]);


         $timestamp1 =  $fleettrip['p725End']['p725VehicleTimestamp'];
         list($date1, $time1) = explode(" ", $timestamp1);
      
         $trucksend =  DB::connection('mysql')->table('novconsumption')->insert([

          'Truck' => $trip->Truck,
          'DateUpdated' => $date1,
          'Time' => $time1,
          'Mileage' => $fleettrip['p725End']['p725Mileage'],
          'Kilometers' => $fleettrip['p725End']['p725Position']['p725KM'],
          'State' => $fleettrip['p725TripRecordKind'],
         // 'consumption' => $fleettrip['p725Consumption'],
          'fuellevel' => $fleettrip['p725FuelLevel'],
          'trip' => 'End',
  
         ]);

       //  dd('done');
      }
      
      }

        } 
 
         }

        }

        Log::info('Finished total fleet board soap on', ['Truck' => $rows->Truck,  '#' => $truckCode]);

       } 

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
      //   dd($trucks);
        foreach ($trucks as  $truckrows => $trip) {
            
        $nextTrip = DB::connection('mysql')->table('baselinetest')->where('id', '>', $trip->id )->where('TripClassificationv2','=', 'Trip End')->first(); 
     //  dd('doing');
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

       // dd($interval,$trip,$nextTrip,$loading);

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


       dd('done.');
     
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
      // dd($trip,$nextTrip);
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
       ->orwhere('TripClassification', 'at Depot Trip')->whereBetween('id', [$trip->id, $nextTrip->id])
       ->first();


       $rbay =  DB::connection('mysql')->table('baselinetest')->whereBetween('id', [$trip->id, $nextTrip->id])
       ->where('TripClassification', 'on Route (RBay)')
       ->first();

       $fromDepot =  DB::connection('mysql')->table('baselinetest')->whereBetween('id', [$trip->id, $nextTrip->id])
       ->where('TripClassification', 'from Depot Trip')
       ->first();

       //all aspects
       if($offload == null && $offload2 == null && $interval == null && $fromDepot == null && $interval2 == null &&  $rbay == null &&  $todepot != null){
       //   dd($todepot);
        $tripUpdate = DB::connection('mysql')->table('baselinetest')->where('id', '=', $nextTrip->id)->update([

        'TripF1' => 'Depot trip at '.$todepot->GFupdated1

        ]);

       }

       if($offload == null && $offload2 == null && $interval == null && $fromDepot != null && $interval2 == null &&  $rbay == null &&  $todepot == null){

        $prev =  DB::connection('mysql')->table('baselinetest')
        ->where('id','=', $fromDepot->id - 1)
        ->first();

         $tripUpdate = DB::connection('mysql')->table('baselinetest')->where('id', '=', $nextTrip->id)->update([
 
         'TripF1' => 'Depot trip at '.$prev->GFupdated1
       ]);
 
        }

       if($offload == null && $offload2 == null && $interval == null && $interval2 == null &&  $rbay != null &&  $todepot != null){
        //   dd($todepot);
         $tripUpdate = DB::connection('mysql')->table('baselinetest')->where('id', '=', $nextTrip->id)->update([
 
         'TripF1' => 'Return Trip from Richards Bay'
       ]);
 
        }

        if($offload == null && $offload2 == null && $interval == null && $interval2 == null &&  $rbay != null &&  $todepot == null){
          //   dd($todepot);
           $tripUpdate = DB::connection('mysql')->table('baselinetest')->where('id', '=', $nextTrip->id)->update([
   
           'TripF1' => 'Return Trip from Richards Bay'
         ]);
   
          }
       // dd('hakuna');

       }

      }

      Log::info('Finished loading Times v2 on', ['Truck' => $rows->Truck,  '#' => $truckCode]);

     } 

     dd('done');

   
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

       dd('done');

     
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
        
      //  $trucks = DB::connection('mysql')->table('baselinetest')->where('id', '=', 16059)->get();
     
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

        $fulltrip = strpos($trip->TripF1, "Full Trip:");
        $offloading = strpos($trip->TripF1, "Offloading Trip");
      //  dd($fulltrip);
        if($fulltrip !== false){

         // dd('1');
         $updatefleet = DB::connection('mysql')->table('baselinetest')->where('id', '=', $trip->id )->update([
         
          'Trucktype' => $truckData->type,
          'TonnesMoved' => $capacity * 0.944

        ]);

        }elseif($offloading !== false){
          
        //  dd('2');
          $updatefleet = DB::connection('mysql')->table('baselinetest')->where('id', '=', $trip->id )->update([
         
            'Trucktype' => $truckData->type,
            'TonnesMoved' => $capacity * 0.944
  
          ]);
  

        }else{

       //   dd('3');
          $updatefleet = DB::connection('mysql')->table('baselinetest')->where('id', '=', $trip->id )->update([
         
            'Trucktype' => $truckData->type,
            'TonnesMoved' => 0
  
          ]);
        }

         }else{

          $fulltrip = strpos($trip->TripF1, "Full Trip:");
          $offloading = strpos($trip->TripF1, "Offloading Trip");
        //  dd($fulltrip);
          if($fulltrip !== false){
  
           $updatefleet = DB::connection('mysql')->table('baselinetest')->where('id', '=', $trip->id )->update([
           
            'Trucktype' => 'N/A',
            'TonnesMoved' => 32
  
          ]);
  
          }elseif($offloading !== false){
            
          //  dd('2');
            $updatefleet = DB::connection('mysql')->table('baselinetest')->where('id', '=', $trip->id )->update([
           
              'Trucktype' =>'N/A',
              'TonnesMoved' => 32
    
            ]);
    
  
          }else{
  
         //   dd('3');
            $updatefleet = DB::connection('mysql')->table('baselinetest')->where('id', '=', $trip->id )->update([
           
              'Trucktype' => 'N/A',
              'TonnesMoved' => 0
    
            ]);

          }

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
       // dd($result);

        $count = DB::connection('mysql')->table('fleetlist')->where('fleetNumber', '=',  $result)->count();
         
        if($count > 0){
          
          $truckmap = DB::connection('mysql')->table('fleetlist')->where('fleetNumber', '=',  $result)->first();

          //  dd($count,$result,$truckmap);
          $dateString = $trip->DateUpdated;
          $timestamp = strtotime($dateString);
          $month = date('W', $timestamp);
        //  dd($month);
       
            $oct = DB::connection('mysql')->table('quarterlyconsumption')->where('Fleet', '=', $truckmap->fleetNumber )->where('Week','=', $month)->count();
            // dd($oct);
            if($oct > 0){
          //   dd('iripo');
              $octData = DB::connection('mysql')->table('quarterlyconsumption')->where('Fleet', '=', $truckmap->fleetNumber )->where('Week','=', $month)->first();

              $tripUpdate = DB::connection('mysql')->table('baselinetest')->where('id', '=', $trip->id)->update([

                'fuelConsumption' => $octData->AveConsumptionPerKm
     
             ]); 

            }else{
             
              $qrt = DB::connection('mysql')->table('quarterlyconsumption')->where('Fleet', '=', $truckmap->fleetNumber )->count();
           //   dd('haipo',$qrt,$trip);
                if($qrt > 0){

                  $qrtData = DB::connection('mysql')->table('quarterlyconsumption')->where('Fleet', '=', $truckmap->fleetNumber )->first();
                  $tripUpdate = DB::connection('mysql')->table('baselinetest')->where('id', '=', $trip->id)->update([

                    'fuelConsumption' => $qrtData->AveConsumptionPerKm
         
                 ]); 
    
                }

            }

          //  dd('doddod');

        

        }

        }

        Log::info('Finished fleetboard Capacity on', ['Truck' => $rows->Truck,  '#' => $truckCode]);

       } 

       dd('done');

             
    }

    public function TotalDistanceFuel()
    {
        
        ini_set('max_execution_time', 360000000000); // 3600 seconds = 60 minutes
        set_time_limit(360000000000);

        $truckData = DB::connection('mysql')->table('baselinetest')->groupBy('Truck')->orderBy('id')->get();
   
        foreach ($truckData as $truckCode => $rows) {

        Log::info('Started total Distance on', ['Truck' => $rows->Truck,  '#' => $truckCode]);
    
     //   $trucks = DB::connection('mysql')->table('baselinetest')->where('id', '=', 46883)->get();
        $trucks = DB::connection('mysql')->table('baselinetest')->where('Truck', '=', $rows->Truck)->where('TripClassificationv2', '=', 'Trip Start')->orderBy('DateUpdated')->orderBy('Time')->get();
   
        foreach ($trucks as  $truckrows => $trip) {
            
        $nextTrip = DB::connection('mysql')->table('baselinetest')->where('id', '>', $trip->id )->where('TripClassificationv2','=', 'Trip End')->first(); 
    
        if($nextTrip != null){

        $distance =  DB::connection('mysql')->table('baselinetest')->whereBetween('id', [$trip->id, $nextTrip->id])
        ->sum('distanceCovered');

        $fuel =  DB::connection('mysql')->table('baselinetest')->whereBetween('id', [$trip->id, $nextTrip->id])
        ->sum('fuelUsed');
        //  dd($distance,$fuel, 1/($fuel/($distance/1000))); 

        if($distance > 0 && $fuel > 0){

        $tripUpdate = DB::connection('mysql')->table('baselinetest')->where('id', '=', $nextTrip->id)->update([

           'TotalDistance' => $distance/1000,
           'TotalFuelUsed' => $fuel,
           'TotalConsumption' => 1/($fuel/($distance/1000))

        ]); 
      }else{
        
        $tripUpdate = DB::connection('mysql')->table('baselinetest')->where('id', '=', $nextTrip->id)->update([

          'TotalDistance' => $distance,
          'TotalFuelUsed' => $fuel,
          'TotalConsumption' => $nextTrip->fuelConsumption

       ]); 
      }
 
      $idlingFuel =  DB::connection('mysql')->table('baselinetest')->whereBetween('id', [$trip->id, $nextTrip->id])->where('TripClassification', 'at Stop')
       ->orwhere('TripClassification', 'at Depot Trip')->whereBetween('id', [$trip->id, $nextTrip->id])
      ->orwhere('TripClassification', 'at Repair')->whereBetween('id', [$trip->id, $nextTrip->id])
       ->orwhere('TripClassification', 'at Theft')->whereBetween('id', [$trip->id, $nextTrip->id])
      ->sum('fuelUsed');

            $tripUpdate = DB::connection('mysql')->table('baselinetest')->where('id', '=', $nextTrip->id)->update([

          'idlingFuelUsed' => $idlingFuel

        ]);

        ///////////////////////////////////////////////////////////////////////////////////////////////////


        $string = $nextTrip->Truck;
        $substring = 'Workshop';
        $substring1 = 'Parked';

      if (strpos($string, $substring) !== false) {

       $results = str_replace($substring, '', $string);
     //'The string contains the word "Workshop"'
      } elseif(strpos($string, $substring1) !== false) {

       $results = str_replace($substring1, '', $string);

      }else{

       $results = $nextTrip->Truck;
      }

      $string = $results;
      $parts = explode(" ", $string);
      $result = $parts[0];
     // dd($result);

      $count = DB::connection('mysql')->table('fleetlist')->where('fleetNumber', '=',  $result)->count();
       
      if($count > 0){
        
        $truckmap = DB::connection('mysql')->table('fleetlist')->where('fleetNumber', '=',  $result)->first();

        //  dd($count,$result,$truckmap);
        $dateString = $nextTrip->DateUpdated;
        $timestamp = strtotime($dateString);
        $month = date('W', $timestamp);
      //  dd($month);
     
          $oct = DB::connection('mysql')->table('quarterlyconsumption')->where('Fleet', '=', $truckmap->fleetNumber )->where('Week','=', $month)->count();
          // dd($oct);
          if($oct > 0){
        //   dd('iripo');
            $octData = DB::connection('mysql')->table('quarterlyconsumption')->where('Fleet', '=', $truckmap->fleetNumber )->where('Week','=', $month)->first();
        
            $cleanedDistance = str_replace(' ', '', $octData->TotalDistance);
            $distance = (float)$cleanedDistance;
        
            $cleanedConsumption = str_replace(' ', '', $octData->TotalConsumption);
            $consumption = (float)$cleanedConsumption;

            $cleanedIdling = str_replace(' ', '', $octData->IdlingFuelUsed);
            $idling = (float)$cleanedIdling;
           
           // dd($distance,$consumption, $idling);

            $tripUpdate = DB::connection('mysql')->table('baselinetest')->where('id', '=', $nextTrip->id)->update([

              'TotalDistance' => $distance,
              'TotalFuelUsed' => $consumption,
              'TotalConsumption' => $qrtData->AveConsumptionPerKm,
              'idlingFuelUsed' => $idling
   
           ]); 

          }else{
           
            $qrt = DB::connection('mysql')->table('quarterlyconsumption')->where('Fleet', '=', $truckmap->fleetNumber )->count();
         //   dd('haipo',$qrt,$trip);
              if($qrt > 0){

                $qrtData = DB::connection('mysql')->table('quarterlyconsumption')->where('Fleet', '=', $truckmap->fleetNumber )->first();

                $cleanedDistance = str_replace(' ', '', $qrtData->TotalDistance);
                $distance = (float)$cleanedDistance;
            
                $cleanedConsumption = str_replace(' ', '', $qrtData->TotalConsumption);
                $consumption = (float)$cleanedConsumption;
    
                $cleanedIdling = str_replace(' ', '', $qrtData->IdlingFuelUsed);
                $idling = (float)$cleanedIdling;

               // dd($distance,$consumption, $idling);

                $tripUpdate = DB::connection('mysql')->table('baselinetest')->where('id', '=', $nextTrip->id)->update([

                  'TotalDistance' =>  $distance,
                  'TotalFuelUsed' =>  $consumption,
                  'TotalConsumption' => $qrtData->AveConsumptionPerKm,
                  'idlingFuelUsed' =>  $idling 
       
               ]); 
  
              }

          }

        //  dd('doddod');     

      }


         }

        }

        Log::info('Finished total Distance on', ['Truck' => $rows->Truck,  '#' => $truckCode]);

       } 

       dd('done');

 
     
    }

    public function RouteClassification()
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
     
           $fulltrip = strpos($nextTrip->TripF1, "Full Trip:");
           $offloading = strpos($nextTrip->TripF1, "Offloading Trip");
           $loadingtTrip = strpos($nextTrip->TripF1, "Loading Trip");
           $depotTrip = strpos($nextTrip->TripF1, "Depot trip");
           $returnRbay = strpos($nextTrip->TripF1, "Return Trip");
         //  dd($fulltrip);
           if($fulltrip !== false){
   
            // dd('1');
            $updatefleet = DB::connection('mysql')->table('baselinetest')->whereBetween('id', [$trip->id, $nextTrip->id])->update([
            
             'RouteClassification' => 'Full Trip',
             'RouteLocation' => $trip->LoadingPoint .' to '. $nextTrip->OffloadingPoint
   
           ]);
   
           }elseif($offloading !== false){
             
           //  dd('2');
             $updatefleet = DB::connection('mysql')->table('baselinetest')->whereBetween('id', [$trip->id, $nextTrip->id])->update([
            
              'RouteClassification' => 'Offloading Trip',
               'RouteLocation' => $nextTrip->OffloadingPoint
     
             ]);
     
   
           }elseif($loadingtTrip !== false){
             
            //  dd('2');
              $updatefleet = DB::connection('mysql')->table('baselinetest')->whereBetween('id', [$trip->id, $nextTrip->id])->update([
             
                'RouteClassification' => 'Loading Trip',
                'RouteLocation' => $trip->LoadingPoint 
      
              ]);
      
    
            }elseif($depotTrip !== false){
             
              $location = str_replace("Depot trip at ", "", $nextTrip->TripF1);
                $updatefleet = DB::connection('mysql')->table('baselinetest')->whereBetween('id', [$trip->id, $nextTrip->id])->update([
               
                  'RouteClassification' => 'Depot Trip',
                  'RouteLocation' =>  $location
        
                ]);
        
      
              }elseif($returnRbay !== false){
             
                //  dd('2');
                  $updatefleet = DB::connection('mysql')->table('baselinetest')->whereBetween('id', [$trip->id, $nextTrip->id])->update([
                 
                    'RouteClassification' => 'Return from Richards Bay Trip',
                    'RouteLocation' => 'Richards Bay'
          
                  ]);
          
        
                }else{
   
        
              }



         }

       }

      Log::info('Finished Trip Route on', ['Truck' => $rows->Truck,  '#' => $truckCode]);

      } 

     dd('done');

    }

    public function TimeSpentPercentage()
    {
        
        ini_set('max_execution_time', 360000000000); // 3600 seconds = 60 minutes
        set_time_limit(360000000000);

        $truckData = DB::connection('mysql')->table('baselinetest')->groupBy('Truck')->orderBy('id')->get();
   
        foreach ($truckData as $truckCode => $rows) {

        Log::info('Started time spent percentage on', ['Truck' => $rows->Truck,  '#' => $truckCode]);
    
        $trucks = DB::connection('mysql')->table('baselinetest')->where('Truck', '=', $rows->Truck)->where('TripClassificationv2', '=', 'Trip Start')->orderBy('DateUpdated')->orderBy('Time')->get();
     
        foreach ($trucks as  $truckrows => $trip) {
            
        $nextTrip = DB::connection('mysql')->table('baselinetest')->where('id', '>', $trip->id )->where('TripClassificationv2','=', 'Trip End')->first(); 
    
        if($nextTrip != null){

          $trips =  DB::connection('mysql')->table('baselinetest')->whereBetween('id', [$trip->id, $nextTrip->id])->get();
       
          
          foreach($trips as $tripData){

            if($tripData->CumulativeTripClassification != null){

            $percentage = (($tripData->CumulativeTripClassification/60)/$nextTrip->TripTime)*100;

            $tripUpdate = DB::connection('mysql')->table('baselinetest')->where('id', '=', $tripData->id)->update([

              'TimeSpentPercentage' => $percentage,
   
           ]); 

           }
      
          }

         }

        }

        Log::info('Finished time spent percentage  on', ['Truck' => $rows->Truck,  '#' => $truckCode]);

       } 

       dd('done');

     
    }

    public function FbCartrack()
    {

       ini_set('max_execution_time', 360000000000); // 3600 seconds = 60 minutes
       set_time_limit(360000000000);

       $truckData = DB::connection('mysql')->table('baselinetest')->groupBy('Truck')->orderBy('id')->get();
 
       foreach ($truckData as $truckCode => $rows) {

        Log::info('Started Fleetboard or cartrack on', ['Truck' => $rows->Truck,  '#' => $truckCode]);

        
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

      $truckmap = DB::connection('mysql')->table('truckmap')->where('Truck', '=',  $results)->orwhere('Registration','=', $results)->first();

      if($truckmap){
        
       // dd('in the truckmap');
       $updateTruck = DB::connection('mysql')->table('baselinetest')->where('Truck','=', $rows->Truck)->update([

        'FbCartrack' => $truckmap->Make

       ]);

      }else{

        $string = $results;
        $parts = explode(" ", $string);
        $result = $parts[0];

        $fleetcheck = DB::connection('mysql')->table('decconsumption')->where('Fleet', '=',  $result)->first();

        if($fleetcheck){
         // dd('M/B');
        $updateTruck = DB::connection('mysql')->table('baselinetest')->where('Truck','=', $rows->Truck)->update([

          'FbCartrack' => 'M/B'
  
         ]);

        }else{

          if($rows->Truck != 'SL144 JTC221MP' && $rows->Truck != 'SL153 JST082MP' && $rows->Truck != 'SL155 JST591MP' && $rows->Truck != 'SL159 JST594MP Parked' && $rows->Truck != 'KYH843MP'){
          //  dd('MAN');
            $updateTruck = DB::connection('mysql')->table('baselinetest')->where('Truck','=', $rows->Truck)->update([

            'FbCartrack' => 'MAN'
    
           ]);

          }

        //  dd('N/A in all');

        }
      }
      
       Log::info('Finished fleetboard or cartrack on', ['Truck' => $rows->Truck,  '#' => $truckCode]);

      } 

     dd('done');
    }


    public function FbCartrackDistanceLink()
    {    
      
        ini_set('max_execution_time', 360000000000); // 3600 seconds = 60 minutes
        set_time_limit(360000000000);

       // $truckData = DB::connection('mysql')->table('baselinetest')->where('FbCartrack','=', 'MAN')->groupBy('Truck')->count();
        $truckData = DB::connection('mysql')->table('baselinetest')->where('FbCartrack','=', 'M/B')->groupBy('Truck')->orderBy('id')->get();
       // dd($truckData);
        foreach ($truckData as $truckCode => $rows) {

        Log::info('Started Load Capacity on', ['Truck' => $rows->Truck,  '#' => $truckCode]);
    
        $trucks = DB::connection('mysql')->table('baselinetest')->where('TripRoute', '!=', null )->where('Truck', '=', $rows->Truck)->get();
        
      //  $trucks = DB::connection('mysql')->table('baselinetest')->where('id', '=', 16059)->get();
     
        foreach ($trucks as  $truckrows => $trip) {

          $allRoutes = DB::connection('mysql')->table('cartrackdistance')->get();
          foreach($allRoutes as $routes){
            
            $searchMatch = DB::connection('mysql')->table('cartrackdistance')->where('RouteClassification','=',$trip->RouteClassification)->where('RouteLocation','=',$trip->RouteLocation)->first();

            if($searchMatch){

              $updateTruck = DB::connection('mysql')->table('baselinetest')->where('id','=', $trip->id)->update([

                'TotalDistance' => $searchMatch->AvgTotalDistance
        
               ]);

            }

          }

        //  dd($allRoutes);

        }

        Log::info('Finished Load Capacity on', ['Truck' => $rows->Truck,  '#' => $truckCode]);

       } 

       dd('done');

             
    }


    public function FleetboardTripDataDistance()
    {
        
        ini_set('max_execution_time', 360000000000); // 3600 seconds = 60 minutes
        set_time_limit(360000000000);

        $truckData = DB::connection('mysql')->table('baselinetest')->groupBy('Truck')->orderBy('id')->get();
   
        foreach ($truckData as $truckCode => $rows) {

        Log::info('Started fb trip Distance on', ['Truck' => $rows->Truck,  '#' => $truckCode]);
    
      //  $trucks = DB::connection('mysql')->table('baselinetest')->where('id', '=', 7558)->get();
        $trucks = DB::connection('mysql')->table('baselinetest')->where('Truck', '=', $rows->Truck)->where('TripClassificationv2', '=', 'Trip Start')->orderBy('DateUpdated')->orderBy('Time')->get();
   
        foreach ($trucks as  $truckrows => $trip) {
            
        $nextTrip = DB::connection('mysql')->table('baselinetest')->where('id', '>', $trip->id )->where('Truck', '=', $rows->Truck)->where('TripClassificationv2','=', 'Trip End')->first(); 
    
        if($nextTrip != null && $trip->FbCartrack == 'M/B'){

        $mota = $trip->Truck;
       // dd($trip->Truck,$nextTrip);
        $start = DB::connection('mysql')
        ->table('novconsumption')
        ->select(DB::raw('*, ABS(TIMESTAMPDIFF(SECOND, CONCAT(DateUpdated, " ", Time), CONCAT(?, " ", ?))) AS difference'))
        ->where('Truck', '=', $mota )
        ->orderBy('difference')
        ->setBindings([$trip->DateUpdated, $trip->Time,$mota])
        ->first();


        $end = DB::connection('mysql')
        ->table('novconsumption')
        ->select(DB::raw('*, ABS(TIMESTAMPDIFF(SECOND, CONCAT(DateUpdated, " ", Time), CONCAT(?, " ", ?))) AS difference'))
        ->where('Truck', '=', $mota )
        ->orderBy('difference')
        ->setBindings([$nextTrip->DateUpdated, $nextTrip->Time,$mota])
        ->first();

        if($start != null && $end != null){
     
          $mileages = (intval($end->Mileage) - intval($start->Mileage))/1000 ;

          if($mileages <= 0 || $mileages > 10000){
         
            $mileages = NULL;

          }

        //  dd($end->Mileage,$start->Mileage,$trip,$nextTrip);

          $updateTruck = DB::connection('mysql')->table('baselinetest')->where('id','=', $nextTrip->id)->update([

            'TotalDistance' => $mileages
    
           ]);

          }

         }

        }

        Log::info('Finished fb trip Distance on', ['Truck' => $rows->Truck,  '#' => $truckCode]);

       } 

       dd('done');

      }
}
