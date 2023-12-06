<?php
// This file is part of the TEAMS plugin for Moodle - http://moodle.org/
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
 * Internal library of functions for module TEAMS
 *
 * All the TEAMS specific functions, needed to implement the module
 * logic, should go here. Never include this file from your lib.php!
 *
 * @package    mod_TEAMS
 * @copyright  2015 UC Regents
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/mod/teams/lib.php');

// Constants.
// Meeting status.
define('TEAMS_MEETING_EXPIRED', 0);
define('TEAMS_MEETING_EXISTS', 1);

// Recurrence type options.
define('TEAMS_RECURRINGTYPE_NOTIME', 0);
define('TEAMS_RECURRINGTYPE_DAILY', 1);
define('TEAMS_RECURRINGTYPE_WEEKLY', 2);
define('TEAMS_RECURRINGTYPE_MONTHLY', 3);
define('TEAMS_RECURRINGTYPE_YEARLY', 4);

// Recurring monthly repeat options.
define('TEAMS_MONTHLY_REPEAT_OPTION_DAY', 1);
define('TEAMS_MONTHLY_REPEAT_OPTION_WEEK', 2);

// Recurring monthly repeat options.
define('TEAMS_YEARLY_REPEAT_OPTION_MONTH', 1);


// Recurring end date options.
define('TEAMS_END_DATE_OPTION_BY', 1);
define('TEAMS_END_DATE_OPTION_AFTER', 2);
define('TEAMS_END_DATE_OPTION_NO_END', 3);




