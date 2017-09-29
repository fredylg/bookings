<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\GoogleCalendar\Event;
use App\MeetingsModel;
use Carbon\Carbon;

class CalendarController extends Controller
{
    //
    public $domain = 'http://bookings.stwgroup.net.au';

    public function GetCalendar( )
    {
      // get all future events on a calendar
      $events = Event::get();
      $control_events = [];
      $googleMeetings = [];
      $databaseMeetings = [];

      //Get google meetings
      foreach ($events as $key => $event) {
          $googleMeetings[$key] = $event->id;
      }

      //check for removed events from google
      $existingMeetingsObj =  MeetingsModel::All(['meetingId'])->toArray();
      foreach ($existingMeetingsObj as $key => $item) {
         $databaseMeetings[] = $item["meetingId"];
      }
      $removedMeetings = array_diff( $databaseMeetings , $googleMeetings );
      if(sizeof($removedMeetings) > 0){
        foreach ($removedMeetings as $key => $eventId) {
          $event = Event::find($eventId);
          $event->delete();
        }
      }
      //create new events
      $newMeetings = array_diff( $googleMeetings , $databaseMeetings );
      if(sizeof($newMeetings) > 0){
        foreach ($newMeetings as $key => $eventId) {
          $event = Event::find($eventId);
          $eventDate = new Carbon($event->start->dateTime);
          $eventEndDate = new Carbon($event->end->dateTime);
          $eventLenth = $eventEndDate->diffInHours($eventDate);
          $url = $this->generateCreateURL($event->summary,$event->description,$eventDate->day,$eventDate->month,$eventDate->year,$eventDate->hour,$eventDate->minute,$eventDate->format('A'),$eventLenth,'hours');
          $res = file_get_contents($url);
          if (strpos($res, 'Scheduling Conflict') !== false) {
              echo '<br/> room booked failed '.$eventId;
          }else{
            echo '<br/> room booked '.$eventId;
          }
        }
      }
      dd('done');


      //update events into db
      foreach ($events as $key => $event) {
          $meeting = MeetingsModel::updateOrCreate(
              ['roomName' => 'FrontRoom','attendees' => json_encode($event->attendees), 'summary' =>   $event->summary,'location' =>  $event->location ?  $event->location : 'Front Room' ,'status' => $event->status ],
              ['meetingId' =>   $event->id ]
          );
          $googleMeetings[] = $event->id;
      }


      dd($diff);

    }

    private function generateCreateURL($title,$description,$day,$month,$year,$hour,$minute,$ampm,$duration,$duration_unit){
      $string = $this->domain.'/edit_entry_handler.php?name='.
                urlencode($title).'&description='.urlencode($description).
                '&day='.urlencode($day).'&month='.urlencode($month).
                '&year='.urlencode($year).
                '&hour='.urlencode($hour).
                '&minute='.urlencode($hour).
                '&ampm='.urlencode($ampm).
                '&duration='.urlencode($duration).
                '&dur_units='.urlencode($duration_unit).
                '&area=63'.
                '&rooms%5B%5D=240&'.
                'type=I'.
                '&create_by=10.161.12.60';
                //'&rep_type=0&rep_end_day=27&rep_end_month=9&rep_end_year=2017&rep_num_weeks=&returl=http%3A%2F%2Fbookings.stwgroup.net.au%2Fday.php%3Fyear%3D2017%26month%3D09%26day%3D27%26area%3D63&create_by=10.161.12.60&rep_id=0&edit_type=series'

      return $string;
    }

    private function generateDeleteUrl($id){
      $string = $this->domain.'/del_entry.php?id='.$id.'&series=0';
      return $string;
  }
}
