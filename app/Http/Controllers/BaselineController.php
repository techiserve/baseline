<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use Illuminate\Support\Facades\Cache;
use DateTime;
use GuzzleHttp\Client;
use DateInterval;
use Illuminate\Support\Carbon;
use App\Imports\BaselineImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Log;

class BaselineController extends Controller
{




 //run complete baseline
  public function RunBaseline()
  {
    dd('done..');

      $this->timeDifference();
      $this->LongDifference();
      $this->LatDifference();
     $this->CoordinateTest();
     $this->movingStationary();
     $this->Count();
     $this->OnTheRoad();
      $this->TripStart();
     $this->tripEnd();
     $this->TripTest();
      $this->TripTestUpdated();
    // $this->cycleTime();
    //  $this->geofence();

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
      // $this->FbCartrack();
      // $this->loadCapacity();
      // $this->RouteClassification();
      // $this->TimeSpentPercentage();
      // $this->TripTimeTruck();
   
   

  }


  //run powerbi baseline
  public function PowerBiRunBaseline()
  {


     $this->TimeDifferenceMins();
     $this->GeofenceWithRBayClass();
     $this->GFupdated11();
    $this->TripClassificationV3();
    $this->TripTimeRoutev2();
  

  }


  public function BaselineV2()
  {
      
   $this->geofence();
   $this->Route();
   $this->GeofenceWithRBayClass();
   $this->GFupdated11();
    $this->Stops();
   $this->TripClassificationV3();
   $this->TripClassificationV3Updated();
    $this->Deadruns();
    $this->TripClassificationV7();
    $this->TripClassificationV7loading();
    $this->TripTimeRoutev2();
    $this->TripTimeRoutev2Deadruns();
  //  $this->TripID();
    $this->loadCapacity();
    $this->TripSummary();
  
  }


///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

//First Baseline

