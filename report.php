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
 * This is an admin_externalpage 'curlmanager_report' for displaying
 * the recorded outbound http requests made by moodle curl reports.
 *
 * @package   tool_curlmanager
 * @author    Xuan Gui <xuangui@catalyst-au.net>
 * @copyright Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir.'/adminlib.php');

use \tool_curlmanager\table\report;

admin_externalpage_setup('curlmanager_report',
                        '',
                        null,
                        '',
                        array('pagelayout' => 'report')
                    );

$viewdomain = optional_param('domain', false, PARAM_TEXT);

$download = optional_param('download', '', PARAM_ALPHA);

$table = new report('curlmanagerreportstable');

$table->is_downloading($download, 'curlmanagerreport', 'curlmanagerreport');

global $OUTPUT, $DB;

$title = get_string('curlmanagerreport', 'tool_curlmanager');
$PAGE->set_title($title);
$PAGE->set_heading($title);
$PAGE->set_pagelayout('admin');

if (!$table->is_downloading()) {
    echo $OUTPUT->header();
    echo $OUTPUT->heading($title);
}

$count = get_string('count', 'tool_curlmanager');
$plugin = get_string('plugin', 'tool_curlmanager');
$url = get_string('url', 'tool_curlmanager');
$path = get_string('codepath', 'tool_curlmanager');
$urlallowed = get_string('urlallowed', 'tool_curlmanager');
$urlblocked = get_string('urlblocked', 'tool_curlmanager');
$timecreated = get_string('timecreated', 'tool_curlmanager');
$timeupdated = get_string('timeupdated', 'tool_curlmanager');
$download = get_string('download', 'tool_curlmanager');

$table->define_baseurl($PAGE->url);
$table->sortable(true, 'count', SORT_DESC);
$table->set_attribute('class', 'generaltable generalbox table-sm');
$table->define_columns([
    'count',
    'plugin',
    'url',
    'codepath',
    'urlallowed',
    'urlblocked',
    'timecreated',
    'timeupdated'
]);
$table->no_sorting('download');
$table->define_headers([
    $count,
    $plugin,
    $url,
    $path,
    $urlallowed,
    $urlblocked,
    $timecreated,
    $timeupdated
]);

$fields = 'id, count, plugin, url, codepath, urlallowed, urlblocked, timecreated, timeupdated';
$from = "mdl_tool_curlmanager";
if ($viewdomain) {
    $where = "host = ?";
    $params = [$viewdomain];
} else {
    $where = '1 = 1';
    $params = [];
}

$table->set_sql($fields, $from, $where, $params);
$table->out(30, true);

if (!$table->is_downloading()) {
    echo $OUTPUT->footer();
}

