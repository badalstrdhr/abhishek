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
 * Page module version information
 *
 * @package mod_page
 * @copyright  2009 Petr Skoda (http://skodak.org)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_once($CFG->dirroot.'/mod/page/lib.php');
require_once($CFG->dirroot.'/mod/page/locallib.php');
require_once($CFG->libdir.'/completionlib.php');

$id      = optional_param('id', 0, PARAM_INT); // Course Module ID
$p       = optional_param('p', 0, PARAM_INT);  // Page instance ID
$inpopup = optional_param('inpopup', 0, PARAM_BOOL);

if ($p) {
    if (!$page = $DB->get_record('teams', array('id'=>$p))) {
        throw new \moodle_exception('invalidaccessparameter');
    }
    $cm = get_coursemodule_from_instance('teams', $page->id, $page->course, false, MUST_EXIST);

} else {
    if (!$cm = get_coursemodule_from_id('teams', $id)) {
        throw new \moodle_exception('invalidcoursemodule');
    }
    $page = $DB->get_record('teams', array('id'=>$cm->instance), '*', MUST_EXIST);
}

$course = $DB->get_record('course', array('id'=>$cm->course), '*', MUST_EXIST);

require_course_login($course, true, $cm);
$context = context_module::instance($cm->id);
// require_capability('mod/page:view', $context);

// Completion and trigger events.
page_view($page, $course, $cm, $context);

$PAGE->set_url('/mod/teams/view.php', array('id' => $cm->id));

$options = empty($page->displayoptions) ? [] : (array) unserialize_array($page->displayoptions);

$activityheader = ['hidecompletion' => false];
if (empty($options['printintro']) || !trim(strip_tags($page->intro))) {
    $activityheader['description'] = '';
}

if ($inpopup and $page->display == RESOURCELIB_DISPLAY_POPUP) {
    $PAGE->set_pagelayout('popup');
    $PAGE->set_title($course->shortname.': '.$page->name);
    $PAGE->set_heading($course->fullname);
} else {
    $PAGE->add_body_class('limitedwidth');
    $PAGE->set_title($course->shortname.': '.$page->name);
    $PAGE->set_heading($course->fullname);
    $PAGE->set_activity_record($page);
    if (!$PAGE->activityheader->is_title_allowed()) {
        $activityheader['title'] = "";
    }
}
$PAGE->activityheader->set_attrs($activityheader);
echo $OUTPUT->header();
$content = file_rewrite_pluginfile_urls($page->content, 'pluginfile.php', $context->id, 'mod_page', 'content', $page->revision);
$formatoptions = new stdClass;
$formatoptions->noclean = true;
$formatoptions->overflowdiv = true;
$formatoptions->context = $context;
$content = format_text($content, $page->contentformat, $formatoptions);
// echo $OUTPUT->box($content, "generalbox center clearfix");


$recurring = $page->recurring;
$isrecurring = '<span style="color:red;">False</span>'; 


if(is_siteadmin()){
    $view_more = '<span><a href="delete-meeting.php?instance='.$cm->instance.'">Delete meeting</a></span>';
}


$currenttime = date("h:i:s a");
$start_datetime = date("Y-m-d", $page->start_datetime);
$end_datetime = date("Y-m-d", $page->end_datetime);
$start_time = date("h:i:s a", $page->start_datetime); 
$end_time = date("h:i:s a", $page->end_datetime);

if($recurring == 1){
    $recurrence_type = $page->recurrence_type;
    $repeat_interval = $page->repeat_interval;
    $occurrences = $page->occurrences;
    $isreminderon = $page->isreminderon;
    $isallday = $page->isallday;
    $importance = ucfirst($page->importance);
    $sensitivity = ucfirst($page->sensitivity);
    $content = '';
    // Recurrence range
    $end_date_array = [];
    $sd = $page->start_datetime;
    if($page->end_date_option == 1){
        $range_type = 'Enddate';
        while($start_datetime <= date("Y-m-d",$page->end_date_time)){
            $end_date_time = date('Y-m-d', strtotime("+$repeat_interval days $start_datetime"));
            array_push($end_date_array, $end_date_time);
            $start_datetime = $end_date_time;
        }
        $content .= '<tr><th style="vertical-align: inherit; text-align: right;">Recurring range</th><td style="padding: 15px 26px; ">'.$range_type.'</td></tr>';
    }elseif($page->end_date_option == 2){
        $range_type = 'Numbered';
        for($i=0; $i<$occurrences; $i++){
            if($recurrence_type == 1){
                $end_date_time = date('Y-m-d', strtotime("+$repeat_interval days $start_datetime"));
            }elseif($recurrence_type == 2){
                $end_date_time = date('Y-m-d', strtotime("+$repeat_interval weeks $start_datetime"));
            }elseif($recurrence_type == 3){
                $end_date_time = date('Y-m-d', strtotime("+$repeat_interval months $start_datetime"));
            }else{
                $end_date_time = date('Y-m-d', strtotime("+$repeat_interval years $start_datetime"));
            }
            array_push($end_date_array, $end_date_time);
            $start_datetime = $end_date_time;
        }
        $content .= '<tr><th style="vertical-align: inherit; text-align: right;">Recurring range</th><td style="padding: 15px 26px; ">'.$range_type.'</td></tr>';
    }else{
        $range_type = 'Noend';
        while($start_datetime <= date('Y-m-d', strtotime('+1 year'))){
            $end_date_time = date('Y-m-d', strtotime("+$repeat_interval days $start_datetime"));
            array_push($end_date_array, $end_date_time);
            $start_datetime = $end_date_time;
        }
        $content .= '<tr><th style="vertical-align: inherit; text-align: right;">Recurring range</th><td style="padding: 15px 26px; ">'.$range_type.'</td></tr>';
    }

    array_pop($end_date_array);
    $enddateofmeeting = implode(", ", $end_date_array);
    // Check meeting visibility

    $isrecurring = '<span style="color:green;">True</span>'; 

    if(is_siteadmin()){
    $view_more = '<span data-toggle="collapse" href="#collapseExample" aria-expanded="false" aria-controls="collapseExample"><a href="javascript:void(0);">View more</a></span> | <span><a href="delete-meeting.php?instance='.$cm->instance.'">Delete meeting</a></span>';
    }

    if(strtotime($end_date_time) > time()){
        if(strtotime($currenttime)> strtotime($start_time) && strtotime($currenttime) < strtotime($end_time)){
            $isvisible = true;
            $prin = '<a target="_blank" class="btn btn-success" href="'.$page->join_url.'">Join Now</a>';
        }else{
            $isvisible = false;
            $prin = '<span style="background-color: #cfdaef;">You are unable to join at this time.</span>';
        }
    }else{
        $isvisible = false;
        $prin = '<span style="background-color: #cfdaef;">The meeting has finished already.</span>';
    }



    // Rcurrence pattern
    if($recurrence_type == 1){

        $recurrence_type = 'Daily';
        $content .= '<tr><th style="vertical-align: inherit; text-align: right;"><b>Repeat Every</b></th><td style="padding: 15px; " ><span class="col-md-12" >'.
        $repeat_interval.' days </span></td></tr>';
        $content .= '<tr><th style="vertical-align: inherit; text-align: right;">Recurring Dates</th><td style="padding: 15px 26px; ">'.$enddateofmeeting.'</td></tr>';

        
    }elseif($recurrence_type == 2){

        $recurrence_type = 'Weekly';
        $content .= '<tr><th style="vertical-align: inherit; text-align: right;"><b>Repeat Every</b></th><td style="padding: 15px; " ><span class="col-md-12" >'.$repeat_interval.' weeks </span></td></tr>';
        $weekly_days_sunday = $page->weekly_days_sunday;
        $weekly_days_monday = $page->weekly_days_monday;
        $weekly_days_tuesday = $page->weekly_days_tuesday;
        $weekly_days_wednesday = $page->weekly_days_wednesday;
        $weekly_days_thursday = $page->weekly_days_thursday;
        $weekly_days_friday = $page->weekly_days_friday;
        $weekly_days_saturday = $page->weekly_days_saturday;

        $daysOfWeek = array_values(array_filter(array($weekly_days_sunday, $weekly_days_monday, $weekly_days_tuesday, $weekly_days_wednesday, $weekly_days_thursday, $weekly_days_friday, $weekly_days_saturday)));

        $content .= '<tr><th style="vertical-align: inherit; text-align: right;">Recurring every week</th><td style="padding: 15px 26px; ">'.implode(", ", $daysOfWeek).'</td></tr>';

    }elseif($recurrence_type == 3){

        $recurrence_type = 'Monthly';
        $content .= '<tr><th style="vertical-align: inherit; text-align: right;"><b>Repeat Every</b></th><td style="padding: 15px; " ><span class="col-md-12" >'.$repeat_interval.' months </span></td></tr>';

        $monthly_day = $page->monthly_day;
        $monthly_week = ucfirst($page->monthly_week);
        $monthly_week_day = ucfirst($page->monthly_week_day);
        $monthly_repeat_option = $page->monthly_repeat_option;

        if(!empty($monthly_week_day)){
            $content .= '<tr><th style="vertical-align: inherit; text-align: right;">Recurring week day</th><td style="padding: 15px 26px; ">'.$monthly_week_day.'</td></tr>';
        }

        if(!empty($monthly_week)){
            $content .= '<tr><th style="vertical-align: inherit; text-align: right;">Recurring week</th><td style="padding: 15px 26px; ">'.$monthly_week.'</td></tr>';
        }

    }elseif($recurrence_type == 4){

        $recurrence_type = 'Yearly';
        $content .= '<tr><th style="vertical-align: inherit; text-align: right;"><b>Repeat every</b></th><td style="padding: 15px; " ><span class="col-md-12" >'.$repeat_interval.' years </span></td></tr>';

        $yearly_monthly_repeat_option = $page->yearly_monthly_repeat_option;
        $yearly_month = $page->yearly_month;
        $yearly_monthly_day = $page->yearly_monthly_day;
        $yearly_monthly_week = $page->yearly_monthly_week;
        $yearly_monthly_week_day = $page->yearly_monthly_week_day;
        $yearly_monthly = $page->yearly_monthly;


        if(!empty($yearly_month)){
            $content .= '<tr><th style="vertical-align: inherit; text-align: right;">Recurring yearly month</th><td style="padding: 15px 26px; ">'.$yearly_month.'</td></tr>';
        }

        if(!empty($yearly_monthly_day)){
            $content .= '<tr><th style="vertical-align: inherit; text-align: right;">Recurring yearly month day</th><td style="padding: 15px 26px; ">'.$yearly_monthly_day.'</td></tr>';
        }

        if(!empty($yearly_monthly_week)){
            $content .= '<tr><th style="vertical-align: inherit; text-align: right;">Recurring yearly month period</th><td style="padding: 15px 26px; ">'.$yearly_monthly_week.'</td></tr>';
        }

        if(!empty($yearly_monthly_week_day)){
            $content .= '<tr><th style="vertical-align: inherit; text-align: right;">Recurring yearly month day</th><td style="padding: 15px 26px; ">'.$yearly_monthly_week_day.'</td></tr>';
        }

        if(!empty($yearly_monthly)){
            $content .= '<tr><th style="vertical-align: inherit; text-align: right;">Recurring yearly month</th><td style="padding: 15px 26px; ">'.$yearly_monthly.'</td></tr>';
        }


    }

    $content .= '<tr><th style="vertical-align: inherit; text-align: right;">Start time</th><td style="padding: 15px 26px; ">'.$start_time.'</td></tr>';
    $content .= '<tr><th style="vertical-align: inherit; text-align: right;">End time</th><td style="padding: 15px 26px; ">'.$end_time.'</td></tr>';
}else{

    if($page->end_datetime > time()){
        if(strtotime($currenttime)> strtotime($start_time) && strtotime($currenttime) < strtotime($end_time)){
            $isvisible = true;
            $prin = '<a target="_blank" class="btn btn-success" href="'.$page->join_url.'">Join Now</a>';
        }else{
            $isvisible = false;
            $prin = '<span style="background-color: #cfdaef;">You are unable to join at this time.</span>';
        }
    }else{
        $isvisible = false;
        $prin = '<span style="background-color: #cfdaef;">The meeting has finished already.</span>';
    }

}


if (!isset($options['printlastmodified']) || !empty($options['printlastmodified'])) {

    $strlastmodified = get_string("lastmodified");
    
    $html = '<p><h3>Meeting Details</h3></p>
    <table class="table table-striped table-hover table-sm table-bordered">

    <tbody>
        <tr>
            <th style="vertical-align: inherit; text-align: center;" class="table-info"><b>Microsoft Teams</b></th>
            <td  style="padding: 15px;" align="center"><b><span class="col-md-12" >Start Date</span><hr><span class="col-md-12" >'.userdate($page->start_datetime).'</span></b></td>
            <td  style="padding: 15px;" align="center"><b><span class="col-md-12" >End Date</span><hr><span class="col-md-12" >'.userdate($page->end_datetime).'</span></b></td>
        </tr>


        <tr>
            <th  style="text-align: right; padding: 15px;"><b>Name</b></th>
            <td colspan="2"  style="padding: 15px 35px;">'.$page->name.'</td>
        </tr>

        <tr>
            <th  style="text-align: right; padding: 15px;" class="table-info"><b>Subject</b></th>
            <td colspan="2"  style="padding: 15px 35px;">'.$page->subjectof.'</td>
        </tr>

        <tr>
            <th  style="text-align: right; padding: 15px;" ><b>Description</b></th>
            <td colspan="2"  style="padding: 15px 35px;">'.$page->intro.'</td>
        </tr>
        

        <tr>
            <th  style="text-align: right; padding: 15px;" class="table-info"><b>'.$strlastmodified.'</b></th>
            <td colspan="2"  style="padding: 15px 35px;">'.userdate($page->timemodified).'</td>
        </tr>

        <tr>
            <th  style="text-align: right; padding: 15px;" ><b>Is recurrence?</b></th>
            <td colspan="1"  style="padding: 15px 35px;">'.$isrecurring.'</td>
            <td colspan="1"  style="padding: 15px 35px;">'.$view_more.'</td>
        </tr>


        <tr class="table-success">
            <th  style="text-align: right;" class="table-info"></th>
            <td colspan="2"  style="padding: 15px 35px;">'.$prin.'</td>
        </tr>


    </tbody>
    </table>

    <div class="collapse" id="collapseExample">
        <div class="card card-body">
            <table class="table table-striped table-hover table-sm table-bordered">
               <tbody>
                    <tr>
                        <th style="vertical-align: inherit; text-align: right;">Recurring Pattern</th>
                        <td  style="padding: 15px; " ><span class="col-md-12" >'.$recurrence_type.'</span></td>
                    </tr>
                    <tr>
                        <th style="vertical-align: inherit; text-align: right;">Importance</th>
                        <td  style="padding: 15px; " ><span class="col-md-12" >'.$importance.'</span></td>
                    </tr>
                    <tr>
                        <th style="vertical-align: inherit; text-align: right;">Sensitivity</th>
                        <td  style="padding: 15px; " ><span class="col-md-12" >'.$sensitivity.'</span></td>
                    </tr>

                    '.$content.'

                </tbody>
            </table>
        </div>
    </div>';
    
}

echo  html_writer::div($html);

echo $OUTPUT->footer();
