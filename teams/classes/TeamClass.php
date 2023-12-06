<?php

class TeamClass {

    // Private variables for team meeting 
    private $TenantID;
    private $ClientID;
    private $ClientSecret;
    private $UserId;
    private $base_url;
    private $base_url_auth;
    private $scope;
    private $username;
    private $password;
    private $grant_type;


    // Generate auth token
    public function __construct() {

        $this->ClientID = get_config('mod_teams','ClientID');
        $this->ClientSecret = get_config('mod_teams','ClientSecret');
        $this->UserId = get_config('mod_teams','UserId');
        $this->base_url = get_config('mod_teams','base_url');
        $this->base_url_auth = get_config('mod_teams','base_url_auth');
        $this->scope = get_config('mod_teams','scope');
        $this->username = get_config('mod_teams','username');
        $this->password = get_config('mod_teams','password');
        $this->grant_type = get_config('mod_teams','grant_type');

        $requestdata = 'client_id='.$this->ClientID.'&scope='.$this->scope.'&client_secret='.$this->ClientSecret.'&username='.$this->username.'&password='.$this->password.'&grant_type='.$this->grant_type;

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->base_url_auth.'token',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $requestdata,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/x-www-form-urlencoded'
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        $json_decode =  json_decode($response);
        return $json_decode->access_token;
    }




