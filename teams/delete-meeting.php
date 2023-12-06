<?php

require_once('../../config.php');
require_once("classes/TeamClass.php");
global $DB, $USER, $CFG;
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->dirroot.'/calendar/lib.php');

require_login();


if (is_siteadmin()) {
	if (isset($_GET['instance'])) {

		$instance = $_GET['instance'];
		$eventid = $DB->get_record('event', array("instance" => $instance));
		$get_teams = $DB->get_record('teams', array('id' => $instance));
		
		$TeamClass_obj = new TeamClass();
    	$get_events = $TeamClass_obj->delete_meeting($get_teams);
    	$DB->delete_records('course_modules', array('instance' => $instance));
		$DB->delete_records('teams', array('id' => $instance));
		
		$event = calendar_event::load($eventid);
	    $event->delete(true);

		admin_externalpage_setup('purgecaches');
		$returnurl = $CFG->wwwroot.'/course/view.php?id='.$get_teams->course;
		$form = new core_admin\form\purge_caches(null, ['returnurl' => $returnurl]);

		if ($form) {
	        purge_caches();
	        $message = "Meeting deleted";
		} 
	}
}


redirect($returnurl, $message, \core\output\notification::NOTIFY_SUCCESS);

