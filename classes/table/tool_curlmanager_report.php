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
 * Class tool_curlmanager_report implements processing of columns
 *
 * - Convert unix timestamp columns to human time.
 * - Adds a button to download a record.
 */
class tool_curlmanager_report extends \table_sql {

    /**
     * Embeds a link to a drilldown table showing only 1 violation class
     *
     * @param stdObject $record fieldset object of db table with field timecreated
     * @return string Link to drilldown table
     */
    protected function col_failcounter($record) {
        // Get blocked URI, and set as param for page if clicked on.
        $url = new \moodle_url('/local/csp/csp_report.php',
            [
                'blockeddomain' => $record->blockeddomain,
                'blockeddirective' => $record->violateddirective
            ]
        );
        return \html_writer::link($url, $record->failcounter);
    }

    /**
     * Formatting unix timestamps in column named timecreated to human readable time.
     *
     * @param stdObject $record fieldset object of db table with field timecreated
     * @return string human readable time
     */
    protected function col_timecreated($record) {
        if ($record->timecreated) {
            return userdate($record->timecreated, get_string('strftimedatetimeshort'))
                . '<br>'
                . format_time(time() - $record->timecreated);
        } else {
            return  '-';
        }
    }

    /**
     * Formatting unix timestamps in column named timeupdated to human readable time.
     *
     * @param stdObject $record fieldset object of db table with field timeupdated
     * @return string human readable time
     */
    protected function col_timeupdated($record) {
        if ($record->timeupdated) {
            return userdate($record->timeupdated, get_string('strftimedatetimeshort'))
                . '<br>'
                . format_time(time() - $record->timecreated);
        } else {
            return  '-';
        }
    }

    /**
     * Draw a link to the original table report URI with a param instructing to download the record.
     *
     * @param stdObject $record
     * @return string HTML link.
     */
    protected function col_download($record)
    {
        global $OUTPUT;

    }
}
