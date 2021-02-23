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

namespace tool_curlmanager\task;

defined('MOODLE_INTERNAL') || die();

/**
 * Purge old data.
 *
 * @package   tool_curlmanager
 * @author    Xuan Gui <xuangui@catalyst-au.net>
 * @copyright Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class purge_old_data extends \core\task\scheduled_task {

    public function get_name() {
        return get_string('purgeolddata', 'tool_curlmanager');
    }

    public function execute() {
        global $DB;

        $config = get_config('tool_curlmanager');
        if (empty($config)) {
            return;
        }

        mtrace("Start purging curlmanager data on ". date("Y-m-d H:i:s"));
        $DB->execute('DELETE FROM {tool_curlmanager} WHERE timeupdated < :purgedate',
                ['purgedate' => (time() - (int)$config->purgedataperiod)]);
        }

        mtrace("Purged data on " . date("Y-m-d H:i:s"));
    }
}
