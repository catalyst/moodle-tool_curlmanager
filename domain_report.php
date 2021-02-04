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
 * This is an admin_externalpage 'curlmanager_domain_report' for displaying
 * the recorded outbound http requests made by moodle curl reports - aggregate by domain.
 *
 * @package   tool_curlmanager
 * @author    Xuan Gui <xuangui@catalyst-au.net>
 * @copyright Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir.'/adminlib.php');

use \tool_curlmanager\table\domain_report;

admin_externalpage_setup('curlmanager_domain_report', '', null, '', array('pagelayout' => 'report'));

$download = optional_param('download', '', PARAM_ALPHA);

$table = new domain_report('curlmanagerdomainreportstable');

$table->is_downloading($download, 'curlmanagerdomainreport', 'curlmanagerdomainreport');

global $OUTPUT, $DB;

$title = get_string('curlmanagerdomainreport', 'tool_curlmanager');
$PAGE->set_title($title);
$PAGE->set_heading($title);
$PAGE->set_pagelayout('admin');

if (!$table->is_downloading()) {
    echo $OUTPUT->header();
    echo $OUTPUT->heading($title);
}

$sum = get_string('sum', 'tool_curlmanager');
$host = get_string('host', 'tool_curlmanager');
$download = get_string('download', 'tool_curlmanager');

$table->define_baseurl($PAGE->url);
$table->sortable(true, 'hostcount', SORT_DESC);
$table->set_attribute('class', 'generaltable generalbox table-sm');
$table->define_columns([
    'hostcount',
    'host'
]);
$table->no_sorting('download');
$table->define_headers([
    $sum,
    $host
]);

$fields = 'hostcount, host';
$from = "(SELECT sum(count) AS hostcount, host
          FROM {tool_curlmanager}
          GROUP BY host) AS A";
$where = '1 = 1';
$params = [];

$table->set_sql($fields, $from, $where, $params);
$table->out(30, true);

if (!$table->is_downloading()) {
    echo $OUTPUT->footer();
}

