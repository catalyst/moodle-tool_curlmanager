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
namespace tool_curlmanager\event;

/**
 * curlmanager_stats_reset
 *
 * @package   tool_curlmanager
 * @author    Xuan Gui <xuangui@catalyst-au.net>
 * @copyright Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class curlmanager_stats_reset extends \core\event\base {

    /**
     * Create instance of event.
     */
    public static function create_log() {
        $data = [
            'context' => \context_system::instance()
        ];
        return self::create($data);
    }

    /**
     * Init method.
     *
     * @return void
     */
    protected function init() {
        $this->data['crud'] = 'd';
        $this->data['edulevel'] = self::LEVEL_OTHER;
    }

    /**
     * Returns description of what happened.
     *
     * @return string
     */
    public function get_description() : string {
        return "The user with id '$this->userid' deleted all data records in tool_curlmanager table.";
    }

    /**
     * Return localised event name.
     *
     * @return string
     */
    public static function get_name() : string {
        return get_string('event:logresetstatsevent', 'tool_curlmanager');
    }
}
