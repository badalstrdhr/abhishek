<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * @package mod_teams
 * @copyright  2009 Petr Skoda (http://skodak.org)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once("classes/TeamClass.php");

function teams_get_weekday_options() {
    return [
        'sunday'    => 'Sunday',
        'monday'    => 'Monday',
        'tuesday'   => 'Tuesday',
        'wednesday' => 'Wednesday',
        'thursday'  => 'Thursday',
        'friday'    => 'Friday',
        'saturday'  => 'Saturday',
    ];
}


function teams_get_monthweek_options() {
    return [
        'first'  => 'First',
        'second' => 'Second',
        'third'  => 'Third',
        'fourth' => 'Fourth',
        'last'   => 'Last',
    ];
}



function teams_get_yearmonth_options() {
    return [
        1 => 'January',
        2 => 'February',
        3 => 'March',
        4 => 'April',
        5 => 'May',
        6 => 'June',
        7 => 'July',
        8 => 'August',
        9 => 'September',
        10 => 'October',
        11 => 'November',
        12 => 'December',
    ];
}

function get_teams_name($teams) {
    $name = html_to_text(format_string($teams->intro, true));
    $name = preg_replace('/@@PLUGINFILE@@\/[[:^space:]]+/i', '', $name);
    // Remove double space and also nbsp; characters.
    $name = preg_replace('/\s+/u', ' ', $name);
    $name = trim($name);
    if (core_text::strlen($name) > TEAMS_MAX_NAME_LENGTH) {
        $name = core_text::substr($name, 0, teams_MAX_NAME_LENGTH) . "...";
    }

    if (empty($name)) {
        // arbitrary name
        $name = get_string('modulename','teams');
    }

    return $name;
}


function teams_supports($feature) {
    switch($feature) {
        case FEATURE_MOD_ARCHETYPE:           return MOD_ARCHETYPE_RESOURCE;
        case FEATURE_GROUPS:                  return false;
        case FEATURE_GROUPINGS:               return false;
        case FEATURE_MOD_INTRO:               return true;
        case FEATURE_COMPLETION_TRACKS_VIEWS: return true;
        case FEATURE_GRADE_HAS_GRADE:         return false;
        case FEATURE_GRADE_OUTCOMES:          return false;
        case FEATURE_BACKUP_MOODLE2:          return true;
        case FEATURE_SHOW_DESCRIPTION:        return true;
        case FEATURE_MOD_PURPOSE:             return MOD_PURPOSE_CONTENT;

        default: return null;
    }
}


function teams_reset_userdata($data) {

    // Any changes to the list of dates that needs to be rolled should be same during course restore and course reset.
    // See MDL-9367.

    return array();
}


function teams_get_view_actions() {
    return array('view','view all');
}


function teams_get_post_actions() {
    return array('update', 'add');
}


function teams_add_instance($data, $mform = null) {
    global $CFG, $DB;
    require_once($CFG->libdir."/resourcelib.php");

    // echo "<pre>";
    // var_dump($data);
    // die;

    $cmid = $data->coursemodule;

    $data->timemodified = time();
    $displayoptions = array();
    if ($data->display == RESOURCELIB_DISPLAY_POPUP) {
        $displayoptions['popupwidth']  = $data->popupwidth;
        $displayoptions['popupheight'] = $data->popupheight;
    }
    $displayoptions['printintro']   = $data->printintro;
    $displayoptions['printlastmodified'] = $data->printlastmodified;
    $data->displayoptions = serialize($displayoptions);

    if ($mform) {
        $data->content       = $data->teams['text'];
        $data->contentformat = $data->teams['format'];
    }


    $data -> subject = htmlentities($data->subjectof);
    $data -> content = htmlentities($data->intro);
    $startdatetime = date("Y-m-d",$data->start_datetime).'T'.date("H:i:s",$data->start_datetime);
    $data -> start_datetime = $startdatetime;
    $data -> start_timezone = "$data->start_timezone";
    $endtdatetime = date("Y-m-d",$data->end_datetime).'T'.date("H:i:s",$data->end_datetime);
    $data -> end_datetime = $endtdatetime;
    $data -> end_timezone = "$data->end_timezone";
    $data -> location = htmlentities("$data->location");
    $data -> attendees_email = "$data->attendees_email";
    $data -> attendees_name = "$data->attendees_name";
    $data -> displayname = htmlentities("$data->name");
    $data -> prefer = "$data->prefer";
    $data -> hideattendees = ($data->hideattendees == 0 )?false:true;
    $data -> importance = "$data->importance"; // low, normal, high
    $data -> isallday = ($data->isallday == 0 )?false:true;
    $data -> isonlinemeeting = ($data->isonlinemeeting == 1)?true:false;
    $data -> isreminderon = ($data->isreminderon == 1)?true:false;
    $data -> sensitivity = "$data->sensitivity";


    // Recurring Type
    $data -> recurring = $data -> recurring;
    $data -> repeat_interval = $data -> repeat_interval;
    $data -> recurrence_type = $data -> recurrence_type;


    if($data -> recurring == 1){
        if($data -> recurrence_type == 1){

                // Weekly recurrence
                $data -> weekly_days_friday = null;
                $data -> weekly_days_monday = null;
                $data -> weekly_days_saturday = null;
                $data -> weekly_days_sunday = null;
                $data -> weekly_days_thursday = null;
                $data -> weekly_days_tuesday = null;
                $data -> weekly_days_wednesday = null;


                // Monthly recurrence
                $data -> monthly_day = null;
                $data -> monthly_repeat_option = null;
                $data -> monthly_week = null;
                $data -> monthly_week_day = null;


                // Yearly recurrence
                $data -> yearly_monthly_repeat_option = null;
                $data -> yearly_month = null;
                $data -> yearly_monthly_day = null;
                $data -> yearly_monthly_week = null;
                $data -> yearly_monthly_week_day = null;
                $data -> yearly_monthly = null;

           
            
        }elseif($data -> recurrence_type == 2){

                // Weekly recurrence
                $data -> weekly_days_friday = ($data -> weekly_days_friday) != '0' ? $data -> weekly_days_friday : null;
                $data -> weekly_days_monday = ($data -> weekly_days_monday) != '0' ? $data -> weekly_days_monday : null;
                $data -> weekly_days_saturday = ($data -> weekly_days_saturday) != '0' ? $data -> weekly_days_saturday : null;
                $data -> weekly_days_sunday = ($data -> weekly_days_sunday) != '0' ? $data -> weekly_days_sunday : null;
                $data -> weekly_days_thursday = ($data -> weekly_days_thursday) != '0' ? $data -> weekly_days_thursday : null;
                $data -> weekly_days_tuesday = ($data -> weekly_days_tuesday) != '0' ? $data -> weekly_days_tuesday : null;
                $data -> weekly_days_wednesday = ($data -> weekly_days_wednesday) != '0' ? $data -> weekly_days_wednesday : null;


                // Monthly recurrence
                $data -> monthly_day = null;
                $data -> monthly_repeat_option = null;
                $data -> monthly_week = null;
                $data -> monthly_week_day = null;


                // Yearly recurrence
                $data -> yearly_monthly_repeat_option = null;
                $data -> yearly_month = null;
                $data -> yearly_monthly_day = null;
                $data -> yearly_monthly_week = null;
                $data -> yearly_monthly_week_day = null;
                $data -> yearly_monthly = null;



        }elseif($data -> recurrence_type == 3){

                // Weekly recurrence
                $data -> weekly_days_friday = null;
                $data -> weekly_days_monday = null;
                $data -> weekly_days_saturday = null;
                $data -> weekly_days_sunday = null;
                $data -> weekly_days_thursday = null;
                $data -> weekly_days_tuesday = null;
                $data -> weekly_days_wednesday = null;
    
    
                // Monthly recurrence
                $data -> monthly_repeat_option = $data -> monthly_repeat_option;
                if($data -> monthly_repeat_option == 1){
                    $data -> monthly_day = $data -> monthly_day;
                    $data -> monthly_week = null;
                    $data -> monthly_week_day = null;
                }else{
                    $data -> monthly_day = null;
                    $data -> monthly_week = $data -> monthly_week;
                    $data -> monthly_week_day = $data -> monthly_week_day;
                }
               
    
                // Yearly recurrence
                $data -> yearly_monthly_repeat_option = null;
                $data -> yearly_month = null;
                $data -> yearly_monthly_day = null;
                $data -> yearly_monthly_week = null;
                $data -> yearly_monthly_week_day = null;
                $data -> yearly_monthly = null;

        }elseif($data -> recurrence_type == 4){

                // Weekly recurrence
                $data -> weekly_days_friday = null;
                $data -> weekly_days_monday = null;
                $data -> weekly_days_saturday = null;
                $data -> weekly_days_sunday = null;
                $data -> weekly_days_thursday = null;
                $data -> weekly_days_tuesday = null;
                $data -> weekly_days_wednesday = null;


                // Monthly recurrence
                $data -> monthly_day = null;
                $data -> monthly_repeat_option = null;
                $data -> monthly_week = null;
                $data -> monthly_week_day = null;


                // Yearly recurrence
                $data -> yearly_monthly_repeat_option = $data -> yearly_monthly_repeat_option;
                if($data -> yearly_monthly_repeat_option == 1){
                    $data -> yearly_month = $data -> yearly_month;
                    $data -> yearly_monthly_day = $data -> yearly_monthly_day;
                    $data -> yearly_monthly_week = null;
                    $data -> yearly_monthly_week_day = null;
                    $data -> yearly_monthly = null;
                }else{
                    $data -> yearly_month = null;
                    $data -> yearly_monthly_day = null;
                    $data -> yearly_monthly_week = $data -> yearly_monthly_week;
                    $data -> yearly_monthly_week_day = $data -> yearly_monthly_week_day;
                    $data -> yearly_monthly = $data -> yearly_monthly;
                }



        }else{

                // Weekly recurrence
                $data -> weekly_days_friday = null;
                $data -> weekly_days_monday = null;
                $data -> weekly_days_saturday = null;
                $data -> weekly_days_sunday = null;
                $data -> weekly_days_thursday = null;
                $data -> weekly_days_tuesday = null;
                $data -> weekly_days_wednesday = null;


                // Monthly recurrence
                $data -> monthly_day = null;
                $data -> monthly_repeat_option = null;
                $data -> monthly_week = null;
                $data -> monthly_week_day = null;


                // Yearly recurrence
                $data -> yearly_monthly_repeat_option = null;
                $data -> yearly_month = null;
                $data -> yearly_monthly_day = null;
                $data -> yearly_monthly_week = null;
                $data -> yearly_monthly_week_day = null;
                $data -> yearly_monthly = null;

        }

        // Endate for range and occerence
        $data -> end_date_option = $data -> end_date_option;
        if($data -> end_date_option == 1){
            $data -> end_date_time = $data -> end_date_time;
            $data -> occurrences = null;
        }elseif($data -> end_date_option == 2){
            $data -> occurrences = $data -> occurrences;
            $data -> end_date_time = null;
        }else{
            $data -> occurrences = null;
            $data -> end_date_time = null;
        }

    }else{

            // Weekly recurrence
            $data -> weekly_days_friday = null;
            $data -> weekly_days_monday = null;
            $data -> weekly_days_saturday = null;
            $data -> weekly_days_sunday = null;
            $data -> weekly_days_thursday = null;
            $data -> weekly_days_tuesday = null;
            $data -> weekly_days_wednesday = null;


            // Monthly recurrence
            $data -> monthly_day = null;
            $data -> monthly_repeat_option = null;
            $data -> monthly_week = null;
            $data -> monthly_week_day = null;


            // Yearly recurrence
            $data -> yearly_monthly_repeat_option = null;
            $data -> yearly_month = null;
            $data -> yearly_monthly_day = null;
            $data -> yearly_monthly_week = null;
            $data -> yearly_monthly_week_day = null;
            $data -> yearly_monthly = null;

            // Endate for range and occerence
            $data -> end_date_option = null;
            $data -> end_date_time = null;
            $data -> occurrences = null;

    }


    $TeamClass_obj = new TeamClass();
    $get_events = json_decode($TeamClass_obj->create_meeting($data));

    $data->timemodified = time();
    $data->meeting_id = $get_events->id;
    $data->start_url = $get_events->onlineMeeting->joinUrl;
    $data->join_url = $get_events->onlineMeeting->joinUrl;
    $data->created_at = strtotime($get_events->createdDateTime);
    $data->host_id = $get_events->iCalUId;
    $data -> start_datetime = strtotime($startdatetime);
    $data -> end_datetime = strtotime($endtdatetime);

    $data->id = $DB->insert_record('teams', $data);



    // Calendar events create.
    require_once($CFG->dirroot.'/calendar/lib.php');

    $event = new stdClass();
    $event->eventtype = 'eventteams'; 
    $event->type = CALENDAR_EVENT_TYPE_STANDARD; 
    $event->name = $data->name;
    $event->description = $data->intro;
    $event->format = FORMAT_HTML;
    $event->courseid = $data->course;
    $event->groupid = 0;
    $event->userid = 0;
    $event->modulename = 'teams';
    $event->instance = $data->id;
    $event->timestart = $data->start_datetime;
    $event->timesort = null;
    $event->visible = instance_is_visible('teams', $data);
    $event->timeduration =  $data->end_date_time-$data->start_datetime;
    $event->repeatid = 0;
    $data_event = calendar_event::create($event);


    if($data -> recurring == 1) {

        $obj_update = new stdClass();
        $obj_update->id = $data_event->id;
        $obj_update->repeatid = $data_event->id;
        $DB->update_record('event', $obj_update); 
           
    }

   
    // we need to use context now, so we need to make sure all needed info is already in db
    $DB->set_field('course_modules', 'instance', $data->id, array('id'=>$cmid));
    $context = context_module::instance($cmid);


    $completiontimeexpected = !empty($data->completionexpected) ? $data->completionexpected : null;
    \core_completion\api::update_completion_date_event($cmid, 'teams', $data->id, $completiontimeexpected);

    return $data->id;
}


function teams_update_instance($data, $mform) {
    global $CFG, $DB;
    require_once($CFG->libdir."/resourcelib.php");

    // echo "<pre>";
    // var_dump($data);
    // die;

    $cmid        = $data->coursemodule;
    $draftitemid = $data->teams['itemid'];


    $data->timemodified = time();
    $data->id           = $data->instance;
    $data->revision++;

    $displayoptions = array();
    if ($data->display == RESOURCELIB_DISPLAY_POPUP) {
        $displayoptions['popupwidth']  = $data->popupwidth;
        $displayoptions['popupheight'] = $data->popupheight;
    }
    $displayoptions['printintro']   = $data->printintro;
    $displayoptions['printlastmodified'] = $data->printlastmodified;
    $data->displayoptions = serialize($displayoptions);

    $data->content       = $data->teams['text'];
    $data->contentformat = $data->teams['format'];



    $data -> subject = htmlentities($data->subjectof);
    $data -> content = htmlentities($data->intro);
    $startdatetime = date("Y-m-d",$data->start_datetime).'T'.date("H:i:s",$data->start_datetime);
    $data -> start_datetime = $startdatetime;
    $data -> start_timezone = "$data->start_timezone";
    $endtdatetime = date("Y-m-d",$data->end_datetime).'T'.date("H:i:s",$data->end_datetime);
    $data -> end_datetime = $endtdatetime;
    $data -> end_timezone = "$data->end_timezone";
    $data -> location = htmlentities("$data->location");
    $data -> attendees_email = "$data->attendees_email";
    $data -> attendees_name = "$data->attendees_name";
    $data -> displayname = htmlentities("$data->name");
    $data -> prefer = "$data->prefer";
    $data -> hideattendees = ($data->hideattendees == 0 )?false:true;
    $data -> importance = "$data->importance"; // low, normal, high
    $data -> isallday = ($data->isallday == 0 )?false:true;

    
    $data -> isonlinemeeting = ($data->isonlinemeeting == 1)?true:false;
    $data -> isreminderon = ($data->isreminderon == 1)?true:false;
    $data -> sensitivity = "$data->sensitivity";

    $get_meeting = $DB -> get_record("teams", array("id"=>$data->id));
    $data -> meeting_id = $get_meeting->meeting_id;


    // Recurring Type
    $data -> recurring = $data -> recurring;
    $data -> repeat_interval = $data -> repeat_interval;
    $data -> recurrence_type = $data -> recurrence_type;


    if($data -> recurring == 1){
        if($data -> recurrence_type == 1){

                // Weekly recurrence
                $data -> weekly_days_friday = null;
                $data -> weekly_days_monday = null;
                $data -> weekly_days_saturday = null;
                $data -> weekly_days_sunday = null;
                $data -> weekly_days_thursday = null;
                $data -> weekly_days_tuesday = null;
                $data -> weekly_days_wednesday = null;


                // Monthly recurrence
                $data -> monthly_day = null;
                $data -> monthly_repeat_option = null;
                $data -> monthly_week = null;
                $data -> monthly_week_day = null;


                // Yearly recurrence
                $data -> yearly_monthly_repeat_option = null;
                $data -> yearly_month = null;
                $data -> yearly_monthly_day = null;
                $data -> yearly_monthly_week = null;
                $data -> yearly_monthly_week_day = null;
                $data -> yearly_monthly = null;

           
            
        }elseif($data -> recurrence_type == 2){

                // Weekly recurrence
                $data -> weekly_days_friday = ($data -> weekly_days_friday) != '0' ? $data -> weekly_days_friday : null;
                $data -> weekly_days_monday = ($data -> weekly_days_monday) != '0' ? $data -> weekly_days_monday : null;
                $data -> weekly_days_saturday = ($data -> weekly_days_saturday) != '0' ? $data -> weekly_days_saturday : null;
                $data -> weekly_days_sunday = ($data -> weekly_days_sunday) != '0' ? $data -> weekly_days_sunday : null;
                $data -> weekly_days_thursday = ($data -> weekly_days_thursday) != '0' ? $data -> weekly_days_thursday : null;
                $data -> weekly_days_tuesday = ($data -> weekly_days_tuesday) != '0' ? $data -> weekly_days_tuesday : null;
                $data -> weekly_days_wednesday = ($data -> weekly_days_wednesday) != '0' ? $data -> weekly_days_wednesday : null;


                // Monthly recurrence
                $data -> monthly_day = null;
                $data -> monthly_repeat_option = null;
                $data -> monthly_week = null;
                $data -> monthly_week_day = null;


                // Yearly recurrence
                $data -> yearly_monthly_repeat_option = null;
                $data -> yearly_month = null;
                $data -> yearly_monthly_day = null;
                $data -> yearly_monthly_week = null;
                $data -> yearly_monthly_week_day = null;
                $data -> yearly_monthly = null;



        }elseif($data -> recurrence_type == 3){

                // Weekly recurrence
                $data -> weekly_days_friday = null;
                $data -> weekly_days_monday = null;
                $data -> weekly_days_saturday = null;
                $data -> weekly_days_sunday = null;
                $data -> weekly_days_thursday = null;
                $data -> weekly_days_tuesday = null;
                $data -> weekly_days_wednesday = null;
    
    
                // Monthly recurrence
                $data -> monthly_repeat_option = $data -> monthly_repeat_option;
                if($data -> monthly_repeat_option == 1){
                    $data -> monthly_day = $data -> monthly_day;
                    $data -> monthly_week = null;
                    $data -> monthly_week_day = null;
                }else{
                    $data -> monthly_day = null;
                    $data -> monthly_week = $data -> monthly_week;
                    $data -> monthly_week_day = $data -> monthly_week_day;
                }
               
    
                // Yearly recurrence
                $data -> yearly_monthly_repeat_option = null;
                $data -> yearly_month = null;
                $data -> yearly_monthly_day = null;
                $data -> yearly_monthly_week = null;
                $data -> yearly_monthly_week_day = null;
                $data -> yearly_monthly = null;

        }elseif($data -> recurrence_type == 4){

                // Weekly recurrence
                $data -> weekly_days_friday = null;
                $data -> weekly_days_monday = null;
                $data -> weekly_days_saturday = null;
                $data -> weekly_days_sunday = null;
                $data -> weekly_days_thursday = null;
                $data -> weekly_days_tuesday = null;
                $data -> weekly_days_wednesday = null;


                // Monthly recurrence
                $data -> monthly_day = null;
                $data -> monthly_repeat_option = null;
                $data -> monthly_week = null;
                $data -> monthly_week_day = null;


                // Yearly recurrence
                $data -> yearly_monthly_repeat_option = $data -> yearly_monthly_repeat_option;
                if($data -> yearly_monthly_repeat_option == 1){
                    $data -> yearly_month = $data -> yearly_month;
                    $data -> yearly_monthly_day = $data -> yearly_monthly_day;
                    $data -> yearly_monthly_week = null;
                    $data -> yearly_monthly_week_day = null;
                    $data -> yearly_monthly = null;
                }else{
                    $data -> yearly_month = null;
                    $data -> yearly_monthly_day = null;
                    $data -> yearly_monthly_week = $data -> yearly_monthly_week;
                    $data -> yearly_monthly_week_day = $data -> yearly_monthly_week_day;
                    $data -> yearly_monthly = $data -> yearly_monthly;
                }



        }else{

                // Weekly recurrence
                $data -> weekly_days_friday = null;
                $data -> weekly_days_monday = null;
                $data -> weekly_days_saturday = null;
                $data -> weekly_days_sunday = null;
                $data -> weekly_days_thursday = null;
                $data -> weekly_days_tuesday = null;
                $data -> weekly_days_wednesday = null;


                // Monthly recurrence
                $data -> monthly_day = null;
                $data -> monthly_repeat_option = null;
                $data -> monthly_week = null;
                $data -> monthly_week_day = null;


                // Yearly recurrence
                $data -> yearly_monthly_repeat_option = null;
                $data -> yearly_month = null;
                $data -> yearly_monthly_day = null;
                $data -> yearly_monthly_week = null;
                $data -> yearly_monthly_week_day = null;
                $data -> yearly_monthly = null;

        }

        // Endate for range and occerence
        $data -> end_date_option = $data -> end_date_option;
        if($data -> end_date_option == 1){
            $data -> end_date_time = $data -> end_date_time;
            $data -> occurrences = null;
        }elseif($data -> end_date_option == 2){
            $data -> occurrences = $data -> occurrences;
            $data -> end_date_time = null;
        }else{
            $data -> occurrences = null;
            $data -> end_date_time = null;
        }

    }else{

            // Weekly recurrence
            $data -> weekly_days_friday = null;
            $data -> weekly_days_monday = null;
            $data -> weekly_days_saturday = null;
            $data -> weekly_days_sunday = null;
            $data -> weekly_days_thursday = null;
            $data -> weekly_days_tuesday = null;
            $data -> weekly_days_wednesday = null;


            // Monthly recurrence
            $data -> monthly_day = null;
            $data -> monthly_repeat_option = null;
            $data -> monthly_week = null;
            $data -> monthly_week_day = null;


            // Yearly recurrence
            $data -> yearly_monthly_repeat_option = null;
            $data -> yearly_month = null;
            $data -> yearly_monthly_day = null;
            $data -> yearly_monthly_week = null;
            $data -> yearly_monthly_week_day = null;
            $data -> yearly_monthly = null;

            // Endate for range and occerence
            $data -> end_date_option = null;
            $data -> end_date_time = null;
            $data -> occurrences = null;

    }


    $TeamClass_obj = new TeamClass();
    $get_events = json_decode($TeamClass_obj->update_meeting($data));


    $data->timemodified = time();
    $data->meeting_id = $get_events->id;
    $data->start_url = $get_events->onlineMeeting->joinUrl;
    $data->join_url = $get_events->onlineMeeting->joinUrl;
    $data->created_at = strtotime($get_events->createdDateTime);
    $data->host_id = $get_events->iCalUId;
    $data -> start_datetime = strtotime($startdatetime);
    $data -> end_datetime = strtotime($endtdatetime);
  

    $DB->update_record('teams', $data);



    // Calendar events create.
    require_once($CFG->dirroot.'/calendar/lib.php');

    $event = new stdClass();
    $event_upd->eventtype = 'eventteams'; 
    $event_upd->type = CALENDAR_EVENT_TYPE_STANDARD; 
    $event_upd->name = $data->name;
    $event_upd->description = $data->intro;
    $event_upd->format = FORMAT_HTML;
    $event_upd->courseid = $data->course;
    $event_upd->groupid = 0;
    $event_upd->userid = 0;
    $event_upd->modulename = 'teams';
    $event_upd->instance = $data->instance;
    $event_upd->timestart = $data->start_datetime;
    $event_upd->timesort = $data->end_datetime;
    $event_upd->visible = instance_is_visible('teams', $data);
    $event_upd->timeduration = 0;


    $eventid = $DB->get_record('event', array("instance"=>$data->instance));
    $event = calendar_event::load($eventid->id);
    $event->update($event_upd);



    $context = context_module::instance($cmid);


    $completiontimeexpected = !empty($data->completionexpected) ? $data->completionexpected : null;
    \core_completion\api::update_completion_date_event($cmid, 'teams', $data->id, $completiontimeexpected);

    return true;
}


function teams_delete_instance($id) {
    global $DB;

    
    if (!$teams = $DB->get_record('teams', array('id'=>$id))) {
        return false;
    }


    $cm = get_coursemodule_from_instance('teams', $id);
    \core_completion\api::update_completion_date_event($cm->id, 'teams', $id, null);


    $DB->delete_records('teams', array('id'=>$teams->id));
    $get_meeting = $DB -> get_record("teams", array("id"=>$id));



}


function teams_get_coursemodule_info($coursemodule) {
    global $CFG, $DB;
    require_once($CFG->libdir."/resourcelib.php");

    if (!$teams = $DB->get_record('teams', array('id'=>$coursemodule->instance),
            'id, name, display, displayoptions, intro, introformat')) {
        return NULL;
    }

    $info = new cached_cm_info();
    $info->name = $teams->name;

    if ($coursemodule->showdescription) {
        // Convert intro to html. Do not filter cached version, filters run at display time.
        $info->content = format_module_intro('teams', $teams, $coursemodule->id, false);
    }

    if ($teams->display != RESOURCELIB_DISPLAY_POPUP) {
        return $info;
    }

    $fullurl = "$CFG->wwwroot/mod/teams/view.php?id=$coursemodule->id&amp;inpopup=1";
    $options = empty($teams->displayoptions) ? [] : (array) unserialize_array($teams->displayoptions);
    $width  = empty($options['popupwidth'])  ? 620 : $options['popupwidth'];
    $height = empty($options['popupheight']) ? 450 : $options['popupheight'];
    $wh = "width=$width,height=$height,toolbar=no,location=no,menubar=no,copyhistory=no,status=no,directories=no,scrollbars=yes,resizable=yes";
    $info->onclick = "window.open('$fullurl', '', '$wh'); return false;";

    return $info;
}





function teams_teams_type_list($teamstype, $parentcontext, $currentcontext) {
    $module_teamstype = array('mod-teams-*'=>get_string('teams-mod-teams-x', 'teams'));
    return $module_teamstype;
}




function teams_view($teams, $course, $cm, $context) {

    // Trigger course_module_viewed event.
    $params = array(
        'context' => $context,
        'objectid' => $teams->id
    );

    $event = \mod_teams\event\course_module_viewed::create($params);
    $event->add_record_snapshot('course_modules', $cm);
    $event->add_record_snapshot('course', $course);
    $event->add_record_snapshot('teams', $teams);
    $event->trigger();

    // Completion.
    $completion = new completion_info($course);
    $completion->set_module_viewed($cm);
}


function teams_check_updates_since(cm_info $cm, $from, $filter = array()) {
    $updates = course_check_module_updates_since($cm, $from, array('content'), $filter);
    return $updates;
}


function mod_teams_core_calendar_provide_event_action(calendar_event $event,
                                                      \core_calendar\action_factory $factory, $userid = 0) {
    global $USER;

    // var_dump($event);
    // die;
    if (empty($userid)) {
        $userid = $USER->id;
    }

    $cm = get_fast_modinfo($event->courseid, $userid)->instances['teams'][$event->instance];

    $completion = new \completion_info($cm->get_course());

    $completiondata = $completion->get_data($cm, false, $userid);

    if ($completiondata->completionstate != COMPLETION_INCOMPLETE) {
        return null;
    }

    return $factory->create_instance(
        get_string('view'),
        new \moodle_url('/mod/teams/view.php', ['id' => $cm->id]),
        1,
        true
    );
}







