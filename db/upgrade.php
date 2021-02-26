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
 * tool_curlmanager upgrade code
 *
 * @package   tool_curlmanager
 * @author    Xuan Gui <xuangui@catalyst-au.net>
 * @copyright Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * @param int $oldversion the version we are upgrading from
 * @return bool result
 */
function xmldb_tool_curlmanager_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2021022500) {

        // Change codepath column from char to text
        $table = new xmldb_table('tool_curlmanager');

        $field = new xmldb_field('codepath');
        $field->set_attributes(XMLDB_TYPE_TEXT, null, null, null, null, null);

        if ($dbman->field_exists($table, $field)) {
            $dbman->change_field_type($table, $field, $continue = true, $feedback = true);
        }

        upgrade_plugin_savepoint(true, 2021022500, 'tool', 'curlmanager');
    }

    return true;
}
