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
 * Class report implements processing of columns
 *
 * - Convert unix timestamp columns to human time.
 * - Adds a button to download a record.
 */
class report extends \table_sql {

    /**
     * Formatting column url that has URLs as links.
     *
     * @param stdObject $record fieldset object of db table with field blockeduri
     * @return string HTML e.g. <a href="url">url</a>
     */
    protected function col_url($record) {
        return $this->format_uri($record->url);
    }

    /**
     * Format a uri
     *
     * @param string $uri Unsafe uri data
     * @param string $label The label for the URL
     * @param int $size How many chars to show
     * @return string HTML e.g. <a href="documenturi">documenturi</a>
     */
    private function format_uri($uri, $label = '', $size = 80) {
        global $CFG;
        if (!$uri) {
            return '-';
        }
        if (filter_var($uri, FILTER_VALIDATE_URL) === false) {
            return s($uri);
        }

        if (empty($label)) {
            $label = $uri;
        }
        $label = str_replace($CFG->wwwroot, '', $uri);
        $label = ltrim($label, '/');
        $label = shorten_text($label, $size, true);
        $label = s($label);

        return \html_writer::link($uri, $label);
    }

    /**
     * Format url allowed column.
     *
     * @param $record
     * @return string
     */
    protected function col_urlallowed($record) {
        if ((int)$record->urlallowed === 1) {
            return get_string('yes', 'tool_curlmanager');
        } else {
            return get_string('no', 'tool_curlmanager');
        }
    }

    /**
     * Format url blcoked column.
     *
     * @param $record
     * @return string
     */
    protected function col_urlblocked($record) {
        if ((int)$record->urlblocked === 1) {
            return get_string('yes', 'tool_curlmanager');
        } else {
            return get_string('no', 'tool_curlmanager');
        }
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
}
