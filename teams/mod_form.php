<?php
// This file is part of the Teams plugin for Moodle - http://moodle.org/
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
 * The main Teams configuration form
 *
 * It uses the standard core Moodle formslib. For more info about them, please
 * visit: http://docs.moodle.org/en/Development:lib/formslib.php
 *
 * @package    mod_teams
 * @copyright  2015 UC Regents
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/course/moodleform_mod.php');
require_once($CFG->dirroot . '/mod/teams/lib.php');
require_once($CFG->dirroot . '/mod/teams/locallib.php');
require_once($CFG->dirroot . '/mod/teams/classes/TeamClass.php');

/**
 * Module instance settings form
 */
class mod_teams_mod_form extends moodleform_mod {
    function definition() {
        global $DB, $USER, $PAGE;

        $PAGE->force_settings_menu();
        $mform = $this->_form;
    
        // displayName
        $mform->addElement('text', 'name', get_string('displayname', 'teams'), ['size'=>'64']);
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');



        // subject
        $mform->addElement('text', 'subjectof', get_string('subject', 'teams'), ['size'=>'64']);
        $mform->setType('subjectof', PARAM_TEXT);
        $mform->addRule('subjectof', null, 'required', null, 'client');


        // Description
        $this->standard_intro_elements();



        // start_dateTime
        $mform->addElement('date_time_selector', 'start_datetime', get_string('start_datetime','teams'));
        $mform->addRule('start_datetime', null, 'required', null, 'client');
        $mform->setDefault('start_datetime', $mform->start_dateTime);

        
        // start_timezone
        $TeamClass_obj = new TeamClass();
        $get_timezone = json_decode($TeamClass_obj->get_timezone());
        $mform->addElement('hidden', 'start_timezone', $get_timezone);
       

        // end_dateTime
        $mform->addElement('date_time_selector', 'end_datetime', get_string('end_datetime','teams'));
        $mform->addRule('end_datetime', null, 'required', null, 'client');
        $mform->setDefault('end_datetime', $mform->end_dateTime);


        // end_timezone
        $get_timezone = json_decode($TeamClass_obj->get_timezone());
        $mform->addElement('hidden', 'end_timezone', $get_timezone);


        // Add recurring widget.
        $mform->addElement('advcheckbox', 'recurring','Recurring meeting','This is a recurring meeting');
        $mform->setDefault('recurring', 'notchecked');


        // Add options for recurring meeting.
        $recurrencetype = [
            TEAMS_RECURRINGTYPE_DAILY => 'Daily',
            TEAMS_RECURRINGTYPE_WEEKLY => 'Weekly',
            TEAMS_RECURRINGTYPE_MONTHLY => 'Monthly',
            TEAMS_RECURRINGTYPE_YEARLY => 'Yearly',
        ];
       

        // Repeat Interval options.
        $options = [];
        for ($i = 1; $i <= 90; $i++) { $options[$i] = $i; }
       
        $group = [];
       
        $group[] = $mform->createElement('select', 'repeat_interval', '', $options);
        $group[] = $mform->createElement('select', 'recurrence_type', 'Recurrence', $recurrencetype);
        $mform->hideif('recurrence_type', 'recurring', 'notchecked');
        
        $mform->addGroup($group, 'repeat_group', get_string('repeatinterval', 'teams'), null, false);
        $mform->hideif('repeat_group', 'recurring', 'notchecked');



        // Weekly options.
        $weekdayoptions = teams_get_weekday_options();
        $group = [];
        foreach ($weekdayoptions as $key => $weekday) {
             $weekdayid = 'weekly_days_' . $key;
            $attributes = [];
            $group[] = $mform->createElement('advcheckbox', $weekdayid, '', $weekday, null, [0, $key]);
        }


        

        $mform->addGroup($group, 'weekly_days_group', get_string('occurson', 'teams'), ' ', false);
        $mform->hideif('weekly_days_group', 'recurrence_type', 'noteq', TEAMS_RECURRINGTYPE_WEEKLY);
        $mform->hideif('weekly_days_group', 'recurring', 'notchecked');
        if (!empty($this->current->weekly_days)) {
            $weekdaynumbers = explode(',', $this->current->weekly_days);
            foreach ($weekdaynumbers as $daynumber) {
                 $weekdayid = 'weekly_days_' . $daynumber;
                $mform->setDefault($weekdayid, $daynumber);
            }
        }

        // die;
        // Monthly options.
        $monthoptions = [];
        for ($i = 1; $i <= 31; $i++) { $monthoptions[$i] = $i; }
        $monthlyweekoptions = teams_get_monthweek_options();
        $group = [];
        $group[] = $mform->createElement('radio', 'monthly_repeat_option','', 'On', TEAMS_MONTHLY_REPEAT_OPTION_DAY);
        $group[] = $mform->createElement('select', 'monthly_day', '', $monthoptions);
        
        $group[] = $mform->createElement('radio', 'monthly_repeat_option', '', '', TEAMS_MONTHLY_REPEAT_OPTION_WEEK);
        $group[] = $mform->createElement('static', 'month_day_text', '', 'On the');
        $group[] = $mform->createElement('select', 'monthly_week', '', $monthlyweekoptions);
        $group[] = $mform->createElement('select', 'monthly_week_day', '', $weekdayoptions);

        $mform->addGroup($group, 'monthly_day_group', get_string('occurson', 'teams'), null, false);
        $mform->hideif('monthly_day_group', 'recurrence_type', 'noteq', TEAMS_RECURRINGTYPE_MONTHLY);
        $mform->hideif('monthly_day_group', 'recurring', 'notchecked');
        $mform->setDefault('monthly_repeat_option', TEAMS_MONTHLY_REPEAT_OPTION_DAY);



        // Yearly options.
        $monthoptions = [];
        for ($i = 1; $i <= 31; $i++) { $monthoptions[$i] = $i; }
        $monthlyweekoptions = teams_get_monthweek_options();
        $yearlymonthoptions = teams_get_yearmonth_options();
        $group = [];
        
        $group[] = $mform->createElement('radio',  'yearly_monthly_repeat_option','', '', TEAMS_MONTHLY_REPEAT_OPTION_DAY);
        $group[] = $mform->createElement('static', 'yearly_month_day_text', '', 'On');
        $group[] = $mform->createElement('select', 'yearly_month', '', $yearlymonthoptions);
        $group[] = $mform->createElement('select', 'yearly_monthly_day', '', $monthoptions);
        $group[] = $mform->createElement('radio',  'yearly_monthly_repeat_option', '', '', TEAMS_MONTHLY_REPEAT_OPTION_WEEK);
        $group[] = $mform->createElement('static', 'yearly_month_day_text', '', 'On the');
        $group[] = $mform->createElement('select', 'yearly_monthly_week', '', $monthlyweekoptions);
        $group[] = $mform->createElement('select', 'yearly_monthly_week_day', '', $weekdayoptions);
        $group[] = $mform->createElement('static', 'yearly_month_day_text', '', 'of');
        $group[] = $mform->createElement('select', 'yearly_monthly', '', $yearlymonthoptions);
        $mform->addGroup($group, 'yearly_day_group', get_string('occurson', 'teams'), null, false);
        $mform->hideif('yearly_day_group', 'recurrence_type', 'noteq', TEAMS_RECURRINGTYPE_YEARLY);
        $mform->hideif('yearly_day_group', 'recurring', 'notchecked');
        $mform->setDefault('yearly_repeat_option', TEAMS_YEARLY_REPEAT_OPTION_MONTH);

        

        // End date option.
        $maxoptions = [];
        for ($i = 1; $i <= 99; $i++) { $maxoptions[$i] = $i; }
        $group = [];
        $group[] = $mform->createElement('radio', 'end_date_option', '', get_string('end_date_option_by', 'teams'), TEAMS_END_DATE_OPTION_BY);
        $group[] = $mform->createElement('date_selector', 'end_date_time', '');
        $group[] = $mform->createElement('radio', 'end_date_option', '', get_string('end_date_option_after', 'teams'), TEAMS_END_DATE_OPTION_AFTER);
        $group[] = $mform->createElement('select', 'occurrences', '', $maxoptions);
        $group[] = $mform->createElement('static', 'end_times_text', '', get_string('end_date_option_occurrences', 'teams'));
        $group[] = $mform->createElement('radio', 'end_date_option', '', 'No end', TEAMS_END_DATE_OPTION_NO_END);
        $mform->addGroup($group, 'radioenddate', get_string('enddate', 'teams'), null, false);
        $mform->hideif('radioenddate', 'recurring', 'notchecked');
        // Set default option for end date to be "By".
        $mform->setDefault('end_date_option', TEAMS_END_DATE_OPTION_NO_END);
        // Set default end_date_time to be 1 week in the future.
        $mform->setDefault('end_date_time', strtotime('+1 week'));


        // location
        $mform->addElement('text', 'location', get_string('location', 'teams'), ['size'=>'64']);
        $mform->setType('location', PARAM_TEXT);
        $mform->addRule('location', null, 'required', null, 'client');


        // attendees_email
        $mform->addElement('text', 'attendees_email', get_string('attendees_email', 'teams'), ['size'=>'64']);
        $mform->setType('attendees_email', PARAM_TEXT);
        $mform->setDefault('attendees_email', $USER->email);


        // attendees_name
        $mform->addElement('text', 'attendees_name', get_string('attendees_name', 'teams'), ['size'=>'64']);
        $mform->setType('attendees_name', PARAM_TEXT);
        $username = $USER->firstname.' '.$USER->lastname;
        $mform->setDefault('attendees_name', $username);


        // prefer
        $get_timezone = json_decode($TeamClass_obj->get_timezone());
        $ynoptions = [$get_timezone => $get_timezone];
        $mform->addElement('select', 'prefer', get_string('prefer', 'teams'), $ynoptions);
        $mform->addHelpButton('prefer', 'prefer', 'teams');


        $mform->addElement('header', 'generalhdr', get_string('general'));
        // hideattendees
        $ynoptions = [0 =>'False', 1=>'True'];
        $mform->addElement('select', 'hideattendees', get_string('hideattendees', 'teams'), $ynoptions);
        $mform->addHelpButton('hideattendees', 'hideattendees', 'teams');
        $mform->setDefault('hideattendees', 1); 


        // importance
        $ynoptions = ['low'=>'Low', 'normal'=>'Normal', 'high'=>'High'];
        $mform->addElement('select', 'importance', get_string('importance', 'teams'), $ynoptions);
        $mform->addHelpButton('importance', 'importance', 'teams');
        $mform->setDefault('importance', 'normal'); 


        // isallday
        $ynoptions = [0 =>'False', 1=>'True'];
        $mform->addElement('select', 'isallday', get_string('isallday', 'teams'), $ynoptions);
        $mform->addHelpButton('isallday', 'isallday', 'teams');
        $mform->setDefault('isallday', 0); 


        // isonlinemeeting
        $ynoptions = [0 =>'False', 1=>'True'];
        $mform->addElement('select', 'isonlinemeeting', get_string('isonlinemeeting', 'teams'), $ynoptions);
        $mform->addHelpButton('isonlinemeeting', 'isonlinemeeting', 'teams');
        $mform->setDefault('isonlinemeeting', 1); 

        // isreminderon
        $ynoptions = [0 =>'False', 1=>'True'];
        $mform->addElement('select', 'isreminderon', get_string('isreminderon', 'teams'), $ynoptions);
        $mform->addHelpButton('isreminderon', 'isreminderon', 'teams');
        $mform->setDefault('isreminderon', 1);

        // reminderminutesbeforestart
        $mform->addElement('text', 'reminderminutesbeforestart', get_string('reminderminutesbeforestart', 'teams'), ['size'=>'64']);
        $mform->setType('reminderminutesbeforestart', PARAM_INT);
        $mform->setDefault('reminderminutesbeforestart', 99);


        // sensitivity
        $ynoptions = ['normal'=>'Normal', 'personal'=>'Personal', 'private'=>'Private', 'confidential'=>'Confidential'];
        $mform->addElement('select', 'sensitivity', get_string('sensitivity', 'teams'), $ynoptions);
        $mform->addHelpButton('sensitivity', 'sensitivity', 'teams');
        $mform->setDefault('sensitivity', 'normal'); 


        $this->standard_coursemodule_elements();
        $this->add_action_buttons(true, false, null);

    }
}

