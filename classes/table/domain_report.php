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
 * tool_curlmanager table
 *
 * @package   tool_curlmanager
 * @author    Xuan Gui <xuangui@catalyst-au.net>
 * @copyright Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_curlmanager\table;

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . '/tablelib.php');

/**
 * Class domain_report implements processing of columns
 *
 * - Convert unix timestamp columns to human time.
 * - Adds a button to download a record.
 */
class domain_report extends \table_sql {

    /**
     * Formatting column hostcount to link back to report table search by host.
     *
     * @param stdObject $record fieldset object of db table with field blockeduri
     * @return string HTML e.g. <a href="url">url</a>
     */
    protected function col_hostcount($record) {
        // Get blocked URI, and set as param for page if clicked on.
        $url = new \moodle_url('/admin/tool/curlmanager/report.php',
            [
                'domain' => $record->host,
            ]
        );
        return \html_writer::link($url, $record->hostcount);
    }
}
