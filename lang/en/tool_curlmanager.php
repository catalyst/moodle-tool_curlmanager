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
 * Lang pack for tool_curlmanager
 *
 * @package   tool_curlmanager
 * @author    Xuan Gui <xuangui@catalyst-au.net>
 * @copyright Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

$string['pluginname'] = 'Curl manager';
$string['privacy:metadata'] = 'The tool curl manager plugin contains no user specific data.';

// Settings.
$string['curlmanagersettings'] = 'Curl manager settings';
$string['curlmanagerreport'] = 'Curl manager report';
$string['curlmanagerdomainreport'] = 'Curl manager domain report';
$string['settings:general'] = 'General curl manager settings';
$string['settings:enabled'] = 'Allowed hosts enabled';
$string['settings:enabled_description'] = '';
$string['settings:allowedhosts'] = 'List of allowed hosts';
$string['settings:allowedhosts_description'] = 'Please specify one allowed host only for each line.';
$string['settings:purgedataperiod'] = 'Purge data not updated in ';
$string['settings:purgedataperiod_description'] = 'The data not updated in the period specified above will be purged';

// Curl manager report header.
$string['count'] = '#';
$string['plugin'] = 'Plugin';
$string['url'] = 'URL';
$string['codepath'] = 'Path';
$string['urlallowed'] = "URL Allowed";
$string['urlblocked'] = "URL Blocked";
$string['timecreated'] = 'Time Created';
$string['timeupdated'] = 'Time Updated';
$string['download'] = 'Download';
$string['yes'] = 'Yes';
$string['no'] = 'No';
$string['resetallcurlstatistics'] = 'Reset all statistics';
$string['areyousuretodeleteallrecords'] = 'Are you sure to delete all Curl manager report records?';

// Curl manager domain report header.
$string['sum'] = '#';
$string['host'] = 'Domain';

// Events.
$string['event:logresetstatsevent'] = 'Reset statistics';

// Tasks.
$string['purgeolddata'] = 'Purge old data';