    // Create team in team 
    public function create_team(object $args) {

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->base_url.'teams/',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS =>'{
            "template@odata.bind": "https://graph.microsoft.com/v1.0/teamsTemplates(\'standard\')",
            "displayName": "'.$args->displayName.'",
            "description": "'.$args->description.'"
            }',
            CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json',
            'Authorization: Bearer '.self::__construct()
            ),
        ));
        
        $response = curl_exec($curl);
        curl_close($curl);
        return json_encode($response);
    }




    // Create team meeting with calendar events
    public function create_meeting(object $args) {

        $requestdata = new stdClass();
        $requestdata->subject=$args->subjectof;
        $requestdata->body=array("contentType"=> "HTML", "content"=> strip_tags($args->intro));
        $requestdata->start=array("dateTime"=> $args->start_datetime, "timeZone"=> $args->start_timezone);
        $requestdata->end=array("dateTime"=> $args->end_datetime, "timeZone"=> $args->end_timezone);
        $requestdata->location=array("displayName"=> $args->location);
        $requestdata->attendees=[array("emailAddress" => array("address"=> "$args->attendees_email", "name"=> "$args->attendees_name"), "type"=> "required")];
        $requestdata->allowNewTimeProposals=true;
        $requestdata->isOnlineMeeting=$args->isonlinemeeting;
        $requestdata->onlineMeetingProvider="teamsForBusiness";
        $requestdata->hideAttendees=$args->hideattendees;
        $requestdata->importance=$args->importance;
        $requestdata->isAllDay=$args->isallday;
        $requestdata->isReminderOn=$args->isreminderon;
        $requestdata->reminderMinutesBeforeStart=$args->reminderminutesbeforestart;
        $requestdata->sensitivity=$args->sensitivity;
        
        // var_dump($requestdata);

        // Recurring type
        $recurring = $args -> recurring;
        $repeat_interval = $args -> repeat_interval;
        $recurrence_type = $args -> recurrence_type;

        // Weekly recurrence
        $weekly_days_sunday = $args -> weekly_days_sunday;
        $weekly_days_monday = $args -> weekly_days_monday;
        $weekly_days_tuesday = $args -> weekly_days_tuesday;
        $weekly_days_wednesday = $args -> weekly_days_wednesday;
        $weekly_days_thursday = $args -> weekly_days_thursday;
        $weekly_days_friday = $args -> weekly_days_friday;
        $weekly_days_saturday = $args -> weekly_days_saturday;

        $daysOfWeek = array_values(array_filter(array($weekly_days_sunday, $weekly_days_monday, $weekly_days_tuesday, $weekly_days_wednesday, $weekly_days_thursday, $weekly_days_friday, $weekly_days_saturday)));


        // Monthly recurrence
        $monthly_day = $args -> monthly_day;
        $monthly_repeat_option = $args -> monthly_repeat_option;
        $monthly_week = $args -> monthly_week;
        $monthly_week_day = $args -> monthly_week_day;

        // Yearly recurrence
        $yearly_monthly_repeat_option = $args -> yearly_monthly_repeat_option;
        $yearly_month = $args -> yearly_month;
        $yearly_monthly_day = $args -> yearly_monthly_day;
        $yearly_monthly_week = $args -> yearly_monthly_week;
        $yearly_monthly_week_day = $args -> yearly_monthly_week_day;
        $yearly_monthly = $args -> yearly_monthly;

        // Endate for range and occerence
        $end_date_option = $args -> end_date_option;

        if($end_date_option == 1){
            $range_type = 'endDate';
        }elseif($end_date_option == 2){
            $range_type = 'numbered';
        }else{
            $range_type = 'noEnd';
        }
         

        $end_date_time = $args -> end_date_time == null ? "0001-01-01" : date("Y-m-d", $args -> end_date_time);
        $occurrences = $args -> occurrences == null ? 0 : (int)$args -> occurrences;

        // Start date of meeting
        $startDate = date("Y-m-d",strtotime($args->start_datetime));
        $repeat_interval = (int)$repeat_interval;

        if($recurring == 1){
            
            if($recurrence_type == 1){ 
                $requestdata->recurrence=array("pattern"=>array("type"=>"daily", "interval"=>$repeat_interval), 
                "range"=> array("type"=>"$range_type", "startDate"=>"$startDate", "endDate"=>"$end_date_time", "numberOfOccurrences"=>$occurrences, "recurrenceTimeZone"=> "$args->prefer"));   
            }if($recurrence_type == 2){
                $requestdata->recurrence=array("pattern"=>array("type"=>"weekly", "interval"=>$repeat_interval, "daysOfWeek" =>$daysOfWeek, "firstDayOfWeek"=>"sunday"), 
                "range"=> array("type"=>"$range_type", "startDate"=>"$startDate", "endDate"=>"$end_date_time", "numberOfOccurrences"=>$occurrences, "recurrenceTimeZone"=> "$args->prefer"));  
            }if($recurrence_type == 3){
                    if($monthly_repeat_option == 1){
                        $requestdata->recurrence=array("pattern"=>array("type"=>"absoluteMonthly", "interval"=>$repeat_interval, "dayOfMonth"=> (int)$monthly_day), 
                        "range"=> array("type"=>"$range_type", "startDate"=>"$startDate", "endDate"=>"$end_date_time", "numberOfOccurrences"=>$occurrences, "recurrenceTimeZone"=> "$args->prefer")); 
                    }if($monthly_repeat_option == 2){
                        $requestdata->recurrence=array("pattern"=>array("type"=>"relativeMonthly", "interval"=>$repeat_interval, "daysOfWeek"=> array("$monthly_week_day"), "index"=> "$monthly_week"), 
                        "range"=> array("type"=>"$range_type", "startDate"=>"$startDate", "endDate"=>"$end_date_time", "numberOfOccurrences"=>$occurrences, "recurrenceTimeZone"=> "$args->prefer")); 
                    }
            }if($recurrence_type == 4){
                    if($yearly_monthly_repeat_option == 1){
                        $requestdata->recurrence=array("pattern"=>array("type"=>"absoluteYearly", "interval"=>$repeat_interval, "dayOfMonth"=> (int)$yearly_monthly_day, "month"=> (int)$yearly_month), 
                        "range"=> array("type"=>"$range_type", "startDate"=>"$startDate", "endDate"=>"$end_date_time", "numberOfOccurrences"=>$occurrences, "recurrenceTimeZone"=> "$args->prefer")); 
                    }if($yearly_monthly_repeat_option == 2){
                        $requestdata->recurrence=array("pattern"=>array("type"=>"relativeYearly", "interval"=>$repeat_interval, "daysOfWeek"=> array("$yearly_monthly_week_day"), "month"=> (int)$yearly_monthly, "index"=>"$yearly_monthly_week"), 
                        "range"=> array("type"=>"$range_type", "startDate"=>"$startDate", "endDate"=>"$end_date_time", "numberOfOccurrences"=>$occurrences, "recurrenceTimeZone"=> "$args->prefer")); 
                    }
            }
        }else{
            $requestdata->recurrence=null;   
        }
        
        // echo  $this->base_url.'users/'.$this->UserId.'/events';
        // echo "<pre>";
        // echo self::__construct();
        // print_r(json_encode($requestdata));
        // die;

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->base_url.'users/'.$this->UserId.'/events',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS =>json_encode($requestdata),
            CURLOPT_HTTPHEADER => array(
            'Prefer: team.timezone="'.$args->prefer.'"',
            'Content-type: application/json',
            'Authorization: Bearer '.self::__construct()
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        // var_dump($response);
        return $response;
    }




    // Create team meeting with calendar events
    public function update_meeting(object $args) {

        $requestdata = new stdClass();
        $requestdata->subject=$args->subjectof;
        $requestdata->body=array("contentType"=> "HTML", "content"=> strip_tags($args->intro));
        $requestdata->start=array("dateTime"=> $args->start_datetime, "timeZone"=> $args->start_timezone);
        $requestdata->end=array("dateTime"=> $args->end_datetime, "timeZone"=> $args->end_timezone);
        $requestdata->location=array("displayName"=> $args->location);
        $requestdata->attendees=[array("emailAddress" => array("address"=> "$args->attendees_email", "name"=> "$args->attendees_name"), "type"=> "required")];
        $requestdata->allowNewTimeProposals=true;

        $requestdata->isOnlineMeeting= $args->isonlinemeeting == 1 ? true : false;
        
        $requestdata->onlineMeetingProvider="teamsForBusiness";
        $requestdata->hideAttendees=$args->hideattendees == 1 ? true : false;
        $requestdata->importance=$args->importance;
        $requestdata->isAllDay=$args->isallday  == 1 ? true : false;
        $requestdata->isReminderOn=$args->isreminderon  == 1 ? true : false;
        $requestdata->reminderMinutesBeforeStart=$args->reminderminutesbeforestart;
        $requestdata->sensitivity=$args->sensitivity;

        // echo "<pre>";
        // var_dump($requestdata->isOnlineMeeting);
        // die;

        // Recurring type
        $recurring = $args -> recurring;
        $repeat_interval = $args -> repeat_interval;
        $recurrence_type = $args -> recurrence_type;

        // Weekly recurrence
        $weekly_days_sunday = $args -> weekly_days_sunday;
        $weekly_days_monday = $args -> weekly_days_monday;
        $weekly_days_tuesday = $args -> weekly_days_tuesday;
        $weekly_days_wednesday = $args -> weekly_days_wednesday;
        $weekly_days_thursday = $args -> weekly_days_thursday;
        $weekly_days_friday = $args -> weekly_days_friday;
        $weekly_days_saturday = $args -> weekly_days_saturday;

        $daysOfWeek = array_values(array_filter(array($weekly_days_sunday, $weekly_days_monday, $weekly_days_tuesday, $weekly_days_wednesday, $weekly_days_thursday, $weekly_days_friday, $weekly_days_saturday)));


        // Monthly recurrence
        $monthly_day = $args -> monthly_day;
        $monthly_repeat_option = $args -> monthly_repeat_option;
        $monthly_week = $args -> monthly_week;
        $monthly_week_day = $args -> monthly_week_day;

        // Yearly recurrence
        $yearly_monthly_repeat_option = $args -> yearly_monthly_repeat_option;
        $yearly_month = $args -> yearly_month;
        $yearly_monthly_day = $args -> yearly_monthly_day;
        $yearly_monthly_week = $args -> yearly_monthly_week;
        $yearly_monthly_week_day = $args -> yearly_monthly_week_day;
        $yearly_monthly = $args -> yearly_monthly;

        // Endate for range and occerence
        $end_date_option = $args -> end_date_option;

        if($end_date_option == 1){
            $range_type = 'endDate';
        }elseif($end_date_option == 2){
            $range_type = 'numbered';
        }else{
            $range_type = 'noEnd';
        }
         

        $end_date_time = $args -> end_date_time == null ? "0001-01-01" : date("Y-m-d", $args -> end_date_time);
        $occurrences = $args -> occurrences == null ? 0 : (int)$args -> occurrences;

        // Start date of meeting
        $startDate = date("Y-m-d",strtotime($args->start_datetime));
        $repeat_interval = (int)$repeat_interval;

        if($recurring == 1){
            
            if($recurrence_type == 1){ 
                $requestdata->recurrence=array("pattern"=>array("type"=>"daily", "interval"=>$repeat_interval), 
                "range"=> array("type"=>"$range_type", "startDate"=>"$startDate", "endDate"=>"$end_date_time", "numberOfOccurrences"=>$occurrences, "recurrenceTimeZone"=> "$args->prefer"));   
            }if($recurrence_type == 2){
                $requestdata->recurrence=array("pattern"=>array("type"=>"weekly", "interval"=>$repeat_interval, "daysOfWeek" =>$daysOfWeek, "firstDayOfWeek"=>"sunday"), 
                "range"=> array("type"=>"$range_type", "startDate"=>"$startDate", "endDate"=>"$end_date_time", "numberOfOccurrences"=>$occurrences, "recurrenceTimeZone"=> "$args->prefer"));  
            }if($recurrence_type == 3){
                    if($monthly_repeat_option == 1){
                        $requestdata->recurrence=array("pattern"=>array("type"=>"absoluteMonthly", "interval"=>$repeat_interval, "dayOfMonth"=> (int)$monthly_day), 
                        "range"=> array("type"=>"$range_type", "startDate"=>"$startDate", "endDate"=>"$end_date_time", "numberOfOccurrences"=>$occurrences, "recurrenceTimeZone"=> "$args->prefer")); 
                    }if($monthly_repeat_option == 2){
                        $requestdata->recurrence=array("pattern"=>array("type"=>"relativeMonthly", "interval"=>$repeat_interval, "daysOfWeek"=> array("$monthly_week_day"), "index"=> "$monthly_week"), 
                        "range"=> array("type"=>"$range_type", "startDate"=>"$startDate", "endDate"=>"$end_date_time", "numberOfOccurrences"=>$occurrences, "recurrenceTimeZone"=> "$args->prefer")); 
                    }
            }if($recurrence_type == 4){
                    if($yearly_monthly_repeat_option == 1){
                        $requestdata->recurrence=array("pattern"=>array("type"=>"absoluteYearly", "interval"=>$repeat_interval, "dayOfMonth"=> (int)$yearly_monthly_day, "month"=> (int)$yearly_month), 
                        "range"=> array("type"=>"$range_type", "startDate"=>"$startDate", "endDate"=>"$end_date_time", "numberOfOccurrences"=>$occurrences, "recurrenceTimeZone"=> "$args->prefer")); 
                    }if($yearly_monthly_repeat_option == 2){
                        $requestdata->recurrence=array("pattern"=>array("type"=>"relativeYearly", "interval"=>$repeat_interval, "daysOfWeek"=> array("$yearly_monthly_week_day"), "month"=> (int)$yearly_monthly, "index"=>"$yearly_monthly_week"), 
                        "range"=> array("type"=>"$range_type", "startDate"=>"$startDate", "endDate"=>"$end_date_time", "numberOfOccurrences"=>$occurrences, "recurrenceTimeZone"=> "$args->prefer")); 
                    }
            }
        }else{
            $requestdata->recurrence=null;   
        }

        // echo $args->meeting_id;
        // echo "<br>";
        // echo self::__construct();
        // echo "<br>";
        // echo $this->base_url.'users/'.$this->UserId.'/calendar/events/'.$args->meeting_id;
        // echo "<pre>";
        // print_r(json_encode($requestdata));
        // die;

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->base_url.'users/'.$this->UserId.'/calendar/events/'.$args->meeting_id,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'PATCH',
            CURLOPT_POSTFIELDS =>json_encode($requestdata),
            CURLOPT_HTTPHEADER => array(
            'Content-type: application/json',
            'Authorization: Bearer '.self::__construct()
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        return $response;

        // echo "<pre>";
        // print_r($response);
        // die;
    }


    // Create team meeting with calendar events
    public function delete_meeting(object $args) {

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->base_url.'users/'.$this->UserId.'/calendar/events/'.$args->meeting_id,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'DELETE',
            CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json',
            'Authorization: Bearer '.self::__construct()
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        return json_encode($response);
    }


    // Get events 
    public function get_events() {

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->base_url.'users/'.$this->UserId.'/calendar/events',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json',
            'Authorization: Bearer '.self::__construct()
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        return json_decode($response);
    }


    // Get timezone of user
    public function get_timezone() {

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->base_url.'users/'.$this->UserId.'/mailboxSettings/timeZone',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json',
            'Authorization: Bearer '.self::__construct()
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        $json_decode = json_decode($response);
        return json_encode($json_decode->value);

    }



}
