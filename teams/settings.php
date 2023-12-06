<?php
// This file is part of Moodle - https://moodle.org/
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
 * Adds admin settings for the plugin.
 *
 * @package     mod_teams
 * @category    admin
 * @copyright   2020 Your Name <email@example.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {


    // TenantID
    $settings->add(new admin_setting_configtext('mod_teams/TenantID',
    get_string('TenantID_title', 'mod_teams'), get_string('TenantID_desc', 'mod_teams'), null, PARAM_ALPHANUMEXT));


    // ClientID
    $settings->add(new admin_setting_configtext('mod_teams/ClientID',
    get_string('ClientID_title', 'mod_teams'), get_string('ClientID_desc', 'mod_teams'), null, PARAM_ALPHANUMEXT));


    // ClientSecret
    $settings->add(new admin_setting_configpasswordunmask('mod_teams/ClientSecret',
    get_string('ClientSecret_title', 'mod_teams'), get_string('ClientSecret_desc', 'mod_teams'), null, PARAM_ALPHANUMEXT));


    // UserId
    $settings->add(new admin_setting_configtext('mod_teams/UserId',
    get_string('UserId_title', 'mod_teams'), get_string('UserId_desc', 'mod_teams'), null, PARAM_ALPHANUMEXT));


    // base_url
    $settings->add(new admin_setting_configtext('mod_teams/base_url',
    get_string('base_url_title', 'mod_teams'), null, null, PARAM_URL));


    // base_url_auth
    $settings->add(new admin_setting_configtext('mod_teams/base_url_auth',
    get_string('base_url_auth_title', 'mod_teams'), null, null, PARAM_URL));


    // scope
    $settings->add(new admin_setting_configtext('mod_teams/scope',
    get_string('scope_title', 'mod_teams'), null, null, PARAM_URL));


    // username
    $settings->add(new admin_setting_configpasswordunmask('mod_teams/username',
    get_string('username_title', 'mod_teams'), null, null, PARAM_ALPHANUMEXT));


    // password
    $settings->add(new admin_setting_configpasswordunmask('mod_teams/password',
    get_string('password_title', 'mod_teams'), null, null, PARAM_ALPHANUMEXT));


    // grant_type
    $settings->add(new admin_setting_configtext('mod_teams/grant_type',
    get_string('grant_type_title', 'mod_teams'), null, null, PARAM_TEXT));


}





