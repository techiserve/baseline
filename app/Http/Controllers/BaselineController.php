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

      // $this->timeDifference();
      // $this->LongDifference();
      // $this->LatDifference();
      // $this->CoordinateTest();
      // $this->movingStationary();
      // $this->Count();
      // $this->OnTheRoad();
      // $this->TripStart();
      $this->tripEnd();
      $this->TripTest();
      $this->TripTestUpdated();
     // $this->cycleTime();
      $this->geofence();

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
     
         // $count =  DB::connection('mysql')->table('baseline')->where('Truck', '=', $rows->Truck)->whereBetween('Date', [$startDateTime, $endDateTime])->where('TripTest', '=', 'Trip Start')->orWhere('TripTest', '=', 'Trip Ended')->where('Truck', '=', $rows->Truck)->orderBy('Date')->orderBy('Time')->count();
         //$trucks =  DB::connection('mysql')->table('baseline')->whereBetween('Date', [$startDateTime, $endDateTime])->where('TripTest', '=', 'Trip Start')->orWhere('TripTest', '=', 'Trip Ended')->where('Truck', '=', $rows->Truck)->orderBy('Date')->orderBy('Time')->skip(1)->take($count - 1)->get();
    
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
       $truckData =  DB::connection('mysql')->table('newbase')->where('Truck' ,'!=', 'SL227 KST809MP')->where('Truck' ,'!=', 'SL228 KST826MP')
       ->where('Truck' ,'!=', 'SL229 KST828MP')->where('Truck' ,'!=', 'SL230 KST819MP')->where('Truck' ,'!=', 'SL231 KST802MP')
       ->where('Truck' ,'!=', 'SL232 KST837MP')->where('Truck' ,'!=', 'SL233 KST838MP')->where('Truck' ,'!=', 'SL234 KST831MP')->where('Truck' ,'!=', 'SL235 KST829MP')
       ->groupby('Truck')->orderBy('id')->get();
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

        // if($truckrows == 318){

        //   dd($trip,$endtrip,$truck,$start_timestamp,$end_timestamp,$data1,$data2);
        // }

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


             
    /**
     * Remove the specified resource from storage.
     */
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


    public function powerBibaseline(){

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


    //    Trip Start

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
  

    //Time Calculation

        //     if($trip->TripClassification != 'Offloading Time' && $nextTrip->TripClassification  == 'Offloading Time'){


        //       $tripUpdat =  DB::connection('mysql')->table('baselinetest')->where('id', '=', $nextTrip->id )->update([

        //         'CumulativeTripClassification' => $nextTrip->TimeDifferenceMins

        //       ]);

        //     }

          



      }

    }

    Log::info('Finished trip analysis on', ['Truck' => $rows->Truck,  '#' => $truckCode]);
    dd('done');


    }





    public function updateTimedIFF(){

      ini_set('max_execution_time', 3600000000000); // 3600 seconds = 60 minutes
      set_time_limit(360000000000);
     
      $truckData = DB::connection('mysql')->table('baselinetest')->groupBy('Truck')->orderBy('id')->get();
     // dd($truckData);

       foreach ($truckData as $truckCode => $rows) {
        
        Log::info('started trip analysis on', ['Truck' => $rows->Truck,  '#' => $truckCode]);
     
       $trucks =  DB::connection('mysql')->table('baselinetest')->where('Truck', '=', $rows->Truck)->orderBy('DateUpdated')->orderBy('Time')->get();
   //   dd($trucks);

      foreach ($trucks as $truckrows =>$trip) {
          
        $base =  DB::connection('mysql')->table('baseline')->where('id', '=', $trip->BaseId)->first();

       $trucks =  DB::connection('mysql')->table('baselinetest')->where('id', '=', $trip->id)->update([

        'TimeDifference' => $base->TimeDifference

       ]);

      }


    }

    Log::info('Finished trip analysis on', ['Truck' => $rows->Truck,  '#' => $truckCode]);
    dd('done');


    }
}