     //calculates differences between consecutive longitudes
      public function LongDifference()
    {

        ini_set('max_execution_time', 3600000000); // 3600 seconds = 60 minutes
        set_time_limit(3600000000);
           
         $truckData = DB::connection('mysql')->table('baselinev2')->whereBetween('Date', ['2024-05-01' , '2024-05-31'])->groupBy('Truck')->orderBy('id')->get();

         foreach ($truckData as $truckCode => $rows) {

          Log::info('Started Longitude Difference on', ['Truck' => $rows->Truck, '#' => $truckCode]);
          $startDate = '2024-05-01'; // Replace with your start date
          $endDate = '2024-05-31';   // Replace with your end date

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

       //calculates differences between consecutive latitude
    public function LatDifference()
    {
        ini_set('max_execution_time', 36000000); // 3600 seconds = 60 minutes
        set_time_limit(360000000);

          $truckData = DB::connection('mysql')->table('baselinev2')->whereBetween('Date', ['2024-05-01' , '2024-05-31'])->groupBy('Truck')->orderBy('id')->get();     // $truckData = $truckData->take(2);
        //   dd($truckData);

         foreach ($truckData as $truckCode => $rows) {

          Log::info('Started Latitude Difference on', ['Truck' => $rows->Truck, '#' => $truckCode]);
          $startDate = '2024-05-01'; // Replace with your start date
        $endDate = '2024-05-31';  // Replace with your end date

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

          //prints 1 or 0 if latdiff or longdiff is less than 0.0001
    public function CoordinateTest()
    {
        ini_set('max_execution_time', 360000000); // 3600 seconds = 60 minutes
        set_time_limit(36000000);

      
          $truckData = DB::connection('mysql')->table('baselinev2')->whereBetween('Date', ['2024-05-01' , '2024-05-31'])->groupBy('Truck')->orderBy('id')->get();     // $truckData = $truckData->take(2);
        //   dd($truckData);

         foreach ($truckData as $truckCode => $rows) {

          Log::info('Started coordinate test on', ['Truck' => $rows->Truck, '#' => $truckCode]);
          $startDate = '2024-05-01'; // Replace with your start date
          $endDate = '2024-05-31';  // Replace with your end date
         
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

    

        Log::info('Finished Coordinate Test on', ['Truck' => $rows->Truck,  '#' => $truckCode]);

       }
   
    }
 
    //counts whenever there is a consecutive value of 1 in previous columnss
     public function Count(){

        ini_set('max_execution_time', 360000000); // 3600 seconds = 60 minutes
        set_time_limit(360000000);

          $truckData = DB::connection('mysql')->table('baselinev2')->whereBetween('Date', ['2024-05-01' , '2024-05-31'])->groupBy('Truck')->orderBy('id')->get();     // $truckData = $truckData->take(2);
        //   dd($truckData);

         foreach ($truckData as $truckCode => $rows) {

          Log::info('Started count on', ['Truck' => $rows->Truck, '#' => $truckCode]);
          $startDate = '2024-05-01'; // Replace with your start date
        $endDate = '2024-05-31';  // Replace with your end date
          
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

   

       Log::info('Finished Count on', ['Truck' => $rows->Truck,  '#' => $truckCode]);
    
      }

  
     }


   public function CumulativeTime()
   {

      ini_set('max_execution_time', 360000000); // 3600 seconds = 60 minutes
      set_time_limit(360000000);
 
        $truckData = DB::connection('mysql')->table('baselinev2')->whereBetween('Date', ['2024-05-01' , '2024-05-31'])->groupBy('Truck')->orderBy('id')->get();   // $truckData = $truckData->take(2);
      //   dd($truckData);

       foreach ($truckData as $truckCode => $rows) {

        Log::info('Started cummulative count on', ['Truck' => $rows->Truck, '#' => $truckCode]);
        $startDate = '2024-05-01'; // Replace with your start date
        $endDate = '2024-05-31'; // Replace with your end date

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

   //prints on the road if count is greater than 17 and movingstationary field says moving
   public function OnTheRoad()
   {
            //On The Road COLUMN
            ini_set('max_execution_time', 360000000); // 3600 seconds = 60 minutes
            set_time_limit(3600000000);
    
              $truckData = DB::connection('mysql')->table('baselinev2')->whereBetween('Date', ['2024-05-01' , '2024-05-31'])->groupBy('Truck')->orderBy('id')->get();         // $truckData = $truckData->take(2);
            //   dd($truckData);
    
             foreach ($truckData as $truckCode => $rows) {
    
              Log::info('Started on the road on', ['Truck' => $rows->Truck, '#' => $truckCode]);
              $startDate = '2024-05-01'; // Replace with your start date
              $endDate = '2024-05-31'; // Replace with your end date
             
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

      

          Log::info('Finished ontheroad on', ['Truck' => $rows->Truck,  '#' => $truckCode]);

       }

   
     }


     //prints trip start if there is an on the road in column ontheroad then if the next row has false
     public function TripStart(){
      
      ini_set('max_execution_time', 3600000000); // 3600 seconds = 60 minutes
      set_time_limit(3600000000);

     
        $truckData = DB::connection('mysql')->table('baselinev2')->whereBetween('Date', ['2024-05-01' , '2024-05-31'])->groupBy('Truck')->orderBy('id')->get();   // $truckData = $truckData->take(2);
      //   dd($truckData);

       foreach ($truckData as $truckCode => $rows) {

        Log::info('Started trip start on', ['Truck' => $rows->Truck, '#' => $truckCode]);
        $startDate = '2024-05-01'; // Replace with your start date
        $endDate = '2024-05-31';  // Replace with your end date

        // Convert to DateTime objects
        $startDateTime = new DateTime($startDate);
        $endDateTime = new DateTime($endDate);
   
        $count =  DB::connection('mysql')->table('baselinev2')->whereBetween('Date', [$startDateTime, $endDateTime])->where('Truck', '=', $rows->Truck)->orderBy('Date')->orderBy('Time')->count();
         // dd($count);
        if($count > 0){
       $trucks =  DB::connection('mysql')->table('baselinev2')->whereBetween('Date', [$startDateTime, $endDateTime])->where('Truck', '=', $rows->Truck)->orderBy('Date')->orderBy('Time')->skip(1)->take($count - 1)->get();
       $prevTruck =  DB::connection('mysql')->table('baselinev2')->whereBetween('Date', [$startDateTime, $endDateTime])->where('Truck', '=', $rows->Truck)->orderBy('Date')->orderBy('Time')->first();
        ////   dd($trucks);
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

     // combines trip end and trip start 
     public function TripTest(){

        ini_set('max_execution_time', 3600000000); // 3600 seconds = 60 minutes
        set_time_limit(360000000);

          $truckData = DB::connection('mysql')->table('baselinev2')->whereBetween('Date', ['2024-05-01' , '2024-05-31'])->groupBy('Truck')->orderBy('id')->get();     

         foreach ($truckData as $truckCode => $rows) {

          Log::info('Started trip test on', ['Truck' => $rows->Truck, '#' => $truckCode]);
          $startDate = '2024-05-01'; // Replace with your start date
          $endDate = '2024-05-31';  // Replace with your end date
  
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

     // combines trip end and trip start if there is a difference of less than 5 minutes
        public function TripTestUpdated()        
        {

            ini_set('max_execution_time', 3600000000); // 3600 seconds = 60 minutes
            set_time_limit(3600000000000);
           
            
              $truckData = DB::connection('mysql')->table('baselinev2')->whereBetween('Date', ['2024-05-01' , '2024-05-31'])->groupBy('Truck')->orderBy('id')->get();         // $truckData = $truckData->take(2);
            //   dd($truckData);
    
             foreach ($truckData as $truckCode => $rows) {
    
              Log::info('Started trip test updated on', ['Truck' => $rows->Truck, '#' => $truckCode]);
              $startDate = '2024-05-01'; // Replace with your start date
        $endDate = '2024-05-31';  // Replace with your end date
  
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

   // sort the Date and Time
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
  
   // orints trip end if there is on the road followed by false on column on the road
    public function tripEnd()
    {
        ini_set('max_execution_time', 36000000000); // 3600 seconds = 60 minutes
        set_time_limit(36000000000);
       // dd('testing');
       
         $truckData = DB::connection('mysql')->table('baselinev2')->whereBetween('Date', ['2024-05-01' , '2024-05-31'])->groupBy('Truck')->orderBy('id')->get();    // $truckData = $truckData->take(2);
       //   dd($truckData);

        foreach ($truckData as $truckCode => $rows) {

         Log::info('Started trip end  on', ['Truck' => $rows->Truck, '#' => $truckCode]);
         $startDate = '2024-05-01'; // Replace with your start date
         $endDate = '2024-05-31';  // Replace with your end date

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

   //calculates if a given co ordinate is inside a geofence
    public function geofence()
    {
        ini_set('max_execution_time', 3600000000000); // 3600 seconds = 60 minutes
        set_time_limit(3600000000000);
    
          $truckData = DB::connection('mysql')->table('baselinetest')->groupBy('Truck')->orderBy('id')->get();     // $truckData = $truckData->take(2);
          // dd($truckData);

         foreach ($truckData as $truckCode => $rows) {

          Log::info('Started geofence on', ['Truck' => $rows->Truck, '#' => $truckCode]);
          $startDate = '2024-04-01'; // Replace with your start date
        $endDate = '2024-04-30'; // Replace with your end date
  
          // Convert to DateTime objects
          $startDateTime = new DateTime($startDate);
          $endDateTime = new DateTime($endDate);
     
     
         $trucks =  DB::connection('mysql')->table('baselinetest')->where('Truck', '=', $rows->Truck)->orderBy('DateUpdated')->orderBy('Time')->get();  
        
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
      

        if($geofence->GeofenceCategory == 'Small'){
   
          $radius = 2251;

        }elseif($geofence->GeofenceCategory == 'Large'){

           $radius = 5001;

        }elseif($geofence->GeofenceCategory == 'Xsmall'){

          $radius = 750;

       }else{

          $radius = 3001;

        }

        if($shortestDistance < $radius){
         
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

       if($geofence->GeofenceCategory == 'Small'){
   
        $radius = 2251;

      }elseif($geofence->GeofenceCategory == 'Large'){

         $radius = 5001;

      }elseif($geofence->GeofenceCategory == 'Xsmall'){

        $radius = 750;

     }else{

        $radius = 3001;

      }

     // dd($radius);

        if($distance < $radius){

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

       // dd($distance, $trip->id,$location);
       }

      }

     }
     Log::info('Finished geofence on', ['Truck' => $rows->Truck,  '#' => $truckCode]);

     }

     Log::info('Baseline Finished', ['Truck' => 'All']);
     //dd("Finally done");
      // die("Execution stopped.");
 
 



    }
  


    public function Stops()
    {
        ini_set('max_execution_time', 3600000000000); // 3600 seconds = 60 minutes
        set_time_limit(3600000000000);
    
          $truckData = DB::connection('mysql')->table('baselinetest')->groupBy('Truck')->orderBy('id')->get();     // $truckData = $truckData->take(2);
       
         foreach ($truckData as $truckCode => $rows) {

          Log::info('Started stops on', ['Truck' => $rows->Truck, '#' => $truckCode]);

          $startDate = '2024-04-01'; // Replace with your start date
          $endDate = '2024-04-30'; // Replace with your end date
  
            // Convert to DateTime objects
            $startDateTime = new DateTime($startDate);
            $endDateTime = new DateTime($endDate);

         $trucks =  DB::connection('mysql')->table('baselinetest')->where('Truck', '=', $rows->Truck)->orderBy('DateUpdated')->orderBy('Time')->get();
        
        foreach ($trucks as  $truckrows => $trip) {
        
          $loading = DB::connection('mysql')->table('routes')->where('LoadingPoint', '=', $trip->GFupdated1)->first();
          $offloading = DB::connection('mysql')->table('routes')->where('OffloadingPoint', '=', $trip->GFupdated1)->first();
  
           if($loading == null && $offloading == null && $trip->GFupdated1 != 'Witbank yard' &&  $trip->GFupdated1 == 'Outside Geofence'){
   
            $lat = $trip->Latitude;
            $lng = $trip->Longitude;

            $geofences = DB::connection('mysql')->table('stops')->where('id', '>', 0)->get();
            
            foreach($geofences as $geofence){

            if($geofence->Shape == 'Polygon'){
      
           $otherPoints = [
            ['latitude' => $geofence->LowLat, 'longitude' => $geofence->LowLong],
            ['latitude' => $geofence->LowLat, 'longitude' => $geofence->HighLong],
            ['latitude' => $geofence->HighLat, 'longitude' => $geofence->LowLong],
            ['latitude' => $geofence->HighLat, 'longitude' => $geofence->HighLong],
            ];

         $testPoint = ['latitude' =>  $lat, 'longitude' => $lng];

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

      //   dd($shortestDistance, $trip->id,$geofence->id);

        if($shortestDistance < 10) {
          // dd($trip,$geofence->ZoneName);
            $location = $geofence->ZoneName;
            $tripUpdate = DB::connection('mysql')->table('baselinetest')->where('id', '=', $trip->id)->update([

                'GFupdated1' => $location
        
               ]); 

            break;

         }
          
       }

      }

      }
     //dd('Done');
     }

     Log::info('Finished stops on', ['Truck' => $rows->Truck,  '#' => $truckCode]);

     }

     Log::info('Stops Finished', ['Truck' => 'All']);
      //  dd("Finally done");

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

     //calculates difference between consecutive time stamps
    public function timeDifference()
    {

        ini_set('max_execution_time', 3600000000000); // 3600 seconds = 60 minutes
        set_time_limit(360000000000);

           $truckData = DB::connection('mysql')->table('baselinev2')->whereBetween('Date', ['2024-05-01' , '2024-05-31'])->groupBy('Truck')->orderBy('id')->get();
          dd($truckData); 

         foreach ($truckData as $truckCode => $rows) {

          Log::info('Started Time Difference on', ['Truck' => $rows->Truck,  '#' => $truckCode]);
          $startDate = '2024-05-01'; // Replace with your start date
        $endDate = '2024-05-31';  // Replace with your end date

          // Convert to DateTime objects
          $startDateTime = new DateTime($startDate);
          $endDateTime = new DateTime($endDate);
     
         $trucks =  DB::connection('mysql')->table('baselinev2')->where('Truck', '=', $rows->Truck)->orderBy('Date')->orderBy('Time')->get();
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

    //calculates cycle time and evene duration between each trip start and trip end
    public function cycleTime()
    {
        
        ini_set('max_execution_time', 360000000000); // 3600 seconds = 60 minutes
        set_time_limit(360000000000);

          $truckData = DB::connection('mysql')->table('baselinev2')->whereBetween('Date', ['2024-05-01' , '2024-05-31'])->groupBy('Truck')->orderBy('id')->get();     // $truckData = $truckData->take(2);
        //   dd($truckData);

         foreach ($truckData as $truckCode => $rows) {

          Log::info('Started cycle time on', ['Truck' => $rows->Truck, '#' => $truckCode]);
          $startDate = '2024-05-01'; // Replace with your start date
          $endDate = '2024-05-31';  // Replace with your end date
  
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

    //prints moving if co ordinate test is 0 or if coordinatetest is 1 and if both highspeed,distance is less than 0 
    public function movingStationary()
    {
        ini_set('max_execution_time', 3600000000000); // 3600 seconds = 60 minutes
        set_time_limit(360000000000);
       
          $truckData = DB::connection('mysql')->table('baselinev2')->whereBetween('Date', ['2024-05-01' , '2024-05-31'])->groupBy('Truck')->orderBy('id')->get();     // $truckData = $truckData->take(2);
        //   dd($truckData);

         foreach ($truckData as $truckCode => $rows) {

          Log::info('Started movingstationary on', ['Truck' => $rows->Truck, '#' => $truckCode]);
          $startDate = '2024-05-01'; // Replace with your start date
          $endDate = '2024-05-31';  // Replace with your end date
          // Convert to DateTime objects
         
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

    

        Log::info('Finished movingStationary on', ['Truck' => $rows->Truck,  '#' => $truckCode]);

      }

    }


    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


 
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //Second Baseline (Power BI logic converted to SQL)
    //prints route from geofence field
    public function Route()
    {

        ini_set('max_execution_time', 3600000000); // 3600 seconds = 60 minutes
        set_time_limit(3600000000);
           
        $truckData = DB::connection('mysql')->table('baselinetest')->groupBy('Truck')->orderBy('id')->get();
        // $truckData = $truckData->take(2);
        //   dd($truckData);

         foreach ($truckData as $truckCode => $rows) {

          Log::info('Started route on', ['Truck' => $rows->Truck, '#' => $truckCode]);
          // Replace with your end date


         $trucks =  DB::connection('mysql')->table('baselinetest')->where('Truck', '=', $rows->Truck)->orderBy('DateUpdated')->orderBy('Time')->get();
        // dd($trucks);
        foreach ($trucks as  $truckrows => $trip) {
        
         $currentTrip = DB::connection('mysql')->table('baselinetest')->where('id', '=', $trip->id)->first(); 

         if($truckrows  >= 1){

          $nextIndex = $truckrows - 1;
               
         $previousTrip = DB::connection('mysql')->table('baselinetest')->where('id', '=',  $trucks[$nextIndex]->id)->first();          
       
    
        //  dd(number_format($interval,));
         $tripUpdate = DB::connection('mysql')->table('baselinetest')->where('id', '=', $trip->id)->update([

            'Route' => $previousTrip->Geofence .' to '. $trip->Geofence
         ]); 

    

      }


        //first row
        $temprev =  DB::connection('mysql')->table('baselinetest')->where('id', '=', $trip->id - 1)->count(); 

        if($temprev){

         $temprevget =  DB::connection('mysql')->table('baselinetest')->where('id', '=', $trip->id - 1)->first(); 

         if($temprevget->Truck != $trip->Truck){
             
           $tripUpdate = DB::connection('mysql')->table('baselinetest')->where('id', '=', $trip->id)->update([

             'Route' =>  $trip->Geofence
          ]); 
          
         }

        }
         

       }

        Log::info('Finished route on', ['Truck' => $rows->Truck, '#' => $truckCode]);

      }

      //  dd('Done');

   
    }
     //calculates time difference in mins
    public function TimeDifferenceMins()
    {

        ini_set('max_execution_time', 3600000000); // 3600 seconds = 60 minutes
        set_time_limit(3600000000);
           
        $truckData = DB::connection('mysql')->table('baselinetest')->groupBy('Truck')->orderBy('id')->get();
        // $truckData = $truckData->take(2);
 
         foreach ($truckData as $truckCode => $rows) {

          Log::info('Started timediff on', ['Truck' => $rows->Truck, '#' => $truckCode]);
          // Replace with your end date
       
         $trucks =  DB::connection('mysql')->table('baselinetest')->where('Truck', '=', $rows->Truck)->orderBy('DateUpdated')->orderBy('Time')->get();
        // dd($trucks);
        foreach ($trucks as  $truckrows => $trip) {
        
         $currentTrip = DB::connection('mysql')->table('baselinetest')->where('id', '=', $trip->id)->first(); 

         if($truckrows  >= 1 && $trip->id == 105822){

          $nextIndex = $truckrows - 1;
        
         $previousTrip = DB::connection('mysql')->table('baselinetest')->where('id', '=',  $trucks[$nextIndex]->id)->first();          
       //   dd($trip->DateUpdated.' '.$currentTrip->Time);
         $interval =  date_diff(date_create( $trip->DateUpdated.' '.$currentTrip->Time),date_create($previousTrip->DateUpdated.' '.$previousTrip->Time)); 
         $totalSeconds = $interval->s + $interval->i * 60 + $interval->h * 3600;
         $timeDiff = $totalSeconds/60;
       //  dd($timeDiff/60);
         $tripUpdate = DB::connection('mysql')->table('baselinetest')->where('id', '=', $trip->id)->update([

            'TimeDifferenceMins' => $timeDiff,
            'EventTime' => abs($timeDiff/60),
            'EventDurationHrs' => $timeDiff/60
         ]); 

      }


        //first row
        $temprev =  DB::connection('mysql')->table('baselinetest')->where('id', '=', $trip->id - 1)->count(); 

        if($temprev){

         $temprevget =  DB::connection('mysql')->table('baselinetest')->where('id', '=', $trip->id - 1)->first(); 

         if($temprevget->Truck != $trip->Truck){
             
           $tripUpdate = DB::connection('mysql')->table('baselinetest')->where('id', '=', $trip->id)->update([

            'TimeDifferenceMins' => 0,
            'EventTime' => 0,
            'EventDurationHrs' => 0
          ]); 
          
         }

        }
         

      }

        Log::info('Finished route on', ['Truck' => $rows->Truck, '#' => $truckCode]);

      }
      dd('done');
    
   
    }

     //prints geofence if geofence column is outside geofence
    public function GeofenceWithRBayClass()
    {

        ini_set('max_execution_time', 3600000000); // 3600 seconds = 60 minutes
        set_time_limit(3600000000);
           
        $truckData = DB::connection('mysql')->table('baselinetest')->groupBy('Truck')->orderBy('id')->get();
        // $truckData = $truckData->take(2);
        //   dd($truckData);

         foreach ($truckData as $truckCode => $rows) {

          Log::info('Started geo on', ['Truck' => $rows->Truck, '#' => $truckCode]);
          // Replace with your end date
       
          $startDate = '2024-04-01'; // Replace with your start date
          $endDate = '2024-04-30'; // Replace with your end date
  
            // Convert to DateTime objects
            $startDateTime = new DateTime($startDate);
            $endDateTime = new DateTime($endDate);

         $trucks =  DB::connection('mysql')->table('baselinetest')->where('Truck', '=', $rows->Truck)->orderBy('DateUpdated')->orderBy('Time')->get();
        // dd($trucks);
        foreach ($trucks as  $truckrows => $trip) {
        
          $geofences =  DB::connection('mysql')->table('powerbigeofences')->get();
     
          foreach($geofences as $geofence){

            if($trip->Latitude >= $geofence->Latitudelow && $trip->Latitude <= $geofence->Latitudehigh && $trip->Longitude >= $geofence->Longitudelow && $trip->Longitude <= $geofence->Longitudehigh){
               
              $tripUpdate = DB::connection('mysql')->table('baselinetest')->where('id', '=', $trip->id)->update([
  
                'GeofenceClassNewWithRBayClass' => $geofence->geofence,
        
             ]); 
    
            }

          }

        }

        Log::info('Finished route on', ['Truck' => $rows->Truck, '#' => $truckCode]);

      }

   
   
    }

    //create the route from one geofence to another
    public function GFupdated11()
    {

        ini_set('max_execution_time', 3600000000); // 3600 seconds = 60 minutes
        set_time_limit(3600000000);
           
        $truckData = DB::connection('mysql')->table('baselinetest')->groupBy('Truck')->orderBy('id')->get();
  
         foreach ($truckData as $truckCode => $rows) {

          Log::info('Started GFupdated11 on', ['Truck' => $rows->Truck, '#' => $truckCode]);
     
         $trucks =  DB::connection('mysql')->table('baselinetest')->where('Truck', '=', $rows->Truck)->orderBy('DateUpdated')->orderBy('Time')->get();
        // dd($trucks);
        foreach ($trucks as  $truckrows => $trip) {
        
          if($trip->Geofence == null || $trip->Geofence == 'Outside Geofence' &&  $trip->GeofenceClassNewWithRBayClass != null){

            $tripUpdate = DB::connection('mysql')->table('baselinetest')->where('id', '=', $trip->id)->update([
  
              'GFupdated1' => $trip->GeofenceClassNewWithRBayClass,
      
           ]); 
  
          }else{

            $tripUpdate = DB::connection('mysql')->table('baselinetest')->where('id', '=', $trip->id)->update([
  
              'GFupdated1' => $trip->Geofence,
      
           ]); 
          }

        }

        Log::info('Finished route on', ['Truck' => $rows->Truck, '#' => $truckCode]);

      }

  
    }

     // trip route to richardsbay trips
    public function GFNew11()
    {

        ini_set('max_execution_time', 3600000000); // 3600 seconds = 60 minutes
        set_time_limit(3600000000);
           
        $truckData = DB::connection('mysql')->table('baselinetest')->groupBy('Truck')->orderBy('id')->get();
        // $truckData = $truckData->take(2);
        //   dd($truckData);

         foreach ($truckData as $truckCode => $rows) {

          Log::info('Started GFnew11 on', ['Truck' => $rows->Truck, '#' => $truckCode]);
          // Replace with your end date
       
         $trucks =  DB::connection('mysql')->table('baselinetest')->where('Truck', '=', $rows->Truck)->orderBy('DateUpdated')->orderBy('Time')->get();
        // dd($trucks);
        foreach ($trucks as  $truckrows => $trip) {
        
         $currentTrip = DB::connection('mysql')->table('baselinetest')->where('id', '=', $trip->id)->first(); 

         if($truckrows  >= 1){

          $nextIndex = $truckrows - 1;
               
         $previousTrip = DB::connection('mysql')->table('baselinetest')->where('id', '=',  $trucks[$nextIndex]->id)->first();          
       
         if($trip->GFupdated1 == 'Richards Bay Route'){

          $tripUpdate = DB::connection('mysql')->table('baselinetest')->where('id', '=', $trip->id)->update([

            'GFnew' =>  'Richards Bay Route'
         ]); 

         }else{

          $tripUpdate = DB::connection('mysql')->table('baselinetest')->where('id', '=', $trip->id)->update([

            'GFnew' => $previousTrip->GFupdated1 .' to '. $trip->GFupdated1
         ]); 

         }
    
       
    

      }


        //first row
        $temprev =  DB::connection('mysql')->table('baselinetest')->where('id', '=', $trip->id - 1)->count(); 

        if($temprev){

         $temprevget =  DB::connection('mysql')->table('baselinetest')->where('id', '=', $trip->id - 1)->first(); 

         if($temprevget->Truck != $trip->Truck){

          if($trip->GFupdated1 == 'Richards Bay Route'){
        
           $tripUpdate = DB::connection('mysql')->table('baselinetest')->where('id', '=', $trip->id)->update([

             'GFnew' =>  'Richards Bay Route'
          ]); 

        }else{

          $tripUpdate = DB::connection('mysql')->table('baselinetest')->where('id', '=', $trip->id)->update([

            'GFnew' =>  $trip->GFupdated1
         ]); 

        }
          
         }

        }
         

       }

        Log::info('Finished route on', ['Truck' => $rows->Truck, '#' => $truckCode]);

      }

     dd('Done....');
   
    }

    //add classification to the columns classification based off GFNew11
    public function Classification11()
    {

        ini_set('max_execution_time', 3600000000); // 3600 seconds = 60 minutes
        set_time_limit(3600000000);
           
        $truckData = DB::connection('mysql')->table('baselinetest')->where('Truck', '=','SL271 KTW865MP')->groupBy('Truck')->orderBy('id')->get();
      

         foreach ($truckData as $truckCode => $rows) {

          Log::info('Started Classification on', ['Truck' => $rows->Truck, '#' => $truckCode]);
  
         $trucks =  DB::connection('mysql')->table('baselinetest')->where('Truck', '=', $rows->Truck)->orderBy('DateUpdated')->orderBy('Time')->get();
        
        foreach ($trucks as  $truckrows => $trip) {
        
          $geofences =  DB::connection('mysql')->table('powerbiclassification')->where('geofence','=', $trip->GFupdated1)->first();
  
          if($geofences){

            $tripUpdate = DB::connection('mysql')->table('baselinetest')->where('id', '=', $trip->id)->update([
  
              'Classification' => $geofences->activity,
      
           ]);

          }

        }
       // dd('done');

        Log::info('Finished route on', ['Truck' => $rows->Truck, '#' => $truckCode]);

      }

   
    }

     //check trip activity and updates corresponding activity
    public function ClassNew11()
    {

        ini_set('max_execution_time', 3600000000); // 3600 seconds = 60 minutes
        set_time_limit(3600000000);
           
        $truckData = DB::connection('mysql')->table('baselinetest')->groupBy('Truck')->orderBy('id')->get();
        // $truckData = $truckData->take(2);
        //   dd($truckData);

         foreach ($truckData as $truckCode => $rows) {

          Log::info('Started ClassNew11 on', ['Truck' => $rows->Truck, '#' => $truckCode]);
          // Replace with your end date
       
         $trucks =  DB::connection('mysql')->table('baselinetest')->where('Truck', '=', $rows->Truck)->orderBy('DateUpdated')->orderBy('Time')->get();
        // dd($trucks);
        foreach ($trucks as  $truckrows => $trip) {
        
         $currentTrip = DB::connection('mysql')->table('baselinetest')->where('id', '=', $trip->id)->first(); 

         if($truckrows  >= 1){

          $nextIndex = $truckrows - 1;
               
         $previousTrip = DB::connection('mysql')->table('baselinetest')->where('id', '=',  $trucks[$nextIndex]->id)->first();          
     
          $tripUpdate = DB::connection('mysql')->table('baselinetest')->where('id', '=', $trip->id)->update([

            'Classnew' => $previousTrip->Classification .' to '. $trip->Classification
         ]); 
  

      }


        //first row
        $temprev =  DB::connection('mysql')->table('baselinetest')->where('id', '=', $trip->id - 1)->count(); 

        if($temprev){

         $temprevget =  DB::connection('mysql')->table('baselinetest')->where('id', '=', $trip->id - 1)->first(); 

         if($temprevget->Truck != $trip->Truck){

           $tripUpdate = DB::connection('mysql')->table('baselinetest')->where('id', '=', $trip->id)->update([

             'Classnew' => $trip->Classification
          ]); 

          
         }

        }
         

       }

        Log::info('Finished route on', ['Truck' => $rows->Truck, '#' => $truckCode]);

      }

  
   
    }

      //final trip classification of the rows
    public function TripClassification()
    {

        ini_set('max_execution_time', 3600000000); // 3600 seconds = 60 minutes
        set_time_limit(3600000000);
           
        $truckData = DB::connection('mysql')->table('baselinetest')->groupBy('Truck')->orderBy('id')->get();
  
         foreach ($truckData as $truckCode => $rows) {

          Log::info('Started TripClassification on', ['Truck' => $rows->Truck, '#' => $truckCode]);
       
         $trucks =  DB::connection('mysql')->table('baselinetest')->where('Truck', '=', $rows->Truck)->orderBy('DateUpdated')->orderBy('Time')->get();
       //  $trucks =  DB::connection('mysql')->table('baselinetest')->where('id', '=', 1301)->get();

        foreach ($trucks as  $truckrows => $trip) {
         
           $normal =  DB::connection('mysql')->table('tripactivity')->where('activity','=', $trip->Classnew)->first();
        //  dd($normal,$trip);
           if($normal){

            $tripUpdate = DB::connection('mysql')->table('baselinetest')->where('id', '=', $trip->id)->update([

              'TripClassification' => $normal->description 
  
           ]); 

           }
         

        }

        Log::info('Finished route on', ['Truck' => $rows->Truck, '#' => $truckCode]);

      }

     // dd('done');
   
    }
     
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


    /////////////////////////////////// Fuel //////////////////////////////////////
 
    public function Dailyfuel()
    {    

        ini_set('max_execution_time', 360000000000); // 3600 seconds = 60 minutes
        set_time_limit(360000000000);

        $truckData = DB::connection('mysql')->table('baselinetest')->groupBy('Truck')->orderBy('id')->get();
   
        foreach ($truckData as $truckCode => $rows) {

        Log::info('Started daily fuel on', ['Truck' => $rows->Truck,  '#' => $truckCode]);
     
        $string = $rows->Truck;
        $substring = 'Workshop';
        $substring1 = 'Parked';

      if (strpos($string, $substring) !== false){

       $results = str_replace($substring, '', $string);
      //'The string contains the word "Workshop"'
      }elseif(strpos($string, $substring1) !== false){

       $results = str_replace($substring1, '', $string);

      }else{
       $results = $rows->Truck;
      }

       $truckMap =  DB::connection('mysql')->table('truckmap')->where('Make' ,'=', 'MAN')->where('Truck','=', $results )->count();

       if($truckMap > 0){

      $trucks = DB::connection('mysql')->table('baselinetest')->where('Truck', '=', $rows->Truck)->orderBy('DateUpdated')->orderBy('Time')->groupBy('DateUpdated')->get();
       // dd($trucks);
        foreach ($trucks as $truckrows => $trip) {

         Log::info('started fuel-sub on', ['Truck' => $trip->Truck, 'row #' => $trip->DateUpdated]);

         $truckDetail =  DB::connection('mysql')->table('truckmap')->where('Make' ,'=', 'MAN')->where('Truck','=', $results )->first();
  
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
 
 
         $time = '00:00:00';
         $start_timestamp = $trip->DateUpdated . ' ' .$time;
        
         $endtime = '23:59:59';
         $end_timestamp =  $trip->DateUpdated . ' ' .$endtime;
     
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
                

          $createTrip = DB::connection('mysql')->table('dailyfuel')->insert([
         
            'Day' => $trip->DateUpdated,
            'Truck' => $trip->Truck,
            'FuelUsed' =>  $data1->data->fuel_consumed,
            'Distance' => $data2->data->distance/1000,
            'TruckType' => $truckDetail->type,
            'TruckCategory' => $truckDetail->Make,
            'Consumption' => $data1->data->fuel_consumed/($data2->data->distance/1000),
 
          ]);

         }else{

          $createTrip = DB::connection('mysql')->table('dailyfuel')->insert([
         
            'Day' => $trip->DateUpdated,
            'Truck' => $trip->Truck,
            'FuelUsed' =>  $data1->data->fuel_consumed,
            'Distance' => $data2->data->distance/1000,
            'TruckType' => $truckDetail->type,
            'TruckCategory' => $truckDetail->Make,
            'Consumption' => 0,
 
          ]);
         }
   
       } 

        }

        Log::info('Finished daily fuel on', ['Truck' => $rows->Truck,  '#' => $truckCode]);

       } 
  
      }

      dd('done');
             
    }

    public function FleetPerfomance()
    {    

      $cacheKey = 'example_cache_key';

      // Forget the cached item
     Cache::forget($cacheKey);
   
        ini_set('max_execution_time', 360000000000); // 3600 seconds = 60 minutes
        set_time_limit(360000000000);

        $truckData = DB::connection('mysql')->table('tripsummary')->groupBy('Truck')->orderBy('id')->get();
       // dd($truckData);
        foreach ($truckData as $truckCode => $rows) {
          $cacheKeys = 'example_cache_key';
         Cache::forget($cacheKeys);
        Log::info('Started fleet perfomance on', ['Truck' => $rows->Truck,  '#' => $truckCode]);

        $trucks = DB::connection('mysql')->table('tripsummary')->where('Truck', '=', $rows->Truck)->orderBy('DateUpdated')->orderBy('Time')->groupBy('DateUpdated')->get();
       // dd($trucks);
        foreach ($trucks as $truckrows => $trip) {

         $daytripscount = DB::connection('mysql')->table('tripsummary')->where('Truck', '=', $trip->Truck)->where('DateUpdated', '=', $trip->DateUpdated)->where('LoadingTripClassificationv2', '=', 'Offloading Trip')->orderBy('Time')->count();    
         $daytrips = DB::connection('mysql')->table('tripsummary')->where('Truck', '=', $trip->Truck)->where('DateUpdated', '=', $trip->DateUpdated)->where('LoadingTripClassificationv2', '=', 'Offloading Trip')->orderBy('Time')->get();
         $onetrip = DB::connection('mysql')->table('tripsummary')->where('Truck', '=', $trip->Truck)->where('TruckType', '!=', null)->first();
         $fuel = DB::connection('mysql')->table('dailyfuel')->where('Truck', '=', $trip->Truck)->where('Day', '=', $trip->DateUpdated)->first();
          if($fuel == null){
            $fuelused = 0;
            $dailydistance = 0;
            $IdlingFuel = 0;
          }else{
            $fuelused = $fuel->FuelUsed;
            $dailydistance = $fuel->Distance;
            $IdlingFuel = $fuel->IdlingFuelUsed;
          }

          if($onetrip == null){
            $trucktype = 'N/A';
          }else{
            $trucktype = $onetrip->TruckType;
          }
       
         $revenue = 0;
         $labour = 0;
         $tonnesmoved = 0;
         $distance = 0;

         foreach($daytrips as $daytrip){

           if($daytrip->TonnesMoved != null){
          $rev = $daytrip->TonnesMoved*$daytrip->Rate;
          $revenue = $revenue+$rev;
          $tonnesmoved = $tonnesmoved + $daytrip->TonnesMoved;
           }
          $labour =  $labour + $daytrip->Labour;
          $distance =  $distance + $daytrip->Distance;

         }


         if($daytripscount ==  0){ 

         // $tripstartcount = DB::connection('mysql')->table('baselinetest')->where('Truck', '=', $trip->Truck)->where('DateUpdated', '=', $trip->DateUpdated)->where('TripClassificationv3', '=', 'Trip Start')->count();  
    
         $vehicle = $trip->Truck;

        
         $expirationTimeInSeconds = 3600; // 1 day
           $vehicle = $trip->Truck;
           $cacheKeya = 'cached_data_for_' . $vehicle;
         //  dd($vehicle);
         $cachedData = Cache::remember($cacheKeya, $expirationTimeInSeconds, function ()use ($vehicle) {
             return DB::connection('mysql')
                 ->table('baselinetest')
                 ->where('Truck','=', $vehicle)
                 ->get();
         });

       //  dd($cachedData);

            // Retrieve the cached table data
            $cachedData = Cache::get($cacheKeya);
           // dd($cachedData);
            // Example: Querying the cached data
            $filteredData = $cachedData->where('Truck', '=',$vehicle)
                           ->where('DateUpdated', $trip->DateUpdated)
                           ->where('TripClassificationv3', 'Trip Start')
                           ->count();

                           $filteredData1 = $cachedData->where('Truck', '=',$vehicle)
                           ->where('DateUpdated', $trip->DateUpdated)
                           ->where('LoadingTripClassification', 'Trip Start')
                           ->count();

                      
                           $filteredData2 = $cachedData->where('Truck', '=',$vehicle)
                           ->where('DateUpdated', $trip->DateUpdated)
                           ->where('LoadingTripClassification', 'Trip End, Trip Start')
                           ->count();

         
        // dd($filteredData);
          if($filteredData > 0 OR $filteredData1 > 0 OR $filteredData2 > 0){   
            
          $tripstart = 'Yes';

          }else{
           
          $tripstart = 'No';
          }

         }else{

          $tripstart = 'Yes';

         }


         if($tripstart == 'No'){
      //  dd($trip->Truck,$trip->DateUpdated);
          $singledata = $cachedData->where('Truck', '=', $trip->Truck)->where('DateUpdated', $trip->DateUpdated)->last()->id;
         // dd($singledata);
          $finalcheck = $cachedData->where('Truck', $trip->Truck)->where('id', '<',$singledata)->where('LoadingTripClassification','!=', null)->last();
          $finalcheck2 = $cachedData->where('Truck', $trip->Truck)->where('id', '<',$singledata)->where('TripClassificationv3','!=', null)->last();

         if($finalcheck == 'Trip Start' OR $finalcheck2 == 'Trip Start'){
                  
          $tripstart = 'Yes';

         };

         }

         $check = DB::connection('mysql')->table('fleetperfomance')->where('Truck', $trip->Truck)->where('Date', $trip->DateUpdated)->count();

         if($check > 0){

          $createTrip = DB::connection('mysql')->table('fleetperfomance')->where('Truck', $trip->Truck)->where('Date', $trip->DateUpdated)->update([
         
            'Date' => $trip->DateUpdated,
            'Truck' => $trip->Truck,
            'TotalFuelUsed' => $fuelused,
            'Trips' => $daytripscount,
            'TruckType' => $trucktype,
            'Revenue' =>  $revenue,
            'LabourExpense' => $labour ,
            'IdlingFuel' => $IdlingFuel,
            'TonnesMoved' =>  $tonnesmoved,
            'TripInProgress' =>  $tripstart,
            'Distance' => $distance,
            'Dailydistance' => $dailydistance,
  
          ]);

         }else{

         $createTrip = DB::connection('mysql')->table('fleetperfomance')->insert([
         
          'Date' => $trip->DateUpdated,
          'Truck' => $trip->Truck,
          'TotalFuelUsed' => $fuelused,
          'Trips' => $daytripscount,
          'TruckType' => $trucktype,
          'Revenue' =>  $revenue,
          'LabourExpense' => $labour ,
          'TonnesMoved' =>  $tonnesmoved,
          'TripInProgress' =>  $tripstart,
          'Distance' => $distance,
          'Dailydistance' => $dailydistance,

        ]);
      }

        }
        
        
         $cacheKeyss = 'example_cache_key';

         // Forget the cached item
        Cache::forget($cacheKeyss);
        Log::info('Finished fleet perfomance on', ['Truck' => $rows->Truck,  '#' => $truckCode]);
 
  
      }

      dd('done');
             
    }


    public function TripinProgressonFleetPerfomance()
    {    

        ini_set('max_execution_time', 360000000000); // 3600 seconds = 60 minutes
        set_time_limit(360000000000);

        $truckData = DB::connection('mysql')->table('tripsummary')->groupBy('Truck')->orderBy('id')->get();
       // dd($truckData);
        foreach ($truckData as $truckCode => $rows) {

        Log::info('Started fleet perfomance on', ['Truck' => $rows->Truck,  '#' => $truckCode]);

        $trucks = DB::connection('mysql')->table('tripsummary')->where('Truck', '=', $rows->Truck)->orderBy('DateUpdated')->orderBy('Time')->groupBy('DateUpdated')->get();
       // dd($trucks);
        foreach ($trucks as $truckrows => $trip) {

         $daytripscount = DB::connection('mysql')->table('tripsummary')->where('Truck', '=', $trip->Truck)->where('DateUpdated', '=', $trip->DateUpdated)->where('LoadingTripClassificationv2', '=', 'Offloading Trip')->orderBy('Time')->count();    
         $daytrips = DB::connection('mysql')->table('tripsummary')->where('Truck', '=', $trip->Truck)->where('DateUpdated', '=', $trip->DateUpdated)->where('LoadingTripClassificationv2', '=', 'Offloading Trip')->orderBy('Time')->get();
         $onetrip = DB::connection('mysql')->table('tripsummary')->where('Truck', '=', $trip->Truck)->where('TruckType', '!=', null)->first();
         $fuel = DB::connection('mysql')->table('dailyfuel')->where('Truck', '=', $trip->Truck)->where('Day', '=', $trip->DateUpdated)->first();
          if($fuel == null){
            $fuelused = 0;
          }else{
            $fuelused = $fuel->FuelUsed;
          }

          if($onetrip == null){

            $trucktype = 'N/A';
          }else{
            $trucktype = $onetrip->TruckType;
          }
        
         $revenue = 0;
         $labour = 0;
         $tonnesmoved = 0;
         $distance = 0;
         foreach($daytrips as $daytrip){
           if($daytrip->TonnesMoved != null){
          $rev = $daytrip->TonnesMoved*$daytrip->Rate;
          $revenue = $revenue+$rev;
          $tonnesmoved = $tonnesmoved + $daytrip->TonnesMoved;
           }
          $labour =  $labour + $daytrip->Labour;
          $distance =   $distance + $daytrip->Distance;

         }


         $createTrip = DB::connection('mysql')->table('fleetperfomance')->insert([
         
          'Date' => $trip->DateUpdated,
          'Truck' => $trip->Truck,
          'TotalFuelUsed' => $fuelused,
          'Trips' => $daytripscount,
          'TruckType' => $trucktype,
          'Revenue' =>  $revenue,
          'LabourExpense' => $labour ,
          'TonnesMoved' =>  $tonnesmoved,
          'Distance' => $distance 

        ]);

        }
          
        Log::info('Finished fleet perfomance on', ['Truck' => $rows->Truck,  '#' => $truckCode]);
  
      }

      dd('done');
             
    }
    /////////////////////////////////////////////////////////////////////////////


   ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    public function truckLogic()
    {    
      
      ini_set('max_execution_time', 3600000000000); // 3600 seconds = 60 minutes
      set_time_limit(360000000000);
     
      $truckData = DB::connection('mysql')->table('baselinev2')->whereBetween('Date', ['2024-01-01' , '2024-04-30'])->groupBy('Truck')->orderBy('id')->get();
      // $truckData = $truckData->take(2);
      //   dd($truckData);

       foreach ($truckData as $truckCode => $rows) {

        Log::info('Started truck logic on', ['Truck' => $rows->Truck, '#' => $truckCode]);
        $startDate = '2024-01-01'; // Replace with your start date
        $endDate = '2024-04-30';   // Replace with your end date

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
       
     dd('done');
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

    public function FuelClassification()
    {    
      
      ini_set('max_execution_time', 3600000000000); // 3600 seconds = 60 minutes
      set_time_limit(360000000000);

      $truckData = DB::connection('mysql')->table('baselinetest')->groupBy('Truck')->orderBy('id')->get();

      foreach ($truckData as $truckCode => $rows) {

        Log::info('Started fuel classification on', ['Truck' => $rows->Truck,  '#' => $truckCode]);

      $trucks = DB::connection('mysql')->table('baselinetest')->where('Truck', '=', $rows->Truck)->orderBy('DateUpdated')->orderBy('Time')->get();
     // $trucks = DB::connection('mysql')->table('baselinetest')->where('id', '=', 413)->orderBy('DateUpdated')->orderBy('Time')->get();

      foreach ($trucks as $truckrows => $trip) {
   

        if($trip->TripClassificationv7 == 'Offloading time' OR $trip->TripClassificationv7 == 'Loading time' OR $trip->TripClassificationv7 == 'Inexplicable loading/offloading time' OR $trip->TripClassificationv7 == 'At Depot (No Load)' OR $trip->TripClassificationv7 == 'At Depot (Loaded)' OR $trip->TripClassificationv7 == ' At Weighbridge' OR 
          $trip->TripClassificationv7 == ' At Towing' OR $trip->TripClassificationv7 == ' At theft' OR $trip->TripClassificationv7 == ' At Stop' OR $trip->TripClassificationv7 == ' At Rest' OR $trip->TripClassificationv7 == ' At Refuel' OR $trip->TripClassificationv7 == ' At Panel Beaters' OR 
          $trip->TripClassificationv7 == ' At Outside Geofence' OR $trip->TripClassificationv7 == ' At OffSite' OR $trip->TripClassificationv7 == ' At Food stop' OR $trip->TripClassificationv7 == ' At Depot' OR $trip->TripClassificationv7 == ' At Customs'){
           
            $tripUpdate = DB::connection('mysql')->table('baselinetest')->where('id', '=', $trip->id)->update([

              'FuelClassification' => 'Idling'
        
             ]); 
           
        }else{

          if($trip->TripClassificationv7 == null){

          }else{
          
            $tripUpdate = DB::connection('mysql')->table('baselinetest')->where('id', '=', $trip->id)->update([

              'FuelClassification' => 'Moving'
        
             ]);
          }
        }
      
      }
      Log::info('Finished fuel classification on', ['Truck' => $rows->Truck,  '#' => $truckCode]);
       }
     
      Log::info('finished transfer on', ['Truck' => $rows->Truck,  '#' => $truckCode]);
      dd('done');       
    }


    public function updateLong()
     {     
      
      ini_set('max_execution_time', 3600000000000); // 3600 seconds = 60 minutes
      set_time_limit(360000000000);

       $truckData =  DB::connection('mysql')->table('baselinetest')->groupby('Truck')->orderBy('id')->get();
       
       foreach ($truckData as $truckCode => $rows) {

        if($truckCode > 119){

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

      $trucks =  DB::connection('mysql')->table('baselinetest')->where('Truck' ,'=', $rows->Truck )->where('TripID' ,'!=', null)->orderby('DateUpdated')->orderBy('Time')->get();
     
      foreach ($trucks as $truckrows => $trip) {

      if($truckrows < 1147){

      Log::info('started fuel-sub on', ['Truck' => $trip->Truck, 'row #' => $truckrows]);
      $endtrip = DB::connection('mysql')->table('baselinetest')->where('id', '>', $trip->id)->where('Truck' ,'=', $rows->Truck)->first();
       
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

            'LineTotalFuelUsed' => $data1->data->fuel_consumed,
            'LineTotalDistance' => $data2->data->distance/1000,
            'LineTotalConsumption' => (($data2->data->distance/1000)/$data1->data->fuel_consumed)
          ]);

        }else{

          $endtrip = DB::connection('mysql')->table('baselinetest')->where('id', '=',  $endtrip->id)->update([

            'LineTotalFuelUsed' => $data1->data->fuel_consumed,
            'LineTotalDistance' => $data2->data->distance/1000,
            'LineTotalConsumption' => 0
          ]);
        }
  
       }
      }
      }

      }

      }

      Log::info('finished transfer on', ['Truck' => 'All']);
      }
      dd('done');

     }  
     
     
     public function CarTrackIdlingFuelOffloading(){
             
      ini_set('max_execution_time', 3600000000000); 
      set_time_limit(360000000000);

       $truckData =  DB::connection('mysql')->table('baselinetest')->groupby('Truck')->orderBy('id')->get();
       
       foreach ($truckData as $truckCode => $rows) {

        Log::info('started cartrack idling fuel on', ['Truck' => $rows->Truck, 'row #' => $truckCode]);

        $string = $rows->Truck;
        $substring = 'Workshop';
        $substring1 = 'Parked';

      if (strpos($string, $substring) !== false) {

       $results = str_replace($substring, '', $string);

      } elseif(strpos($string, $substring1) !== false) {

       $results = str_replace($substring1, '', $string);

      }else{

       $results = $rows->Truck;
      }

       $truckMap =  DB::connection('mysql')->table('truckmap')->where('Make' ,'=', 'MAN')->where('Truck','=', $results )->count();
       
      if($truckMap > 0){

      $trucks =  DB::connection('mysql')->table('baselinetest')->where('Truck' ,'=', $rows->Truck )->where('TripClassificationv3' ,'=', 'Trip Start')->orderby('DateUpdated')->orderBy('Time')->get();
     
      foreach ($trucks as $truckrows => $trip) {

      Log::info('started fuel-sub on', ['Truck' => $trip->Truck, 'row #' => $truckrows]);

      $endtrip = DB::connection('mysql')->table('baselinetest')->where('id', '>', $trip->id)->where('Truck' ,'=', $rows->Truck)->where('TripClassificationv3' ,'=', 'Trip End')->first();
 
       $interval =  DB::connection('mysql')->table('baselinetest')->whereBetween('id', [$trip->id, $endtrip->id])
       ->where('Truck', '=', $rows->Truck)
       ->where('FuelClassification', '=', 'Idling')
       ->sum('LineTotalFuelUsed');


       $updatefleet = DB::connection('mysql')->table('baselinetest')->where('id', '=', $endtrip->id )->update([
         
        'idlingFuelUsed' => $interval

      ]);
 
      
      }

    }

  
      Log::info('finished cartrack idling fuel on', ['Truck' => 'All']);
      }
      dd('done');


     }
 

     public function CarTrackIdlingFuelDeadruns(){
             
      ini_set('max_execution_time', 3600000000000); 
      set_time_limit(360000000000);

       $truckData =  DB::connection('mysql')->table('baselinetest')->groupby('Truck')->orderBy('id')->get();
       
       foreach ($truckData as $truckCode => $rows) {

        Log::info('started cartrack idling fuel on', ['Truck' => $rows->Truck, 'row #' => $truckCode]);

        $string = $rows->Truck;
        $substring = 'Workshop';
        $substring1 = 'Parked';

      if (strpos($string, $substring) !== false) {

       $results = str_replace($substring, '', $string);

      } elseif(strpos($string, $substring1) !== false) {

       $results = str_replace($substring1, '', $string);

      }else{

       $results = $rows->Truck;
      }

       $truckMap =  DB::connection('mysql')->table('truckmap')->where('Make' ,'=', 'MAN')->where('Truck','=', $results )->count();
       
      if($truckMap > 0){

        $trucks = DB::connection('mysql')->table('baselinetest')->where('Truck', '=', $rows->Truck)->where('LoadingTripClassification', '=', 'Trip Start')->orwhere('Truck', '=', $rows->Truck)->where('LoadingTripClassification', '=', 'Trip End, Trip Start')->orderBy('DateUpdated')->orderBy('Time')->get();
     
      foreach ($trucks as $truckrows => $trip) {

      Log::info('started fuel-sub on', ['Truck' => $trip->Truck, 'row #' => $truckrows]);

      $endtrip = DB::connection('mysql')->table('baselinetest')->where('id', '>', $trip->id )->where('Truck', '=', $rows->Truck)->where('LoadingTripClassification','=', 'Trip End')->orwhere('id', '>', $trip->id )->where('Truck', '=', $rows->Truck)->where('LoadingTripClassification','=', 'Trip End, Trip Start')->first(); 
      // dd($trip,$endtrip);
       $interval =  DB::connection('mysql')->table('baselinetest')->whereBetween('id', [$trip->id, $endtrip->id])
       ->where('Truck', '=', $rows->Truck)
       ->where('FuelClassification', '=', 'Idling')
       ->sum('LineTotalFuelUsed');


       $updatefleet = DB::connection('mysql')->table('baselinetest')->where('id', '=', $endtrip->id )->update([
         
        'idlingFuelUsed' => $interval

      ]);
 
      
       }

        }

  
      Log::info('finished cartrack idling fuel on', ['Truck' => 'All']);
      }
      dd('done');


     }

     
     public function CarTrackIdlingFuelDaily(){
             
      ini_set('max_execution_time', 3600000000000); 
      set_time_limit(360000000000);

       $truckData =  DB::connection('mysql')->table('baselinetest')->groupby('Truck')->orderBy('id')->get();
       
       foreach ($truckData as $truckCode => $rows) {

        $cacheKeys = 'example_cache_key';
        Cache::forget($cacheKeys);

        Log::info('started cartrack daily idling fuel on', ['Truck' => $rows->Truck, 'row #' => $truckCode]);

        $string = $rows->Truck;
        $substring = 'Workshop';
        $substring1 = 'Parked';

       if (strpos($string, $substring) !== false) {
         
       $results = str_replace($substring, '', $string);
          
       }elseif(strpos($string, $substring1) !== false) {
        
       $results = str_replace($substring1, '', $string);
        
       }else{
        
       $results = $rows->Truck;

       }

       $truckMap =  DB::connection('mysql')->table('truckmap')->where('Make' ,'=', 'MAN')->where('Truck','=', $results )->count();
       
      if($truckMap > 0){

        $expirationTimeInSeconds = 3600; // 1 day
        $vehicle = $rows->Truck;
        $cacheKeya = 'cached_data_for_' . $vehicle;
 
       $cachedData = Cache::remember($cacheKeya, $expirationTimeInSeconds, function ()use ($vehicle) {
          return DB::connection('mysql')
              ->table('baselinetest')
              ->where('Truck','=', $vehicle)
              ->get();
       });
 

       $cachedData = Cache::get($cacheKeya);

    
       $trucks = $cachedData->groupBy('DateUpdated');

       foreach ($trucks as $truckrows => $trip) {
        

        $interval = $cachedData->where('DateUpdated', $trip[0]->DateUpdated)
        ->where('FuelClassification', '=', 'Idling')
        ->sum('LineTotalFuelUsed');

       // dd($interval,$trip[0]);

        $updatefleet = DB::connection('mysql')->table('fleetperfomance')->where('Truck', '=', $trip[0]->Truck )->where('Date', $trip[0]->DateUpdated)->update([
          
          'IdlingFuel' => $interval

        ]);
 
      
        }

       }

      Log::info('finished cartrack daily idling fuel on', ['Truck' => 'All']);

      $cacheKeyss = 'example_cache_key';
      Cache::forget($cacheKeyss);

      }

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

    
        $trucks = DB::connection('mysql')->table('baselinetest')->where('Truck', '=', $rows->Truck)->orderBy('DateUpdated')->orderBy('Time')->groupBy('DateUpdated')->get();
   
        foreach ($trucks as  $truckrows => $trip) {

        Log::info('Started sub total fleet board soap on', ['Truck' => $rows->Truck,  '#' => $trip->DateUpdated]);

        $nextTrip = $trip;
        // dd($nextTrip);
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

      $time = '00:00:00';
      $start_timestamp = $trip->DateUpdated . ' ' .$time;
     
      $endtime = '23:59:59';
      $end_timestamp =  $nextTrip->DateUpdated . ' ' .$endtime ;

        //dd($fleettruck->VehicleID,$start_timestamp,$end_timestamp);
        
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
            'Cookie: JSESSIONID=0001f9EVVW_llxYL4vhe7BM77o8:prdwas03l3m3'
          ),
        ));

     $response = curl_exec($curl);

      curl_close($curl);
      $xml =  preg_replace("/(<\/?)(\w+):([^>]*>)/", "$1$2$3", $response);
      $xml = simplexml_load_string($xml);
      $json = json_encode($xml);
      $responseArray = json_decode($json,true);
     
     // dd($responseArray['soapenvBody']['p725getTripRecordResponse']['p725GetTripRecordResponse']['@attributes']['responseSize']);
      $checkResponse = $responseArray['soapenvBody']['p725getTripRecordResponse']['p725GetTripRecordResponse']['@attributes']['responseSize'];
     
      $resultsize = $responseArray['soapenvBody']['p725getTripRecordResponse']['p725GetTripRecordResponse']['@attributes']['resultSize'];
      
       if($checkResponse == 0 || $checkResponse == 1){
       
      }else{

      $fleettrips = $responseArray['soapenvBody']['p725getTripRecordResponse']['p725GetTripRecordResponse']['p725TripRecordReport'];
    
      foreach($fleettrips as $fleettrip){

        $timestamp =  $fleettrip['p725Start']['p725VehicleTimestamp'];
        list($date, $time) = explode(" ", $timestamp);

        $timestamp1 =  $fleettrip['p725End']['p725VehicleTimestamp'];
        list($date1, $time1) = explode(" ", $timestamp1);

        if (array_key_exists('p725Consumption', $fleettrip)) {
       
          $trucks =  DB::connection('mysql')->table('novconsumption')->insert([

            'Truck' => $trip->Truck,
            'DateUpdated' => $date,
            'DateUpdated1' => $date1,
            'Time' => $time,
            'Time1' => $time1,
            'StartMileage' => $fleettrip['p725Start']['p725Mileage'],
            'StartKilometers' => $fleettrip['p725Start']['p725Position']['p725KM'],
            'EndMileage' => $fleettrip['p725End']['p725Mileage'],
            'EndKilometers' => $fleettrip['p725End']['p725Position']['p725KM'],
            'State' => $fleettrip['p725TripRecordKind'],
            'consumption' => $fleettrip['p725Consumption'],
            'fuellevel' => $fleettrip['p725FuelLevel'],
            //'trip' => 'Start',
    
           ]);
  

        }else{

          $trucks =  DB::connection('mysql')->table('novconsumption')->insert([

            'Truck' => $trip->Truck,
            'DateUpdated' => $date,
            'DateUpdated1' => $date1,
            'Time' => $time,
            'Time1' => $time1,
            'StartMileage' => $fleettrip['p725Start']['p725Mileage'],
            'StartKilometers' => $fleettrip['p725Start']['p725Position']['p725KM'],
            'EndMileage' => $fleettrip['p725End']['p725Mileage'],
            'EndKilometers' => $fleettrip['p725End']['p725Position']['p725KM'],
            'State' => $fleettrip['p725TripRecordKind'],
            'consumption' => 0,
            'fuellevel' => $fleettrip['p725FuelLevel'],
            //'trip' => 'Start',
    
           ]);
  
  
        }
   
      }

      $getconsumption =  DB::connection('mysql')->table('novconsumption')->where('DateUpdated','=', $trip->DateUpdated)->where('Truck','=', $trip->Truck)->sum('consumption');
      $getidlingconsumption =  DB::connection('mysql')->table('novconsumption')->where('DateUpdated','=', $trip->DateUpdated)->where('Truck','=', $trip->Truck)->where('State','=','PAUSE')->sum('consumption');
      $getenddistance =  DB::connection('mysql')->table('novconsumption')->where('DateUpdated','=', $trip->DateUpdated)->where('Truck','=', $trip->Truck)->orderByDesc('EndMileage')->first();
      $getstartdistance =  DB::connection('mysql')->table('novconsumption')->where('DateUpdated','=', $trip->DateUpdated)->where('Truck','=', $trip->Truck)->where('StartMileage','!=', 0)->orderBy('StartMileage')->first();

        if($getconsumption != null && $getidlingconsumption != null && $getenddistance != null &&  $getstartdistance!= null){

        $createTrip = DB::connection('mysql')->table('dailyfuel')->insert([
         
        'Day' => $trip->DateUpdated,
        'Truck' => $trip->Truck,
        'FuelUsed' => $getconsumption/1000,
        'IdlingFuelUsed' => $getidlingconsumption/1000,
        'Distance' => ($getenddistance->EndMileage - $getstartdistance->StartMileage)/1000,
       // 'TruckType' => $truckDetail->type,
        'TruckCategory' => 'M/B',
        'Consumption' => (($getenddistance->EndMileage - $getstartdistance->StartMileage)/1000)/($getconsumption/1000),

      ]);

      }
     
      }

        } 
 
         }

        }
       //  dd('done..');
        Log::info('Finished total fleet board soap on', ['Truck' => $rows->Truck,  '#' => $truckCode]);
     
       } 

       dd('done');

     
    }


    public function DailySoapFleetboard(){

      ini_set('max_execution_time', 360000000000); // 3600 seconds = 60 minutes
      set_time_limit(360000000000);

      $truckData = DB::connection('mysql')->table('baselinetest')->where('Truck', '=', 'SL222 KMG172MP')->groupBy('Truck')->orderBy('id')->get();
 
      foreach ($truckData as $truckCode => $rows) {

      Log::info('Started daily fleet board soap on', ['Truck' => $rows->Truck,  '#' => $truckCode]);

      $trucks = DB::connection('mysql')->table('baselinetest')->where('Truck', '=', $rows->Truck)->where('Truck', '=', $rows->Truck)->orderBy('DateUpdated')->orderBy('Time')->groupBy('DateUpdated')->get();
 
      foreach ($trucks as  $truckrows => $trip) {

      Log::info('Started sub total fleet board soap on', ['Truck' => $rows->Truck,  '#' => $trip->DateUpdated]);

      $nextTrip = $trip;
    
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
  
    $getconsumption =  DB::connection('mysql')->table('novconsumption')->where('DateUpdated','=', $trip->DateUpdated)->where('Truck','=', $trip->Truck)->sum('consumption');
    $getidlingconsumption =  DB::connection('mysql')->table('novconsumption')->where('DateUpdated','=', $trip->DateUpdated)->where('Truck','=', $trip->Truck)->where('State','=','PAUSE')->sum('consumption');
    $getenddistance =  DB::connection('mysql')->table('novconsumption')->where('DateUpdated','=', $trip->DateUpdated)->where('Truck','=', $trip->Truck)->orderByDesc('EndMileage')->first();
    $getstartdistance =  DB::connection('mysql')->table('novconsumption')->where('DateUpdated','=', $trip->DateUpdated)->where('Truck','=', $trip->Truck)->where('StartMileage','!=', 0)->orderBy('StartMileage')->first();

      if($getconsumption != null && $getidlingconsumption != null && $getenddistance != null &&  $getstartdistance!= null){

    //   $createTrip = DB::connection('mysql')->table('dailyfuel')->insert([
       
    //   'Day' => $trip->DateUpdated,
    //   'Truck' => $trip->Truck,
    //   'FuelUsed' => $getconsumption/1000,
    //   'IdlingFuelUsed' => $getidlingconsumption/1000,
    //   'Distance' => ($getenddistance->EndMileage - $getstartdistance->StartMileage)/1000,
    //  // 'TruckType' => $truckDetail->type,
    //   'TruckCategory' => 'M/B',
    //   'Consumption' => (($getenddistance->EndMileage - $getstartdistance->StartMileage)/1000)/($getconsumption/1000),

    // ]);

    }
   
    } 

   }

   }
     //  dd('done..');
      Log::info('Finished total fleet board soap on', ['Truck' => $rows->Truck,  '#' => $truckCode]);
   
     } 

     dd('done');
    }


    public function TripSoapFleetboard()
      {
          
          ini_set('max_execution_time', 360000000000); // 3600 seconds = 60 minutes
          set_time_limit(360000000000);

          $truckData = DB::connection('mysql')->table('baselinetest')->groupBy('Truck')->orderBy('id')->get();
    
          foreach ($truckData as $truckCode => $rows) {
      
          Log::info('Started dead trip fleet board soap on', ['Truck' => $rows->Truck,  '#' => $truckCode]);
          
          //  $trucks = DB::connection('mysql')->table('baselinetest')->where('Truck', '=', $rows->Truck)->where('LoadingTripClassification', '=', 'Trip Start')->orwhere('Truck', '=', $rows->Truck)->where('LoadingTripClassification', '=', 'Trip End, Trip Start')->orderBy('DateUpdated')->orderBy('Time')->get();
             $trucks = DB::connection('mysql')->table('baselinetest')->where('Truck', '=', $rows->Truck)->where('TripClassificationv3', '=', 'Trip Start')->orderBy('DateUpdated')->orderBy('Time')->get();
           // $trucks = DB::connection('mysql')->table('baselinetest')->where('Truck', '=', $rows->Truck)->where('TripID', '!=', null)->orderBy('DateUpdated')->orderBy('Time')->get();

          foreach ($trucks as  $truckrows => $trip) {

          Log::info('Started sub dead trip fleet board soap on', ['Truck' => $rows->Truck,  '#' => $trip->id]);
       //   $nextTrip = DB::connection('mysql')->table('baselinetest')->where('id', '>', $trip->id )->where('Truck', '=', $rows->Truck)->first(); 
            $nextTrip = DB::connection('mysql')->table('baselinetest')->where('id', '>', $trip->id )->where('Truck', '=', $rows->Truck)->where('TripClassificationv3','=', 'Trip End')->first(); 
          //  dd($trip,$nextTrip);
          if($nextTrip != null){

            $start_timestamp = $trip->DateUpdated . ' ' .$trip->Time;
            $end_timestamp = $nextTrip->DateUpdated . ' ' .$nextTrip->Time;
            $startDate = Carbon::parse($start_timestamp);
          $endDate = Carbon::parse($end_timestamp);
          $truck = $trip->Truck;
          // dd($startDate,$endDate,$trip->Date,$trip->Time,$nextTrip->Date,$nextTrip->Time);

          $trips = DB::connection('mysql')->table('novconsumption')->where(function($query) use ($startDate, $endDate, $truck ) {
            $query->where('DateUpdated', '>=', $startDate->toDateString())
                  ->where('Time', '>=', $startDate->toTimeString())
                  ->where('Truck', '=', $truck);
        })
        ->where(function($query) use ($startDate, $endDate,$truck ) {
            $query->where('DateUpdated', '<=', $endDate->toDateString())
                  ->where('Time', '<=', $endDate->toTimeString())
                  ->where('Truck', '=', $truck);
        })
        ->get();
      
        $getconsumption =  $trips->sum('consumption');
        $getidlingconsumption =  $trips->where('State','=','PAUSE')->sum('consumption');
      
        $getenddistance  = DB::connection('mysql')->table('novconsumption')->where(function($query) use ($startDate, $endDate, $truck ) {
          $query->where('DateUpdated', '>=', $startDate->toDateString())
                ->where('Time', '>=', $startDate->toTimeString())
                ->where('Truck', '=', $truck);
        })
        ->where(function($query) use ($startDate, $endDate,$truck ) {
            $query->where('DateUpdated', '<=', $endDate->toDateString())
                  ->where('Time', '<=', $endDate->toTimeString())
                  ->where('Truck', '=', $truck);
        })->orderByDesc('EndMileage',)->first();

         // dd($getenddistance);
      
          $getstartdistance =  DB::connection('mysql')->table('novconsumption')->where(function($query) use ($startDate, $endDate, $truck ) {
            $query->where('DateUpdated', '>=', $startDate->toDateString())
                  ->where('Time', '>=', $startDate->toTimeString())
                  ->where('Truck', '=', $truck);
        })
        ->where(function($query) use ($startDate, $endDate,$truck ) {
            $query->where('DateUpdated', '<=', $endDate->toDateString())
                  ->where('Time', '<=', $endDate->toTimeString())
                  ->where('Truck', '=', $truck);
        })->where('StartMileage','!=', 0)->orderBy('StartMileage')->first();

        if($trips->isNotEmpty() && $getenddistance != null && $getstartdistance!= null){
  
         if(($getenddistance->EndMileage - $getstartdistance->StartMileage)/1000 < 50){

        $tripz = DB::connection('mysql')->table('baselinetest')->where('id', '=', $trip->id - 1)->where('Truck', '=', $rows->Truck)->count(); 

        if($tripz > 0){

          $trip = DB::connection('mysql')->table('baselinetest')->where('id', '=', $trip->id - 1)->where('Truck', '=', $rows->Truck)->first(); 

        }

         $start_timestamp = $trip->DateUpdated . ' ' .$trip->Time;
         $end_timestamp = $nextTrip->DateUpdated . ' ' .$nextTrip->Time;
         $startDate = Carbon::parse($start_timestamp);
         $endDate = Carbon::parse($end_timestamp);
         $truck = $trip->Truck;


         $trips = DB::connection('mysql')->table('novconsumption')->where(function($query) use ($startDate, $endDate, $truck ) {
          $query->where('DateUpdated', '>=', $startDate->toDateString())
                ->where('Time', '>=', $startDate->toTimeString())
                ->where('Truck', '=', $truck);
            })
            ->where(function($query) use ($startDate, $endDate,$truck ) {
                $query->where('DateUpdated', '<=', $endDate->toDateString())
                      ->where('Time', '<=', $endDate->toTimeString())
                      ->where('Truck', '=', $truck);
            })
            ->get();
          // dd($trips);

            $getconsumption =  $trips->sum('consumption');
            $getidlingconsumption =  $trips->where('State','=','PAUSE')->sum('consumption');
          
            $getenddistance  = DB::connection('mysql')->table('novconsumption')->where(function($query) use ($startDate, $endDate, $truck ) {
              $query->where('DateUpdated', '>=', $startDate->toDateString())
                    ->where('Time', '>=', $startDate->toTimeString())
                    ->where('Truck', '=', $truck);
          })
          ->where(function($query) use ($startDate, $endDate,$truck ) {
              $query->where('DateUpdated', '<=', $endDate->toDateString())
                    ->where('Time', '<=', $endDate->toTimeString())
                    ->where('Truck', '=', $truck);
          })->orderByDesc('EndMileage',)->first();

        // dd($getenddistance);
        
            $getstartdistance =  DB::connection('mysql')->table('novconsumption')->where(function($query) use ($startDate, $endDate, $truck ) {
              $query->where('DateUpdated', '>=', $startDate->toDateString())
                    ->where('Time', '>=', $startDate->toTimeString())
                    ->where('Truck', '=', $truck);
          })
          ->where(function($query) use ($startDate, $endDate,$truck ) {
              $query->where('DateUpdated', '<=', $endDate->toDateString())
                    ->where('Time', '<=', $endDate->toTimeString())
                    ->where('Truck', '=', $truck);
          })->where('StartMileage','!=', 0)->orderBy('StartMileage')->first();

        // dd(($getenddistance->EndMileage - $getstartdistance->StartMileage)/1000,'less than 50',$trips);
            }
          }

          //  dd(($getenddistance->EndMileage - $getstartdistance->StartMileage)/1000,'more than 50');

              if($getconsumption != null && $getidlingconsumption != null && $getenddistance != null && $getstartdistance!= null){

              $tripUpdate = DB::connection('mysql')->table('baselinetest')->where('Truck', '=', $rows->Truck)->where('id', '=', $nextTrip->id)->update([

              'TotalFuelUsed' => $getconsumption/1000,
              'TotalDistance' => ($getenddistance->EndMileage - $getstartdistance->StartMileage)/1000,
              'TotalConsumption' => (($getenddistance->EndMileage - $getstartdistance->StartMileage)/1000)/($getconsumption/1000),

              ]); 

                }

         }

        }

   
        Log::info('Finished dead trip fleet board soap on', ['Truck' => $rows->Truck,  '#' => $truckCode]);
     
     
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
 
     $truckData = DB::connection('mysql')->table('baselinetest')->where('Truck','=','KWY053MP')->groupBy('Truck')->orderBy('id')->get();

      foreach ($truckData as $truckCode => $rows) {

        Log::info('Started power BI time calculation', ['Truck' => $rows->Truck,  '#' => $truckCode]);

        $count =  DB::connection('mysql')->table('baselinetest')->where('Truck', '=', $rows->Truck)->orderBy('DateUpdated')->orderBy('Time')->count();
        if($count > 0){
       $trucks =  DB::connection('mysql')->table('baselinetest')->where('Truck', '=', $rows->Truck)->orderBy('DateUpdated')->orderBy('Time')->skip(1)->take($count - 1)->get();
       $prevTruck =  DB::connection('mysql')->table('baselinetest')->where('Truck', '=', $rows->Truck)->orderBy('DateUpdated')->orderBy('Time')->first();

      foreach ($trucks as  $trip) {

       $prev = $prevTruck->id;

      $currentTrip = $trip->GFupdated1;

      $next = $trip->id + 1;
      $previousFullTrip = DB::connection('mysql')->table('baselinetest')->where('id', '=', $prev)->first();


      //step 1
      if($trip->GFupdated1  ==  $previousFullTrip->GFupdated1 ){
   
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


       }else{

        $result =  $trip->TimeDifferenceMins;

        $updateCount = DB::connection('mysql')->table('baselinetest')->where('id', '=', $trip->id)->update([

            'CumulativeTripClassification' =>  $result
        ]);

       }
       

       $prevTruck = $trip;
 
      }

     }

     Log::info('Finished CumulativeTime on', ['Truck' => $rows->Truck,  '#' => $truckCode]);
  
     }

     dd('done');

    }

    //8
    public function TripTime()
    {
        
        ini_set('max_execution_time', 360000000000); // 3600 seconds = 60 minutes
        set_time_limit(360000000000);

        $truckData = DB::connection('mysql')->table('baselinetest')->groupBy('Truck')->orderBy('id')->get();
        
        foreach ($truckData as $truckCode => $rows) {

        Log::info('Started triptime on', ['Truck' => $rows->Truck,  '#' => $truckCode]);
    
        $trucks = DB::connection('mysql')->table('baselinetest')->where('Truck', '=', $rows->Truck)->where('TripID', '!=', null)->groupBy('TripID')->orderBy('DateUpdated')->orderBy('Time')->get();
       //  dd($trucks);
        foreach ($trucks as  $truckrows => $trip) {

        Log::info('Started triptime on', ['Truck' => $trip->Truck, 'tripId' => $trip->TripID]); 
        $sum = DB::connection('mysql')->table('baselinetest')->where('TripID', '=', $trip->TripID)->sum('EventTime');   
        $last = DB::connection('mysql')->table('baselinetest')->where('TripID', '=', $trip->TripID)->orderBy('id', 'desc')->first();  
        // dd($sum,$last);
        $tripUpdate = DB::connection('mysql')->table('baselinetest')->where('id', '=', $last->id)->update([

           'TripTimev2' => $sum

        ]); 


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


       //dd('done.');
     
    }

    //9
    public function LoadingTimesv2()
    {

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

     // dd('done');

   
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

      // dd('done');

     
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

      // dd('done');
    }


    
    public function FleetRefactor()
    {

       ini_set('max_execution_time', 360000000000); // 3600 seconds = 60 minutes
       set_time_limit(360000000000);

       $truckData = DB::connection('mysql')->table('baselinetest')->where('Truck','=','SL162 JST599MP')->groupBy('Truck')->orderBy('id')->get();
       // dd($truckData);
       foreach ($truckData as $truckCode => $rows) {

        Log::info('Started truck refactor on', ['Truck' => $rows->Truck,  '#' => $truckCode]);
       
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

      $parts = explode(' ', $results);

      $first_part = $parts[0];

       $trucks = DB::connection('mysql')->table('fleetlist')->where('fleetNumber', '=', $first_part)->first();
       if($trucks != null){

       $truck = DB::connection('mysql')->table('truckmap')->where('Truck', '=', $rows->Truck)->first();

        if($truck == null){

          $createTrip = DB::connection('mysql')->table('truckmap')->insert([
         
            'Fleet' => $trucks->fleetNumber,
            'Registration' => $trucks->regNumber,
            'Truck' =>  $rows->Truck,
            'capacity' => $trucks->capacity,
            'type' => $trucks->type,
  
          ]);

        }else{



        }

       }
        
   
       Log::info('Finished truck refactor on', ['Truck' => $rows->Truck,  '#' => $truckCode]);

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

      // dd('done');

     
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
      
     //  dd('done');
             
    }

    public function loadCapacity()
    {    
      
        ini_set('max_execution_time', 360000000000); // 3600 seconds = 60 minutes
        set_time_limit(360000000000);

        $truckData = DB::connection('mysql')->table('baselinetest')->groupBy('Truck')->orderBy('id')->get();
   
        foreach ($truckData as $truckCode => $rows) {

        Log::info('Started Load Capacity on', ['Truck' => $rows->Truck,  '#' => $truckCode]);

        $startDate = '2024-04-01'; // Replace with your start date
        $endDate = '2024-04-30'; // Replace with your end date

          // Convert to DateTime objects
          $startDateTime = new DateTime($startDate);
          $endDateTime = new DateTime($endDate);

    
        $trucks = DB::connection('mysql')->table('baselinetest')->where('TripClassificationv3', '=', 'Trip End' )->where('Truck', '=', $rows->Truck)->get();
     
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

      // dd('done');
             
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

        

        }

        }

        Log::info('Finished fleetboard Capacity on', ['Truck' => $rows->Truck,  '#' => $truckCode]);

       } 

      // dd('done');

             
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

      // dd('done');

 
     
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

     //  dd('done');

    }

    public function TimeSpentPercentageDeadruns()
    {
        
      ini_set('max_execution_time', 360000000000); // 3600 seconds = 60 minutes
      set_time_limit(360000000000);

      $truckData = DB::connection('mysql')->table('baselinetest')->groupBy('Truck')->orderBy('id')->get();
 
      foreach ($truckData as $truckCode => $rows) {

      Log::info('Started time spent percentage on deadrun', ['Truck' => $rows->Truck,  '#' => $truckCode]); 
 
      $trucks = DB::connection('mysql')->table('baselinetest')->where('Truck', '=', $rows->Truck)->where('LoadingTripClassification', '=', 'Trip Start')->orwhere('Truck', '=', $rows->Truck)->where('LoadingTripClassification', '=', 'Trip End, Trip Start')->orderBy('DateUpdated')->orderBy('Time')->get();
     // dd($trucks);
      foreach ($trucks as  $truckrows => $trip) {
          
      $nextTrip = DB::connection('mysql')->table('baselinetest')->where('id', '>', $trip->id )->where('Truck', '=', $rows->Truck)->where('LoadingTripClassification','=', 'Trip End')->orwhere('id', '>', $trip->id )->where('Truck', '=', $rows->Truck)->where('LoadingTripClassification','=', 'Trip End, Trip Start')->first(); 

      if($nextTrip != null){
          
     //   Log::info('Started sub time spent percentage on deadrun', ['Truck' => $trip->Truck,  '#' => $trip->id,$nextTrip->id]); 
   
        if($trip->LoadingTripClassification == 'Trip Start' && $trip->TripClassificationv3 == 'Trip End'){

          $trips =  DB::connection('mysql')->table('baselinetest')->whereBetween('id', [$trip->id+1, $nextTrip->id])->where('Truck','=',$trip->Truck)->get();
        }
       //loading times on trip start
       if($trip->LoadingTripClassification == 'Trip Start' && $trip->TripClassificationv3 == null){

        $trips =  DB::connection('mysql')->table('baselinetest')->whereBetween('id', [$trip->id, $nextTrip->id])->where('Truck','=',$trip->Truck)->get();
      }

      if($trip->LoadingTripClassification == 'Trip End, Trip Start' && $trip->TripClassificationv3 == null){

        $trips =  DB::connection('mysql')->table('baselinetest')->whereBetween('id', [$trip->id+1, $nextTrip->id])->where('Truck','=',$trip->Truck)->get();

      }

        foreach($trips as $tripData){

          if($tripData->EventTime != null){

          $percentage = (($tripData->EventTime)/$nextTrip->TripTimev2)*100;

          $tripUpdate = DB::connection('mysql')->table('baselinetest')->where('id', '=', $tripData->id)->update([

            'TimeSpentPercentage' => $percentage,
 
         ]); 

         }
    
        }
     
       }

      }

      Log::info('Finished time spent percentage on deadrun', ['Truck' => $rows->Truck,  '#' => $truckCode]);

     } 

     dd('done');

    }

    public function TimeSpentPercentageOffloading()
    {
        
        ini_set('max_execution_time', 360000000000); // 3600 seconds = 60 minutes
        set_time_limit(360000000000);

        $truckData = DB::connection('mysql')->table('baselinetest')->groupBy('Truck')->orderBy('id')->get();
   
        foreach ($truckData as $truckCode => $rows) {

        Log::info('Started time spent percentage on', ['Truck' => $rows->Truck,  '#' => $truckCode]);
    
        $trucks = DB::connection('mysql')->table('baselinetest')->where('Truck', '=', $rows->Truck)->where('TripClassificationv3', '=', 'Trip Start')->orderBy('DateUpdated')->orderBy('Time')->get();
     
        foreach ($trucks as  $truckrows => $trip) {
            
        $nextTrip = DB::connection('mysql')->table('baselinetest')->where('id', '>', $trip->id )->where('TripClassificationv3','=', 'Trip End')->where('Truck','=',$trip->Truck)->first(); 
    
        if($nextTrip != null){

          $trips =  DB::connection('mysql')->table('baselinetest')->whereBetween('id', [$trip->id+1, $nextTrip->id])->where('Truck','=',$trip->Truck)->get();
        
          foreach($trips as $tripData){

            if($tripData->EventTime != null){

            $percentage = (($tripData->EventTime)/$nextTrip->TripTimev2)*100;

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

      //  dd('done');
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

     //  dd('done');

             
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

       //  dd('done');

      }


      public function TripTimeTruck()
      {
  
        ini_set('max_execution_time', 360000000000); // 3600 seconds = 60 minutes
        set_time_limit(360000000000);
  
        $truckData = DB::connection('mysql')->table('baselinetest')->groupBy('Truck')->orderBy('id')->get();
   
        foreach ($truckData as $truckCode => $rows) {
  
        Log::info('Started Trip Time Truck on', ['Truck' => $rows->Truck,  '#' => $truckCode]);
    
        $trucks = DB::connection('mysql')->table('baselinetest')->where('Truck', '=', $rows->Truck)->orderBy('DateUpdated')->orderBy('Time')->get();
     
        foreach ($trucks as  $truckrows => $trip)
        {

            $dateTime = DateTime::createFromFormat('Y-m-d', $trip->DateUpdated);
            $formattedDate = $dateTime->format('d F');

            $time = DateTime::createFromFormat('H:i:s.u', $trip->Time);
            $sortedTime = $time->format('H:i:s');

            $response = $formattedDate .' @ '.$sortedTime.' & '.$trip->Truck;
             
              $tripUpdate = DB::connection('mysql')->table('baselinetest')->where('id', '=', $trip->id)->update([
  
                'TruckTimeClassification' => $response,
     
             ]); 
  
    
        }
  
        Log::info('Finished TripTimeTruck on', ['Truck' => $rows->Truck,  '#' => $truckCode]);
        //  dd('done');
  
       } 
  
      }
  
      
        /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
       // Updated Trip Data after showing Taps
      public function TripClassificationV3()
      {

      ini_set('max_execution_time', 360000000000); // 3600 seconds = 60 minutes
      set_time_limit(360000000000);

      $truckData = DB::connection('mysql')->table('baselinetest')->groupBy('Truck')->orderBy('id')->get();
     
      foreach ($truckData as $truckCode => $rows) {

      Log::info('Started Trip Classification V3 on', ['Truck' => $rows->Truck,  '#' => $truckCode]);

      $truckupdate = DB::connection('mysql')->table('baselinetest')->where('id', '=', $rows->id)->update([

        'TripClassificationv3' => 'Trip End'

      ]);

     $trucks = DB::connection('mysql')->table('baselinetest')->where('Truck', '=', $rows->Truck)->where('id' ,'>', 1)->orderBy('DateUpdated')->orderBy('Time')->get();
     // $trucks = DB::connection('mysql')->table('baselinetest')->where('Truck', '=', $rows->Truck)->where('id' ,'>=', 131370)->orderBy('DateUpdated')->orderBy('Time')->get();


      foreach ($trucks as  $truckrows => $trip) {

        $prev = DB::connection('mysql')->table('baselinetest')->where('id', '=', $trip->id - 1)->first();
        $prevv =  DB::connection('mysql')->table('baselinetest')->where('id', '=', $trip->id - 2)->first();
        $next = DB::connection('mysql')->table('baselinetest')->where('id', '=', $trip->id + 1)->first();
       // $nexttt = DB::connection('mysql')->table('baselinetest')->where('id', '=', $trip->id + 2)->first();
       
        //Trip Start
        $currentRoute = DB::connection('mysql')->table('routes')->where('LoadingPoint', '=', $trip->GFupdated1)->first();
        $prevRoute = DB::connection('mysql')->table('routes')->where('LoadingPoint', '=', $prev->GFupdated1)->first();
        $recentprev = DB::connection('mysql')->table('baselinetest')->whereNotNull('TripClassificationv3')->where('id','<', $trip->id)->orderBy('id', 'desc')->first();

       // if($currentRoute != null &&  $prevRoute == null && $recentprev->TripClassificationv3 == 'Trip End'){

        if($recentprev->TripClassificationv3 == 'Trip End'){

           $tripEndCheck =  DB::connection('mysql')->table('baselinetest')->whereBetween('id', [$trip->id, $trip->id+9])
           ->where('Truck','=', $trip->Truck)
           ->get();

           $offloadCheck = null;

           foreach($tripEndCheck as $RouteCheck){
          
            $routeSet = DB::connection('mysql')->table('routes')->where('LoadingPoint', '=', $trip->GFupdated1)->where('OffloadingPoint','=', $RouteCheck->GFupdated1)->first();

            if($routeSet){
            
              $offloadCheck = 1;

            }
          
           }

          if($offloadCheck){
        
          $prev = DB::connection('mysql')->table('baselinetest')->where('id', '=', $trip->id )->update([

            'TripClassificationv3' => 'Trip Start'

          ]);

          }

        }
        
        //Trip End
        $currentRoute1 = DB::connection('mysql')->table('routes')->where('LoadingPoint', '=', $recentprev->GFupdated1)->where('OffloadingPoint','=', $trip->GFupdated1)->first();
        if($next != null){
        $nextRoute = DB::connection('mysql')->table('routes')->where('LoadingPoint', '=', $recentprev->GFupdated1)->where('OffloadingPoint','=', $next->GFupdated1)->first();
         if($nextRoute && $currentRoute1){    
          if($currentRoute1->id != $nextRoute->id){
            $check = 0;
          }else{
            $check = 1;
          }
         }else{
          $check = 0;
         }

        if($currentRoute1 != null && $check == 0 && $recentprev->TripClassificationv3 == 'Trip Start'){
        //  dd($trip);
       
          $prev = DB::connection('mysql')->table('baselinetest')->where('id', '=', $trip->id )->update([

            'TripClassificationv3' => 'Trip End',

          ]); 

     

        }

        }
     
      }

      $truckupdate = DB::connection('mysql')->table('baselinetest')->where('id', '=', $rows->id)->update([

        'TripClassificationv3' => null

      ]);

      Log::info('Finished Trip Classification V3 on', ['Truck' => $rows->Truck,  '#' => $truckCode]);
     
    }

  //  dd('nothing...'); 
 
    }


   public function TripClassificationV3Updated()
   {

    ini_set('max_execution_time', 360000000000); // 3600 seconds = 60 minutes
    set_time_limit(360000000000);

    $truckData = DB::connection('mysql')->table('baselinetest')->groupBy('Truck')->orderBy('id')->get();

    foreach ($truckData as $truckCode => $rows) {

    Log::info('Started Trip class v3 updated v2 on', ['Truck' => $rows->Truck,  '#' => $truckCode]);

    $trucks = DB::connection('mysql')->table('baselinetest')->where('Truck', '=', $rows->Truck)->where('TripClassificationv3', '=', 'Trip Start')->orderBy('DateUpdated')->orderBy('Time')->get();
     //$trucks = DB::connection('mysql')->table('baselinetest')->where('id', '=', 324 )->orderBy('DateUpdated')->orderBy('Time')->get();

    foreach ($trucks as  $truckrows => $trip) {
        
    $nextTrip = DB::connection('mysql')->table('baselinetest')->where('id', '>', $trip->id )->where('Truck', '=', $rows->Truck)->where('TripClassificationv3','=', 'Trip End')->first(); 

    if($nextTrip != null){
      
    //loading times on trip start
    $interval =  DB::connection('mysql')->table('baselinetest')->whereBetween('id', [$trip->id, $nextTrip->id])
    ->where('Truck', '=', $rows->Truck)
    ->where('GFupdated1','=','Witbank yard')
    ->orderBy('id', 'desc')
    ->first();
 
     if($interval){

    $newStartCheck =  DB::connection('mysql')->table('baselinetest')->whereBetween('id', [$interval->id, $nextTrip->id])
    ->where('Truck', '=', $rows->Truck)
    ->where('GFupdated1','=', $trip->GFupdated1)
    ->first();

    
    if($newStartCheck){

     $tripUpdate = DB::connection('mysql')->table('baselinetest')->where('id', '=', $newStartCheck->id)->update([

      'TripClassificationv3' => 'Trip Start'

     ]); 

     $tripUpdate = DB::connection('mysql')->table('baselinetest')->where('id', '=', $trip->id)->update([

      'TripClassificationv3' => null

     ]); 

     }

    }

     }

    }

    Log::info('Finished Trip class v3 updated on', ['Truck' => $rows->Truck,  '#' => $truckCode]);

     } 


     }



    public function TripTimeRoutev2()
    {
           
           ini_set('max_execution_time', 360000000000); // 3600 seconds = 60 minutes
           set_time_limit(360000000000);
   
           $truckData = DB::connection('mysql')->table('baselinetest')->groupBy('Truck')->orderBy('id')->get();
      
           foreach ($truckData as $truckCode => $rows) {
   
           Log::info('Started Trip Time Route v2 on', ['Truck' => $rows->Truck,  '#' => $truckCode]);

           $startDate = '2024-04-01'; // Replace with your start date
           $endDate = '2024-04-30'; // Replace with your end date
   
             // Convert to DateTime objects
             $startDateTime = new DateTime($startDate);
             $endDateTime = new DateTime($endDate);
       
           $trucks = DB::connection('mysql')->table('baselinetest')->where('Truck', '=', $rows->Truck)->where('TripClassificationv3', '=', 'Trip Start')->orderBy('DateUpdated')->orderBy('Time')->get();
           //$trucks = DB::connection('mysql')->table('baselinetest')->where('id', '=', 3857)->orderBy('DateUpdated')->orderBy('Time')->get();

           foreach ($trucks as  $truckrows => $trip) {
               
           $nextTrip = DB::connection('mysql')->table('baselinetest')->where('id', '>', $trip->id )->where('Truck', '=', $rows->Truck)->where('TripClassificationv3','=', 'Trip End')->first(); 
           // dd($trip,$nextTrip);
           if($nextTrip != null){
   
           //loading times on trip start
           $interval =  DB::connection('mysql')->table('baselinetest')->whereBetween('id', [$trip->id+1, $nextTrip->id])
           ->where('Truck', '=', $rows->Truck)
           ->sum('EventTime');

             $tripUpdate = DB::connection('mysql')->table('baselinetest')->where('Truck', '=', $rows->Truck)->where('id', '=', $nextTrip->id)->update([
   
               'TripRoutev2' => $trip->GFupdated1. ' to ' . $nextTrip->GFupdated1,
               'TripTimev2' => $interval,
               'LoadingTripClassificationv2' => 'Offloading Trip'
    
            ]); 

            
            $tripUpdateAll = DB::connection('mysql')->table('baselinetest')->where('Truck', '=', $rows->Truck)->whereBetween('id', [$trip->id+1, $nextTrip->id])->update([
   
              'TripRoutev2' => $trip->GFupdated1. ' to ' . $nextTrip->GFupdated1,
             // 'TripTimev2' => $interval,
   
           ]); 
   
            }
   
           }
       
          // dd('done');
           Log::info('Finished Trip Time Route V2on', ['Truck' => $rows->Truck,  '#' => $truckCode]);
   
          } 
   
         // dd('done');
   
        
   }
   
   public function TripClassificationV7()
   {
           ini_set('max_execution_time', 360000000000); // 3600 seconds = 60 minutes
           set_time_limit(360000000000);
   
           $truckData = DB::connection('mysql')->table('baselinetest')->groupBy('Truck')->orderBy('id')->get();
      
           foreach ($truckData as $truckCode => $rows) {
   
           Log::info('Started Trip Class v7 on', ['Truck' => $rows->Truck,  '#' => $truckCode]);

           $trucks = DB::connection('mysql')->table('baselinetest')->where('Truck', '=', $rows->Truck)->where('TripClassificationv3', '=', 'Trip Start')->orderBy('DateUpdated')->orderBy('Time')->get();
        
           foreach ($trucks as  $truckrows => $trip) {
               
           $nextTrip = DB::connection('mysql')->table('baselinetest')->where('id', '>', $trip->id )->where('Truck', '=', $rows->Truck)->where('TripClassificationv3','=', 'Trip End')->first(); 
       
           if($nextTrip != null){
   
           $interval =  DB::connection('mysql')->table('baselinetest')->whereBetween('id', [$trip->id, $nextTrip->id])
           ->where('Truck', '=', $rows->Truck)
           ->where('GFupdated1','=', 'Witbank yard')
           ->count();

         
           if($interval > 1){

            $getWitbankYard =  DB::connection('mysql')->table('baselinetest')->whereBetween('id', [$trip->id+1, $nextTrip->id])
            ->where('Truck', '=', $rows->Truck)
            ->get();
             
          //  dd($getWitbankYard);

             foreach($getWitbankYard as $witbank){
                
              $prev = DB::connection('mysql')->table('baselinetest')->where('id', '=', $witbank->id - 1 )->first();
              $current = DB::connection('mysql')->table('baselinetest')->where('id', '=', $witbank->id )->first();
              $next = DB::connection('mysql')->table('baselinetest')->where('id', '=', $witbank->id + 1 )->first();

              if($prev->GFupdated1 == 'Witbank yard' && $current->GFupdated1 == 'Witbank yard' && $next->GFupdated1 != 'Witbank yard'){

                $tripUpdate = DB::connection('mysql')->table('baselinetest')->where('id', '=', $current->id)->update([
   
                  'TripClassificationv7' => 'At Depot (Loaded)'
           
               ]); 

              }

              if($prev->GFupdated1 == 'Witbank yard' && $current->GFupdated1 == 'Witbank yard' && $next->GFupdated1 == 'Witbank yard'){

                $tripUpdate = DB::connection('mysql')->table('baselinetest')->where('id', '=', $current->id)->update([
   
                  'TripClassificationv7' => 'At Depot (Loaded)'
           
               ]); 

              }
                         
             }

             }

            }
   
           }
   
           Log::info('Finished Trip Class V7 on', ['Truck' => $rows->Truck,  '#' => $truckCode]);
   
          } 
   
        
     }


       public function TripClassificationV7loading()
       {
           ini_set('max_execution_time', 360000000000); // 3600 seconds = 60 minutes
           set_time_limit(360000000000);
   
           $truckData = DB::connection('mysql')->table('baselinetest')->groupBy('Truck')->orderBy('id')->get();
      
           foreach ($truckData as $truckCode => $rows) {
   
           Log::info('Started Trip Class v7 load on', ['Truck' => $rows->Truck,  '#' => $truckCode]);
       
           $trucks = DB::connection('mysql')->table('baselinetest')->where('Truck', '=', $rows->Truck)->where('TripClassificationv3', '=', 'Trip End')->orderBy('DateUpdated')->orderBy('Time')->get();
        
           foreach ($trucks as  $truckrows => $trip) {
               
           $nextTrip = DB::connection('mysql')->table('baselinetest')->where('id', '>', $trip->id )->where('Truck', '=', $rows->Truck)->where('TripClassificationv3','=', 'Trip Start')->first(); 
       
           if($nextTrip != null){
        
            
           $interval =  DB::connection('mysql')->table('baselinetest')->whereBetween('id', [$trip->id, $nextTrip->id])
           ->where('Truck', '=', $rows->Truck)
           ->where('GFupdated1','=', 'Witbank yard')
           ->count();

         
           if($interval > 1){
         
            $getWitbankYard =  DB::connection('mysql')->table('baselinetest')->whereBetween('id', [$trip->id+1, $nextTrip->id])
            ->where('Truck', '=', $rows->Truck)
            ->get();
             
          //  dd($getWitbankYard);

             foreach($getWitbankYard as $witbank){
                
              $prev = DB::connection('mysql')->table('baselinetest')->where('id', '=', $witbank->id - 1 )->first();
              $current = DB::connection('mysql')->table('baselinetest')->where('id', '=', $witbank->id )->first();
              $next = DB::connection('mysql')->table('baselinetest')->where('id', '=', $witbank->id + 1 )->first();

              if($prev->GFupdated1 == 'Witbank yard' && $current->GFupdated1 == 'Witbank yard' && $next->GFupdated1 != 'Witbank yard'){
            
                $tripUpdate = DB::connection('mysql')->table('baselinetest')->where('id', '=', $current->id)->update([
   
                  'TripClassificationv7' => 'At Depot (No Load)'
           
               ]); 

              }

              if($prev->GFupdated1 == 'Witbank yard' && $current->GFupdated1 == 'Witbank yard' && $next->GFupdated1 == 'Witbank yard'){
            
                $tripUpdate = DB::connection('mysql')->table('baselinetest')->where('id', '=', $current->id)->update([
   
                  'TripClassificationv7' => 'At Depot (No Load)'
           
               ]); 

              }
                         
             }

             }

            }
   
           }
   
           Log::info('Finished Trip Class V7 load on', ['Truck' => $rows->Truck,  '#' => $truckCode]);
   
          } 
   
        
     }
 
    public function Deadruns()
    {
          ini_set('max_execution_time', 360000000000); // 3600 seconds = 60 minutes
          set_time_limit(360000000000);
  
          $truckData = DB::connection('mysql')->table('baselinetest')->groupBy('Truck')->orderBy('id')->get();
     
          foreach ($truckData as $truckCode => $rows) {
  
          Log::info('Started Dead runs on', ['Truck' => $rows->Truck,  '#' => $truckCode]);

          $trucks = DB::connection('mysql')->table('baselinetest')->where('Truck', '=', $rows->Truck)->where('TripClassificationv3', '=', 'Trip End')->orderBy('DateUpdated')->orderBy('Time')->get();
       //  $trucks = DB::connection('mysql')->table('baselinetest')->where('Truck', '=', $rows->Truck)->where('id', '=', 6919)->orderBy('DateUpdated')->orderBy('Time')->get();
          foreach ($trucks as  $truckrows => $trip) {
              
          $nextTrip = DB::connection('mysql')->table('baselinetest')->where('id', '>', $trip->id )->where('Truck', '=', $rows->Truck)->where('TripClassificationv3','=', 'Trip Start')->first(); 
        //  Log::info('Started sub dead runs on', ['Truck' => $trip->id,  '#' => $truckCode]);
          if($nextTrip != null){
             
            $count =  DB::connection('mysql')->table('baselinetest')->whereBetween('id', [$trip->id, $nextTrip->id])
            ->where('Truck', '=', $rows->Truck)
            ->where('GFupdated1','=','Witbank yard')
            ->count();

           // dd($count);
            if($count == 0){

              $recentprev = DB::connection('mysql')->table('baselinetest')->where('Truck', '=', $rows->Truck)->where('TripClassificationv3','=', 'Trip Start')->where('id','<', $trip->id)->orderBy('id', 'desc')->first();
                if($recentprev != null && $nextTrip != null){
              
              if($recentprev->GFupdated1 == $nextTrip->GFupdated1){
              
                $loadingplace = 'Loading trip (same loading point)';

              }else{

                 $loadingplace = 'Loading trip (different loading point)';
              }

            }
         
              $tripUpdate = DB::connection('mysql')->table('baselinetest')->where('id', '=', $nextTrip->id)->update([

                'LoadingTripClassification' => 'Trip End',
                'LoadingTripClassificationv2' => $loadingplace
          
               ]); 
                  
              $classUpdate= DB::connection('mysql')->table('baselinetest')->where('id', '=', $trip->id)->update([

                'LoadingTripClassification' => 'Trip Start',
          
               ]); 
          
            }else{

              $tripUpdate = DB::connection('mysql')->table('baselinetest')->where('id', '=', $nextTrip->id)->update([

                'LoadingTripClassification' => 'Trip End',
                'LoadingTripClassificationv2' => 'Dead run (from depot)'
          
               ]); 
                  
              $classUpdate= DB::connection('mysql')->table('baselinetest')->where('id', '=', $trip->id)->update([

                'LoadingTripClassification' => 'Trip Start',
          
               ]); 

              $getWitbank =  DB::connection('mysql')->table('baselinetest')->whereBetween('id', [$trip->id, $nextTrip->id])
              ->where('Truck', '=', $rows->Truck)
              ->where('GFupdated1','=','Witbank yard')
              ->get();

              foreach ($getWitbank as $wits => $witbank) {
              
                $prev =  DB::connection('mysql')->table('baselinetest')->where('id', '=', $witbank->id - 1)->where('Truck', '=', $rows->Truck)->first();    
                $next = DB::connection('mysql')->table('baselinetest')->where('id', '=', $witbank->id + 1)->where('Truck', '=', $rows->Truck)->first();
              //  Log::info('Started sub sub dead runs on', ['Truck' => $witbank->id,  '#' => $truckCode]);
             
                if($next != null && $prev != null){

                if($prev->GFupdated1 != 'Witbank yard' && $witbank->GFupdated1 == 'Witbank yard' && $next->GFupdated1 != 'Witbank yard'){
                      
                  $tripUpdate = DB::connection('mysql')->table('baselinetest')->where('id', '=', $witbank->id)->update([
   
                    'LoadingTripClassification' => 'Trip End, Trip Start',
                    'LoadingTripClassificationv2' => 'Dead run (to depot)'
             
                 ]); 

                }elseif($prev->GFupdated1 != 'Witbank yard' && $witbank->GFupdated1 == 'Witbank yard' && $next->GFupdated1 == 'Witbank yard'){

                  $tripUpdate = DB::connection('mysql')->table('baselinetest')->where('id', '=', $witbank->id)->update([
   
                    'LoadingTripClassification' => 'Trip End',
                    'LoadingTripClassificationv2' => 'Dead run (to depot)'
                    
             
                 ]); 

                }elseif($prev->GFupdated1 == 'Witbank yard' && $witbank->GFupdated1 == 'Witbank yard' && $next->GFupdated1 != 'Witbank yard'){

                   $tripUpdate = DB::connection('mysql')->table('baselinetest')->where('id', '=', $witbank->id)->update([
   
                    'LoadingTripClassification' => 'Trip Start'
             
                 ]); 

                }else{

                }

              }
        

              }

           
            }

  
           }
  
          }
  
          Log::info('Finished Dead runs on', ['Truck' => $rows->Truck,  '#' => $truckCode]);
  
         } 
     
   }

  public function TripTimeRoutev2Deadruns()
  {
         
         ini_set('max_execution_time', 360000000000); // 3600 seconds = 60 minutes
         set_time_limit(360000000000);
 
         $truckData = DB::connection('mysql')->table('baselinetest')->groupBy('Truck')->orderBy('id')->get();
    
         foreach ($truckData as $truckCode => $rows) {
 
         Log::info('Started Trip Time Route deadruns on', ['Truck' => $rows->Truck,  '#' => $truckCode]);

         $startDate = '2024-04-01'; // Replace with your start date
         $endDate = '2024-04-30'; // Replace with your end date
 
           // Convert to DateTime objects
           $startDateTime = new DateTime($startDate);
           $endDateTime = new DateTime($endDate);
     
         $trucks = DB::connection('mysql')->table('baselinetest')->where('Truck', '=', $rows->Truck)->where('LoadingTripClassification', '=', 'Trip Start')->orwhere('Truck', '=', $rows->Truck)->where('LoadingTripClassification', '=', 'Trip End, Trip Start')->orderBy('DateUpdated')->orderBy('Time')->get();
      
         foreach ($trucks as  $truckrows => $trip) {
             
         $nextTrip = DB::connection('mysql')->table('baselinetest')->where('id', '>', $trip->id )->where('Truck', '=', $rows->Truck)->where('LoadingTripClassificationv2','!=', null)->first(); 
        // dd($trip,$nextTrip);
         if($nextTrip != null){
            
          if($trip->LoadingTripClassification == 'Trip Start' && $trip->TripClassificationv3 == 'Trip End'){
     
          $tripUpdateAll = DB::connection('mysql')->table('baselinetest')->where('Truck', '=', $rows->Truck)->whereBetween('id', [$trip->id+1, $nextTrip->id])->update([
 
          'TripRoutev2' => $trip->GFupdated1. ' to ' . $nextTrip->GFupdated1,

          ]); 

          }
         //loading times on trip start
         if($trip->LoadingTripClassification == 'Trip Start' && $trip->TripClassificationv3 == null){

          $tripUpdateAll = DB::connection('mysql')->table('baselinetest')->where('Truck', '=', $rows->Truck)->whereBetween('id', [$trip->id, $nextTrip->id])->update([
 
            'TripRoutev2' => $trip->GFupdated1. ' to ' . $nextTrip->GFupdated1,
          //  'TripTimev2' => $interval
 
         ]); 
        }

        if($trip->LoadingTripClassification == 'Trip End, Trip Start' && $trip->TripClassificationv3 == null){

          $tripUpdateAll = DB::connection('mysql')->table('baselinetest')->where('Truck', '=', $rows->Truck)->whereBetween('id', [$trip->id+1, $nextTrip->id])->update([
 
            'TripRoutev2' => $trip->GFupdated1. ' to ' . $nextTrip->GFupdated1,
          //  'TripTimev2' => $interval
 
         ]); 
        }

        $interval =  DB::connection('mysql')->table('baselinetest')->whereBetween('id', [$trip->id+1, $nextTrip->id])
        ->where('Truck', '=', $rows->Truck)
        ->sum('EventTime');

           $tripUpdate = DB::connection('mysql')->table('baselinetest')->where('Truck', '=', $rows->Truck)->where('id','=', $nextTrip->id)->update([
 
             'TripRoutev2' => $trip->GFupdated1. ' to ' . $nextTrip->GFupdated1,
             'TripTimev2' => $interval
  
          ]); 

 
          }
 
         }
 
         Log::info('Finished Trip Time Route deadruns on', ['Truck' => $rows->Truck,  '#' => $truckCode]);
 
        }
       // dd('done'); 

  }

 public function TripID()
 {
        
        ini_set('max_execution_time', 360000000000); // 3600 seconds = 60 minutes
        set_time_limit(360000000000);

        $trucks = DB::connection('mysql')->table('baselinetest')->where('LoadingTripClassificationv2', '!=', null)->orderBy('Truck')->orderBy('DateUpdated')->orderBy('Time')->get();
       // dd($trucks);
        foreach ($trucks as $truckrows => $trip) {
            
        Log::info('Started Trip ID on', ['Truck' => $trip->Truck,  '#' => $trip->id]); 

          if($trip->LoadingTripClassificationv2 == 'Offloading Trip'){
              
            $recentprev = DB::connection('mysql')->table('baselinetest')->where('Truck', '=', $trip->Truck)->where('TripClassificationv3' ,'=', 'Trip Start')->where('id','<', $trip->id)->orderBy('id', 'desc')->first();

            $interval =  DB::connection('mysql')->table('baselinetest')->whereBetween('id', [$recentprev->id, $trip->id])
            ->where('Truck', '=', $trip->Truck)
            ->where('TripID', '=', null)
            ->update([
              'TripID' => $truckrows + 1
            ]);

           // dd($recentprev,$trip,$truckrows);
          }else{

            $recentprev = DB::connection('mysql')->table('baselinetest')->where('Truck', '=', $trip->Truck)->where('id','<', $trip->id)->where('LoadingTripClassification' ,'=', 'Trip Start')->orwhere('Truck', '=', $trip->Truck)->where('LoadingTripClassification' ,'=', 'Trip End, Trip Start')->where('id','<', $trip->id)->orderBy('id', 'desc')->first();
          // dd($trip,$recentprev);
           if($recentprev){
            $interval =  DB::connection('mysql')->table('baselinetest')->whereBetween('id', [$recentprev->id, $trip->id])
            ->where('Truck', '=', $trip->Truck)
            ->where('TripID', '=', null)
            ->update([
              'TripID' => $truckrows + 1
            ]);
          }

           // dd($recentprev,$trip,$truckrows);

          }
      
        }

        Log::info('Finished Trip ID on', ['Truck' => $trip->Truck,  '#' => $trip->id]);

      // dd('done');

     
     }


     public function TripSummary()
      {
       
       ini_set('max_execution_time', 360000000000); // 3600 seconds = 60 minutes
       set_time_limit(360000000000);

       $truckData = DB::connection('mysql')->table('baselinetest')->groupBy('Truck')->orderBy('id')->get();
  
       foreach ($truckData as $truckCode => $rows) {

       Log::info('Started Trip Summary on', ['Truck' => $rows->Truck,  '#' => $truckCode]);

       $startDate = '2024-04-01'; // Replace with your start date
       $endDate = '2024-04-30'; // Replace with your end date

         // Convert to DateTime objects
         $startDateTime = new DateTime($startDate);
         $endDateTime = new DateTime($endDate);
   
       $trucks = DB::connection('mysql')->table('baselinetest')->where('Truck', '=', $rows->Truck)->where('LoadingTripClassificationv2', '!=', null)->orderBy('DateUpdated')->orderBy('Time')->get();
       //  dd($trucks);
       foreach ($trucks as  $truckrows => $trip) {
           
         if($trip->LoadingTripClassificationv2 == 'Offloading Trip'){
             
           $recentprev = DB::connection('mysql')->table('baselinetest')->where('Truck', '=', $rows->Truck)->where('TripClassificationv3' ,'=', 'Trip Start')->where('id','<', $trip->id)->orderBy('id', 'desc')->first();
            
           $currentRoute1 = DB::connection('mysql')->table('routes')->where('LoadingPoint', '=', $recentprev->GFupdated1)->where('OffloadingPoint','=', $trip->GFupdated1)->first();
   
           $createTrip = DB::connection('mysql')->table('tripsummary')->insert([
         
            'DateUpdated' => $trip->DateUpdated,
            'Truck' => $trip->Truck,
            'Time' => $trip->Time,
            'Distance' => $currentRoute1->Distance,
            'TruckType' => $trip->TruckType,
            'TripID' => $trip->TripID,
            'LoadingTripClassificationv2' => $trip->LoadingTripClassificationv2,
            'TripTimev2' => $trip->TripTimev2,
            'TripRoutev2' => $trip->TripRoutev2,
            'TonnesMoved' => $trip->TonnesMoved,
            'TotalDistance' => $trip->TotalDistance,
            'TotalFuelUsed' => $trip->TotalFuelUsed,
            'TotalConsumption' => $trip->TotalConsumption,
            'idlingFuelUsed' => $trip->idlingFuelUsed,
            'Labour' => $currentRoute1->LabourRate,
            'Rate' => $currentRoute1->Rate,
  
          ]);

         }else{

          
          $createTrip = DB::connection('mysql')->table('tripsummary')->insert([
         
            'DateUpdated' => $trip->DateUpdated,
            'Truck' => $trip->Truck,
            'Time' => $trip->Time,
           // 'Distance' => $trip->Distance,
            'TruckType' => $trip->TruckType,
            'TripID' => $trip->TripID,
            'LoadingTripClassificationv2' => $trip->LoadingTripClassificationv2,
            'TripTimev2' => $trip->TripTimev2,
            'TripRoutev2' => $trip->TripRoutev2,
            'TonnesMoved' => $trip->TonnesMoved,
           'TotalDistance' => $trip->TotalDistance,
           'TotalFuelUsed' => $trip->TotalFuelUsed,
           'TotalConsumption' => $trip->TotalConsumption,
          // 'idlingFuelUsed' => $trip->idlingFuelUsed,
           // 'Labour' => $trip->Labour,
           // 'Rate' => $trip->Rate,
  
          ]);
         }
       }

       Log::info('Finished Trip Summary on', ['Truck' => $rows->Truck,  '#' => $truckCode]);

      } 

      //dd('done');

    
  }


  public function TripDetail()
  {
   
   ini_set('max_execution_time', 360000000000); // 3600 seconds = 60 minutes
   set_time_limit(360000000000);

   $truckData = DB::connection('mysql')->table('baselinetest')->groupBy('Truck')->orderBy('id')->get();

   foreach ($truckData as $truckCode => $rows) {

   Log::info('Started Trip Detail on', ['Truck' => $rows->Truck,  '#' => $truckCode]);

   $trucks = DB::connection('mysql')->table('baselinetest')->where('Truck', '=', $rows->Truck)->orderBy('DateUpdated')->orderBy('Time')->get();
   //  dd($trucks);
   foreach ($trucks as  $truckrows => $trip) {            
      // $recentprev = DB::connection('mysql')->table('baselinetest')->where('Truck', '=', $rows->Truck)->where('TripClassificationv3' ,'=', 'Trip Start')->where('id','<', $trip->id)->orderBy('id', 'desc')->first();
        
     //  $currentRoute1 = DB::connection('mysql')->table('routes')->where('LoadingPoint', '=', $recentprev->GFupdated1)->where('OffloadingPoint','=', $trip->GFupdated1)->first();

       $createTrip = DB::connection('mysql')->table('tripdetail')->insert([
     
        'DateUpdated' => $trip->DateUpdated,
        'Truck' => $trip->Truck,
        'TruckType' => $trip->TruckType,
        'Time' => $trip->Time,
        'Latitude' => $trip->Latitude,
        'TruckType' => $trip->TruckType,
        'TripID' => $trip->TripID,
        'Longitude' => $trip->Longitude,
        'TimeDifference' => $trip->TimeDifference,
        'EventTime' => $trip->EventTime,
        'TripRoutev2' => $trip->TripRoutev2,
        'GFupdated1' => $trip->GFupdated1,
        'GFnew' => $trip->GFnew,
        'TripID' => $trip->TripID,
        'TripClassificationv7' => $trip->TripClassificationv7,
        'CumulativeTripClassification' => $trip->CumulativeTripClassification,
        'TimeSpentPercentage' => $trip->TimeSpentPercentage,
        'ShiftClassification' => $trip->ShiftClassification,
        'FuelClassification' => $trip->FuelClassification,

        'LineDistance' => $trip->LineTotalDistance,
        'LineFuelUsed' => $trip->LineTotalFuelUsed,
        'LineConsumption' => $trip->LineTotalConsumption,
        'FbCartrack' => $trip->FbCartrack,

      ]);


   }

   Log::info('Finished Trip Detail on', ['Truck' => $rows->Truck,  '#' => $truckCode]);

  } 

 // dd('done');


 }


  public function lineClassification()
  {
   
   ini_set('max_execution_time', 360000000000); // 3600 seconds = 60 minutes
   set_time_limit(360000000000);

   $truckData = DB::connection('mysql')->table('baselinetest')->groupBy('Truck')->orderBy('id')->get();

   foreach ($truckData as $truckCode => $rows) {

   Log::info('Started line classification on', ['Truck' => $rows->Truck,  '#' => $truckCode]);

   $trucks = DB::connection('mysql')->table('baselinetest')->where('Truck', '=', $rows->Truck)->where('id', '>', $rows->id+1)->orderBy('DateUpdated')->orderBy('Time')->get();
    // $trucks = DB::connection('mysql')->table('baselinetest')->where('Truck', '=', $rows->Truck)->where('id', '=', 12)->orderBy('DateUpdated')->orderBy('Time')->get();

   foreach ($trucks as  $truckrows => $trip) {

    /////////////////////////////trip start
   if($trip->TripClassificationv3 == 'Trip Start'){

    $tripUpdate = DB::connection('mysql')->table('baselinetest')->where('id', '=', $trip->id)->update([
 
      'TripClassificationv7' => 'Travel time (to loading)',

   ]); 

   $next1 = DB::connection('mysql')->table('baselinetest')->where('Truck', '=', $rows->Truck)->where('id', '=', $trip->id + 1)->first();   
   $next2 = DB::connection('mysql')->table('baselinetest')->where('Truck', '=', $rows->Truck)->where('id', '=', $trip->id + 2)->first(); 
   $next3 = DB::connection('mysql')->table('baselinetest')->where('Truck', '=', $rows->Truck)->where('id', '=', $trip->id + 3)->first(); 
   $next4 = DB::connection('mysql')->table('baselinetest')->where('Truck', '=', $rows->Truck)->where('id', '=', $trip->id + 4)->first(); 

   if($trip->GFupdated1 == $next1->GFupdated1){

       $tripUpdate = DB::connection('mysql')->table('baselinetest')->where('id', '=', $next1->id)->update([

      'TripClassificationv7' => 'Loading time',
 
      ]);  

   }
   
   if($trip->GFupdated1 == $next1->GFupdated1 && $trip->GFupdated1 == $next2->GFupdated1){

     $interval =  DB::connection('mysql')->table('baselinetest')->whereBetween('id', [$next1->id, $next2->id])
     ->where('Truck', '=', $rows->Truck)
     ->update([

       'TripClassificationv7' => 'Loading time',

     ]);

   }
   
   if($trip->GFupdated1 == $next1->GFupdated1 && $trip->GFupdated1 == $next2->GFupdated1 && $trip->GFupdated1 == $next3->GFupdated1){

     $interval =  DB::connection('mysql')->table('baselinetest')->whereBetween('id', [$next1->id, $next3->id])
     ->where('Truck', '=', $rows->Truck)
     ->update([

       'TripClassificationv7' => 'Loading time',
       
     ]);

   }
   if($trip->GFupdated1 == $next1->GFupdated1 && $trip->GFupdated1 == $next2->GFupdated1 && $trip->GFupdated1 == $next3->GFupdated1 && $trip->GFupdated1 == $next4->GFupdated1){

     $interval =  DB::connection('mysql')->table('baselinetest')->whereBetween('id', [$next1->id, $next4->id])
     ->where('Truck', '=', $rows->Truck)
     ->update([

       'TripClassificationv7' => 'Loading time',
       
     ]);

   }

   ///////////////////////////////not start and not end
   }elseif($trip->TripClassificationv3 != 'Trip Start' && $trip->TripClassificationv3 != 'Trip End'){

    $aboveTrip = DB::connection('mysql')->table('baselinetest')->where('Truck', '=', $rows->Truck)->where('id', '=', $trip->id - 1)->first();    
    $routeCheck = DB::connection('mysql')->table('routes')->where('OffloadingPoint', '=', $trip->GFupdated1)->first();
     if($aboveTrip != null){

     
    if($aboveTrip->GFupdated1 != $trip->GFupdated1 && $routeCheck == null){
    
      $tripUpdate = DB::connection('mysql')->table('baselinetest')->where('TripClassificationv7', '=', null)->where('id', '=', $trip->id)->update([
 
      'TripClassificationv7' => 'Travel time('.$aboveTrip->GFupdated1.' to '.$trip->GFupdated1.')',
  
     ]); 

    }

    if($aboveTrip->GFupdated1 != $trip->GFupdated1 &&  $routeCheck != null){

      $tripUpdate = DB::connection('mysql')->table('baselinetest')->where('TripClassificationv7', '=', null)->where('id', '=', $trip->id)->update([
 
      'TripClassificationv7' => 'Travel time (to offloading)',
  
     ]);
    }
     
     ///////////////////Inexplicable///////////////////   
    //  $next = DB::connection('mysql')->table('baselinetest')->where('Truck', '=', $rows->Truck)->where('id', '=', $trip->id + 1)->first(); 
    //  $prevrouteCheck = DB::connection('mysql')->table('routes')->where('loadingPoint', '=', $aboveTrip->GFupdated1)->orwhere('OffloadingPoint', '=', $aboveTrip->GFupdated1)->first();
    //  $routeCheck = DB::connection('mysql')->table('routes')->where('loadingPoint', '=', $trip->GFupdated1)->orwhere('OffloadingPoint', '=', $trip->GFupdated1)->first();
    //  $nextrouteCheck = DB::connection('mysql')->table('routes')->where('loadingPoint', '=', $next->GFupdated1)->orwhere('OffloadingPoint', '=', $next->GFupdated1)->first();

    //  if($aboveTrip->GFupdated1 == $trip->GFupdated1){

    //  }

    }
 
    //////////////////trip end
   }elseif($trip->TripClassificationv3 == 'Trip End'){

    $next1 = DB::connection('mysql')->table('baselinetest')->where('Truck', '=', $rows->Truck)->where('id', '=', $trip->id - 1)->first();   
    $next2 = DB::connection('mysql')->table('baselinetest')->where('Truck', '=', $rows->Truck)->where('id', '=', $trip->id - 2)->first(); 
    $next3 = DB::connection('mysql')->table('baselinetest')->where('Truck', '=', $rows->Truck)->where('id', '=', $trip->id - 3)->first(); 
    $next4 = DB::connection('mysql')->table('baselinetest')->where('Truck', '=', $rows->Truck)->where('id', '=', $trip->id - 4)->first(); 

    $next5 = DB::connection('mysql')->table('baselinetest')->where('Truck', '=', $rows->Truck)->where('id', '=', $trip->id - 5)->first();   
    $next6 = DB::connection('mysql')->table('baselinetest')->where('Truck', '=', $rows->Truck)->where('id', '=', $trip->id - 6)->first(); 
    $next7 = DB::connection('mysql')->table('baselinetest')->where('Truck', '=', $rows->Truck)->where('id', '=', $trip->id - 7)->first(); 
    $next8 = DB::connection('mysql')->table('baselinetest')->where('Truck', '=', $rows->Truck)->where('id', '=', $trip->id - 8)->first(); 

    $next9 = DB::connection('mysql')->table('baselinetest')->where('Truck', '=', $rows->Truck)->where('id', '=', $trip->id - 9)->first();   
    $next10 = DB::connection('mysql')->table('baselinetest')->where('Truck', '=', $rows->Truck)->where('id', '=', $trip->id - 10)->first(); 
    $next11 = DB::connection('mysql')->table('baselinetest')->where('Truck', '=', $rows->Truck)->where('id', '=', $trip->id - 11)->first(); 
    $next12 = DB::connection('mysql')->table('baselinetest')->where('Truck', '=', $rows->Truck)->where('id', '=', $trip->id - 12)->first(); 

    $next13 = DB::connection('mysql')->table('baselinetest')->where('Truck', '=', $rows->Truck)->where('id', '=', $trip->id - 13)->first();   
    $next14 = DB::connection('mysql')->table('baselinetest')->where('Truck', '=', $rows->Truck)->where('id', '=', $trip->id - 14)->first(); 
    $next15 = DB::connection('mysql')->table('baselinetest')->where('Truck', '=', $rows->Truck)->where('id', '=', $trip->id - 15)->first(); 
    $next16 = DB::connection('mysql')->table('baselinetest')->where('Truck', '=', $rows->Truck)->where('id', '=', $trip->id - 16)->first(); 

    $next17 = DB::connection('mysql')->table('baselinetest')->where('Truck', '=', $rows->Truck)->where('id', '=', $trip->id - 17)->first();   
    $next18 = DB::connection('mysql')->table('baselinetest')->where('Truck', '=', $rows->Truck)->where('id', '=', $trip->id - 18)->first(); 
    $next19 = DB::connection('mysql')->table('baselinetest')->where('Truck', '=', $rows->Truck)->where('id', '=', $trip->id - 19)->first(); 
    $next20 = DB::connection('mysql')->table('baselinetest')->where('Truck', '=', $rows->Truck)->where('id', '=', $trip->id - 20)->first(); 
     
    if($trip->GFupdated1 == $next1->GFupdated1){
  
      $interval =  DB::connection('mysql')->table('baselinetest')->whereBetween('id', [$next1->id, $trip->id])
      ->where('Truck', '=', $rows->Truck)
      ->where('TripClassificationv7', '=', null)
      ->update([
 
        'TripClassificationv7' => 'Offloading time',
 
      ]);
    
    }

    if($trip->GFupdated1 != $next1->GFupdated1){
  
      $interval =  DB::connection('mysql')->table('baselinetest')->where('id', $trip->id)
      ->where('Truck', '=', $rows->Truck)
      ->where('TripClassificationv7', '=', null)
      ->update([
 
        'TripClassificationv7' => 'Travel time',
 
      ]);
    
    }
    
    if($trip->GFupdated1 == $next1->GFupdated1 && $trip->GFupdated1 == $next2->GFupdated1){
 
      $interval =  DB::connection('mysql')->table('baselinetest')->whereBetween('id', [$next2->id, $trip->id])
      ->where('Truck', '=', $rows->Truck)
      ->where('TripClassificationv7', '=', null)
      ->update([
 
        'TripClassificationv7' => 'Offloading time',
 
      ]);
 
    }

    //3
    if($trip->GFupdated1 == $next1->GFupdated1 && $trip->GFupdated1 == $next2->GFupdated1 && $trip->GFupdated1 == $next3->GFupdated1){
 
      $interval =  DB::connection('mysql')->table('baselinetest')->whereBetween('id', [$next3->id, $trip->id])
      ->where('Truck', '=', $rows->Truck)
      ->where('TripClassificationv7', '=', null)
      ->update([
 
        'TripClassificationv7' => 'Offloading time',
        
      ]);
 
    }

   //4
    if($trip->GFupdated1 == $next1->GFupdated1 && $trip->GFupdated1 == $next2->GFupdated1 && $trip->GFupdated1 == $next3->GFupdated1 && $trip->GFupdated1 == $next4->GFupdated1){
 
      $interval =  DB::connection('mysql')->table('baselinetest')->whereBetween('id', [$next4->id, $trip->id])
      ->where('Truck', '=', $rows->Truck)
      ->where('TripClassificationv7', '=', null)
      ->update([
 
        'TripClassificationv7' => 'Offloading time',
        
      ]);
 
    }
     //5
     if($trip->GFupdated1 == $next1->GFupdated1 && $trip->GFupdated1 == $next2->GFupdated1 && $trip->GFupdated1 == $next3->GFupdated1 && $trip->GFupdated1 == $next4->GFupdated1
     && $trip->GFupdated1 == $next5->GFupdated1
     ){
 
      $interval =  DB::connection('mysql')->table('baselinetest')->whereBetween('id', [$next5->id, $trip->id])
      ->where('Truck', '=', $rows->Truck)
      ->where('TripClassificationv7', '=', null)
      ->update([
 
        'TripClassificationv7' => 'Offloading time',
        
      ]);
 
    }
     //6
     if($trip->GFupdated1 == $next1->GFupdated1 && $trip->GFupdated1 == $next2->GFupdated1 && $trip->GFupdated1 == $next3->GFupdated1 && $trip->GFupdated1 == $next4->GFupdated1
     && $trip->GFupdated1 == $next5->GFupdated1&& $trip->GFupdated1 == $next6->GFupdated1
     ){
 
      $interval =  DB::connection('mysql')->table('baselinetest')->whereBetween('id', [$next6->id, $trip->id])
      ->where('Truck', '=', $rows->Truck)
      ->where('TripClassificationv7', '=', null)
      ->update([
 
        'TripClassificationv7' => 'Offloading time',
        
      ]);
 
    }
     //7
     if($trip->GFupdated1 == $next1->GFupdated1 && $trip->GFupdated1 == $next2->GFupdated1 && $trip->GFupdated1 == $next3->GFupdated1 && $trip->GFupdated1 == $next4->GFupdated1
     && $trip->GFupdated1 == $next5->GFupdated1&& $trip->GFupdated1 == $next6->GFupdated1&& $trip->GFupdated1 == $next7->GFupdated1){
 
      $interval =  DB::connection('mysql')->table('baselinetest')->whereBetween('id', [$next7->id, $trip->id])
      ->where('Truck', '=', $rows->Truck)
      ->where('TripClassificationv7', '=', null)
      ->update([
 
        'TripClassificationv7' => 'Offloading time',
        
      ]);
 
    }
     //8
     if($trip->GFupdated1 == $next1->GFupdated1 && $trip->GFupdated1 == $next2->GFupdated1 && $trip->GFupdated1 == $next3->GFupdated1 && $trip->GFupdated1 == $next4->GFupdated1
     && $trip->GFupdated1 == $next5->GFupdated1&& $trip->GFupdated1 == $next6->GFupdated1&& $trip->GFupdated1 == $next7->GFupdated1&& $trip->GFupdated1 == $next8->GFupdated1){
 
      $interval =  DB::connection('mysql')->table('baselinetest')->whereBetween('id', [$next8->id, $trip->id])
      ->where('Truck', '=', $rows->Truck)
      ->where('TripClassificationv7', '=', null)
      ->update([
 
        'TripClassificationv7' => 'Offloading time',
        
      ]);
 
    }
     //9
     if($trip->GFupdated1 == $next1->GFupdated1 && $trip->GFupdated1 == $next2->GFupdated1 && $trip->GFupdated1 == $next3->GFupdated1 && $trip->GFupdated1 == $next4->GFupdated1&& $trip->GFupdated1 == $next5->GFupdated1
     && $trip->GFupdated1 == $next6->GFupdated1&& $trip->GFupdated1 == $next7->GFupdated1&& $trip->GFupdated1 == $next8->GFupdated1&& $trip->GFupdated1 == $next9->GFupdated1){
 
      $interval =  DB::connection('mysql')->table('baselinetest')->whereBetween('id', [$next9->id, $trip->id])
      ->where('Truck', '=', $rows->Truck)
      ->where('TripClassificationv7', '=', null)
      ->update([
 
        'TripClassificationv7' => 'Offloading time',
        
      ]);
 
    }
     //10
     if($trip->GFupdated1 == $next1->GFupdated1 && $trip->GFupdated1 == $next2->GFupdated1 && $trip->GFupdated1 == $next3->GFupdated1 && $trip->GFupdated1 == $next4->GFupdated1&& $trip->GFupdated1 == $next5->GFupdated1
     && $trip->GFupdated1 == $next6->GFupdated1&& $trip->GFupdated1 == $next7->GFupdated1&& $trip->GFupdated1 == $next8->GFupdated1&& $trip->GFupdated1 == $next9->GFupdated1&& $trip->GFupdated1 == $next10->GFupdated1){
 
      $interval =  DB::connection('mysql')->table('baselinetest')->whereBetween('id', [$next10->id, $trip->id])
      ->where('Truck', '=', $rows->Truck)
      ->where('TripClassificationv7', '=', null)
      ->update([
 
        'TripClassificationv7' => 'Offloading time',
        
      ]);
 
    }
     //11
     if($trip->GFupdated1 == $next1->GFupdated1 && $trip->GFupdated1 == $next2->GFupdated1 && $trip->GFupdated1 == $next3->GFupdated1 && $trip->GFupdated1 == $next4->GFupdated1&& $trip->GFupdated1 == $next5->GFupdated1
     && $trip->GFupdated1 == $next6->GFupdated1&& $trip->GFupdated1 == $next7->GFupdated1&& $trip->GFupdated1 == $next8->GFupdated1&& $trip->GFupdated1 == $next9->GFupdated1&& $trip->GFupdated1 == $next10->GFupdated1&& $trip->GFupdated1 == $next11->GFupdated1){
      $interval =  DB::connection('mysql')->table('baselinetest')->whereBetween('id', [$next11->id, $trip->id])
      ->where('Truck', '=', $rows->Truck)
      ->where('TripClassificationv7', '=', null)
      ->update([
 
        'TripClassificationv7' => 'Offloading time',
        
      ]);
 
    }
     //12
     if($trip->GFupdated1 == $next1->GFupdated1 && $trip->GFupdated1 == $next2->GFupdated1 && $trip->GFupdated1 == $next3->GFupdated1 && $trip->GFupdated1 == $next4->GFupdated1&& $trip->GFupdated1 == $next5->GFupdated1
     && $trip->GFupdated1 == $next6->GFupdated1&& $trip->GFupdated1 == $next7->GFupdated1&& $trip->GFupdated1 == $next8->GFupdated1&& $trip->GFupdated1 == $next9->GFupdated1&& $trip->GFupdated1 == $next10->GFupdated1
     && $trip->GFupdated1 == $next11->GFupdated1&& $trip->GFupdated1 == $next12->GFupdated1){
 
      $interval =  DB::connection('mysql')->table('baselinetest')->whereBetween('id', [$next12->id, $trip->id])
      ->where('Truck', '=', $rows->Truck)
      ->where('TripClassificationv7', '=', null)
      ->update([
 
        'TripClassificationv7' => 'Offloading time',
        
      ]);
 
    }
     //13
     if($trip->GFupdated1 == $next1->GFupdated1 && $trip->GFupdated1 == $next2->GFupdated1 && $trip->GFupdated1 == $next3->GFupdated1 && $trip->GFupdated1 == $next4->GFupdated1&& $trip->GFupdated1 == $next5->GFupdated1
     && $trip->GFupdated1 == $next6->GFupdated1&& $trip->GFupdated1 == $next7->GFupdated1&& $trip->GFupdated1 == $next8->GFupdated1&& $trip->GFupdated1 == $next9->GFupdated1&& $trip->GFupdated1 == $next10->GFupdated1
     && $trip->GFupdated1 == $next11->GFupdated1&& $trip->GFupdated1 == $next12->GFupdated1&& $trip->GFupdated1 == $next13->GFupdated1){
 
      $interval =  DB::connection('mysql')->table('baselinetest')->whereBetween('id', [$next13->id, $trip->id])
      ->where('Truck', '=', $rows->Truck)
      ->where('TripClassificationv7', '=', null)
      ->update([
 
        'TripClassificationv7' => 'Offloading time',
        
      ]);
 
    }
     //14
     if($trip->GFupdated1 == $next1->GFupdated1 && $trip->GFupdated1 == $next2->GFupdated1 && $trip->GFupdated1 == $next3->GFupdated1 && $trip->GFupdated1 == $next4->GFupdated1&& $trip->GFupdated1 == $next5->GFupdated1
     && $trip->GFupdated1 == $next6->GFupdated1&& $trip->GFupdated1 == $next7->GFupdated1&& $trip->GFupdated1 == $next8->GFupdated1&& $trip->GFupdated1 == $next9->GFupdated1&& $trip->GFupdated1 == $next10->GFupdated1
     && $trip->GFupdated1 == $next11->GFupdated1&& $trip->GFupdated1 == $next12->GFupdated1&& $trip->GFupdated1 == $next13->GFupdated1&& $trip->GFupdated1 == $next14->GFupdated1){
 
      $interval =  DB::connection('mysql')->table('baselinetest')->whereBetween('id', [$next14->id, $trip->id])
      ->where('Truck', '=', $rows->Truck)
      ->where('TripClassificationv7', '=', null)
      ->update([
 
        'TripClassificationv7' => 'Offloading time',
        
      ]);
 
      }
      //15
      if($trip->GFupdated1 == $next1->GFupdated1 && $trip->GFupdated1 == $next2->GFupdated1 && $trip->GFupdated1 == $next3->GFupdated1 && $trip->GFupdated1 == $next4->GFupdated1&& $trip->GFupdated1 == $next5->GFupdated1
      && $trip->GFupdated1 == $next6->GFupdated1&& $trip->GFupdated1 == $next7->GFupdated1&& $trip->GFupdated1 == $next8->GFupdated1&& $trip->GFupdated1 == $next9->GFupdated1&& $trip->GFupdated1 == $next10->GFupdated1
      && $trip->GFupdated1 == $next11->GFupdated1&& $trip->GFupdated1 == $next12->GFupdated1&& $trip->GFupdated1 == $next13->GFupdated1&& $trip->GFupdated1 == $next14->GFupdated1&& $trip->GFupdated1 == $next15->GFupdated1){
 
        $interval =  DB::connection('mysql')->table('baselinetest')->whereBetween('id', [$next15->id, $trip->id])
        ->where('Truck', '=', $rows->Truck)
        ->where('TripClassificationv7', '=', null)
        ->update([
   
          'TripClassificationv7' => 'Offloading time',
          
        ]);
   
      }
        //16
        if($trip->GFupdated1 == $next1->GFupdated1 && $trip->GFupdated1 == $next2->GFupdated1 && $trip->GFupdated1 == $next3->GFupdated1 && $trip->GFupdated1 == $next4->GFupdated1&& $trip->GFupdated1 == $next5->GFupdated1
        && $trip->GFupdated1 == $next6->GFupdated1&& $trip->GFupdated1 == $next7->GFupdated1&& $trip->GFupdated1 == $next8->GFupdated1&& $trip->GFupdated1 == $next9->GFupdated1&& $trip->GFupdated1 == $next10->GFupdated1
        && $trip->GFupdated1 == $next11->GFupdated1&& $trip->GFupdated1 == $next12->GFupdated1&& $trip->GFupdated1 == $next13->GFupdated1&& $trip->GFupdated1 == $next14->GFupdated1&& $trip->GFupdated1 == $next15->GFupdated1
        && $trip->GFupdated1 == $next16->GFupdated1){
 
      $interval =  DB::connection('mysql')->table('baselinetest')->whereBetween('id', [$next16->id, $trip->id])
      ->where('Truck', '=', $rows->Truck)
      ->where('TripClassificationv7', '=', null)
      ->update([
 
        'TripClassificationv7' => 'Offloading time',
        
      ]);
 
    }

         //17
         if($trip->GFupdated1 == $next1->GFupdated1 && $trip->GFupdated1 == $next2->GFupdated1 && $trip->GFupdated1 == $next3->GFupdated1 && $trip->GFupdated1 == $next4->GFupdated1&& $trip->GFupdated1 == $next5->GFupdated1
         && $trip->GFupdated1 == $next6->GFupdated1&& $trip->GFupdated1 == $next7->GFupdated1&& $trip->GFupdated1 == $next8->GFupdated1&& $trip->GFupdated1 == $next9->GFupdated1&& $trip->GFupdated1 == $next10->GFupdated1
         && $trip->GFupdated1 == $next11->GFupdated1&& $trip->GFupdated1 == $next12->GFupdated1&& $trip->GFupdated1 == $next13->GFupdated1&& $trip->GFupdated1 == $next14->GFupdated1&& $trip->GFupdated1 == $next15->GFupdated1
         && $trip->GFupdated1 == $next16->GFupdated1&& $trip->GFupdated1 == $next17->GFupdated1){
  
       $interval =  DB::connection('mysql')->table('baselinetest')->whereBetween('id', [$next17->id, $trip->id])
       ->where('Truck', '=', $rows->Truck)
       ->where('TripClassificationv7', '=', null)
       ->update([
  
         'TripClassificationv7' => 'Offloading time',
         
       ]);
  
     }


      //18
      if($trip->GFupdated1 == $next1->GFupdated1 && $trip->GFupdated1 == $next2->GFupdated1 && $trip->GFupdated1 == $next3->GFupdated1 && $trip->GFupdated1 == $next4->GFupdated1&& $trip->GFupdated1 == $next5->GFupdated1
      && $trip->GFupdated1 == $next6->GFupdated1&& $trip->GFupdated1 == $next7->GFupdated1&& $trip->GFupdated1 == $next8->GFupdated1&& $trip->GFupdated1 == $next9->GFupdated1&& $trip->GFupdated1 == $next10->GFupdated1
      && $trip->GFupdated1 == $next11->GFupdated1&& $trip->GFupdated1 == $next12->GFupdated1&& $trip->GFupdated1 == $next13->GFupdated1&& $trip->GFupdated1 == $next14->GFupdated1&& $trip->GFupdated1 == $next15->GFupdated1
      && $trip->GFupdated1 == $next16->GFupdated1&& $trip->GFupdated1 == $next17->GFupdated1&& $trip->GFupdated1 == $next18->GFupdated1){

    $interval =  DB::connection('mysql')->table('baselinetest')->whereBetween('id', [$next18->id, $trip->id])
    ->where('Truck', '=', $rows->Truck)
    ->where('TripClassificationv7', '=', null)
    ->update([

      'TripClassificationv7' => 'Offloading time',
      
    ]);

    }

      //19
      if($trip->GFupdated1 == $next1->GFupdated1 && $trip->GFupdated1 == $next2->GFupdated1 && $trip->GFupdated1 == $next3->GFupdated1 && $trip->GFupdated1 == $next4->GFupdated1&& $trip->GFupdated1 == $next5->GFupdated1
      && $trip->GFupdated1 == $next6->GFupdated1&& $trip->GFupdated1 == $next7->GFupdated1&& $trip->GFupdated1 == $next8->GFupdated1&& $trip->GFupdated1 == $next9->GFupdated1&& $trip->GFupdated1 == $next10->GFupdated1
      && $trip->GFupdated1 == $next11->GFupdated1&& $trip->GFupdated1 == $next12->GFupdated1&& $trip->GFupdated1 == $next13->GFupdated1&& $trip->GFupdated1 == $next14->GFupdated1&& $trip->GFupdated1 == $next15->GFupdated1
      && $trip->GFupdated1 == $next16->GFupdated1&& $trip->GFupdated1 == $next17->GFupdated1&& $trip->GFupdated1 == $next18->GFupdated1&& $trip->GFupdated1 == $next19->GFupdated1){

    $interval =  DB::connection('mysql')->table('baselinetest')->whereBetween('id', [$next19->id, $trip->id])
    ->where('Truck', '=', $rows->Truck)
    ->where('TripClassificationv7', '=', null)
    ->update([

      'TripClassificationv7' => 'Offloading time',
      
    ]);

    }

      //19
      if($trip->GFupdated1 == $next1->GFupdated1 && $trip->GFupdated1 == $next2->GFupdated1 && $trip->GFupdated1 == $next3->GFupdated1 && $trip->GFupdated1 == $next4->GFupdated1&& $trip->GFupdated1 == $next5->GFupdated1
      && $trip->GFupdated1 == $next6->GFupdated1&& $trip->GFupdated1 == $next7->GFupdated1&& $trip->GFupdated1 == $next8->GFupdated1&& $trip->GFupdated1 == $next9->GFupdated1&& $trip->GFupdated1 == $next10->GFupdated1
      && $trip->GFupdated1 == $next11->GFupdated1&& $trip->GFupdated1 == $next12->GFupdated1&& $trip->GFupdated1 == $next13->GFupdated1&& $trip->GFupdated1 == $next14->GFupdated1&& $trip->GFupdated1 == $next15->GFupdated1
      && $trip->GFupdated1 == $next16->GFupdated1&& $trip->GFupdated1 == $next17->GFupdated1&& $trip->GFupdated1 == $next18->GFupdated1&& $trip->GFupdated1 == $next19->GFupdated1&& $trip->GFupdated1 == $next20->GFupdated1){

    $interval =  DB::connection('mysql')->table('baselinetest')->whereBetween('id', [$next20->id, $trip->id])
    ->where('Truck', '=', $rows->Truck)
    ->where('TripClassificationv7', '=', null)
    ->update([

      'TripClassificationv7' => 'Offloading time',
      
    ]);

    }


   }else{

   }

  
    }

   Log::info('Finished  line classification on', ['Truck' => $rows->Truck,  '#' => $truckCode]);

    } 

  //  dd('done');


  }


public function lineclassificationV2()
{
       
       ini_set('max_execution_time', 360000000000); // 3600 seconds = 60 minutes
       set_time_limit(360000000000);

       $truckData = DB::connection('mysql')->table('baselinetest')->groupBy('Truck')->orderBy('id')->get();
  
       foreach ($truckData as $truckCode => $rows) {

       Log::info('Started line class v2 on', ['Truck' => $rows->Truck,  '#' => $truckCode]);

       $startDate = '2024-04-01'; // Replace with your start date
       $endDate = '2024-04-30'; // Replace with your end date

         // Convert to DateTime objects
         $startDateTime = new DateTime($startDate);
         $endDateTime = new DateTime($endDate);
   
       $trucks = DB::connection('mysql')->table('baselinetest')->where('Truck', '=', $rows->Truck)->whereBetween('DateUpdated', [$startDateTime, $endDateTime])->where('id', '>', $rows->id+1)->orderBy('DateUpdated')->orderBy('Time')->get();
       //  dd($trucks);
       foreach ($trucks as  $truckrows => $trip) {

     if($trip->TripClassificationv3 != 'Trip Start' && $trip->TripClassificationv3 != 'Trip End'){

    $aboveTrip = DB::connection('mysql')->table('baselinetest')->where('Truck', '=', $rows->Truck)->where('id', '=', $trip->id - 1)->first();    
    $routeCheck = DB::connection('mysql')->table('routes')->where('OffloadingPoint', '=', $trip->GFupdated1)->first();
      
     ///////////////////Inexplicable///////////////////   
     $next = DB::connection('mysql')->table('baselinetest')->where('Truck', '=', $rows->Truck)->where('id', '=', $trip->id + 1)->first(); 
     $routeCheck = DB::connection('mysql')->table('routes')->where('loadingPoint', '=', $trip->GFupdated1)->orwhere('OffloadingPoint', '=', $trip->GFupdated1)->first();
     $classcheck = DB::connection('mysql')->table('classifications')->where('Name', '=', $trip->GFupdated1)->first();
     // dd($classcheck);
      if($aboveTrip != null){
     if($aboveTrip->GFupdated1 == $trip->GFupdated1 && $routeCheck != null){
     
      $update =  DB::connection('mysql')->table('baselinetest')->where('id', $trip->id)
      ->where('Truck', '=', $rows->Truck)
      ->where('TripClassificationv7', '=', null)
      ->update([
 
        'TripClassificationv7' => 'Inexplicable loading/offloading time',
 
      ]);
    
        
     }

     if($aboveTrip->GFupdated1 == $trip->GFupdated1 && $classcheck != null){
     
      $update =  DB::connection('mysql')->table('baselinetest')->where('id', $trip->id)
      ->where('Truck', '=', $rows->Truck)
      ->where('TripClassificationv7', '=', null)
      ->update([
 
        'TripClassificationv7' => ' At '.$classcheck->Classification.'',
 
      ]);
         
     }

     if($aboveTrip->GFupdated1 == $trip->GFupdated1 && $classcheck == null && $routeCheck == null){
     
      $update =  DB::connection('mysql')->table('baselinetest')->where('id', $trip->id)
      ->where('Truck', '=', $rows->Truck)
      ->where('TripClassificationv7', '=', null)
      ->update([
 
        'TripClassificationv7' => 'Unauthorized stop at '.$trip->GFupdated1.'',
 
      ]);
           
     }

    }
   
 
    //////////////////trip end
   }


       }

       Log::info('Finished line class v2 on', ['Truck' => $rows->Truck,  '#' => $truckCode]);

      } 

    //  dd('done');

    
    }



}

