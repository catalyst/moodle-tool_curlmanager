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

use tool_curlmanager\curlmanager_security_helper;

/**
 * @param int $oldversion the version we are upgrading from
 * @return bool result
 */
function xmldb_tool_curlmanager_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2021022500) {

        // Change codepath column from char to text.
        $table = new xmldb_table('tool_curlmanager');

        $field = new xmldb_field('codepath');
        $field->set_attributes(XMLDB_TYPE_TEXT, null, null, null, null, null);

        if ($dbman->field_exists($table, $field)) {
            $dbman->change_field_type($table, $field, $continue = true, $feedback = true);
        }

        upgrade_plugin_savepoint(true, 2021022500, 'tool', 'curlmanager');
    }

    if ($oldversion < 2021031700) {

        // Change url column from char to text.
        $table = new xmldb_table('tool_curlmanager');

        $field = new xmldb_field('url');
        $field->set_attributes(XMLDB_TYPE_TEXT, null, null, null, null, null);

        if ($dbman->field_exists($table, $field)) {
            $dbman->change_field_type($table, $field, $continue = true, $feedback = true);
        }

        upgrade_plugin_savepoint(true, 2021031700, 'tool', 'curlmanager');
    }

    if ($oldversion < 2025020401) {

        // Add the new reference field, originally as null allowed.
        $table = new xmldb_table('tool_curlmanager');
        $field = new xmldb_field('reference', XMLDB_TYPE_CHAR, '255', null, null, null, null, 'timeupdated');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);

            // Calculate the reference for all existing records.
            $records = $DB->get_recordset('tool_curlmanager', [], '', 'id,url,codepath,urlallowed,urlblocked');

            foreach ($records as $record) {
                $ref = curlmanager_security_helper::get_reference(new moodle_url($record->url),
                    $record->codepath, $record->urlblocked, !$record->urlallowed);

                // If a record already has this ref, just merge the counts
                // (we are about to add a unique index, so we cannot have duplicates).
                if ($otherrecord = $DB->get_record('tool_curlmanager', ['reference' => $ref])) {
                    $DB->update_record('tool_curlmanager', [
                        'id' => $record->id,
                        'reference' => $ref,
                        // Re-query the count, as the current $record->count may be outdated.
                        'count' => $otherrecord->count + $DB->get_field('tool_curlmanager', 'count', ['id' => $record->id]),
                    ]);

                    $DB->delete_records('tool_curlmanager', ['id' => $otherrecord->id]);
                } else {
                    $DB->update_record('tool_curlmanager', [
                        'id' => $record->id,
                        'reference' => $ref,
                    ]);
                }
            }

            // Now all records have the reference, make the field NOT NULL.
            $field = new xmldb_field('reference', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, 'timeupdated');
            $dbman->change_field_notnull($table, $field);
        }

        // Add unique index onto this reference.
        $index = new xmldb_index('mdl_tool_curlmanager_reference', XMLDB_INDEX_UNIQUE, ['reference']);
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }

        // Drop the other indexes, they are not necessary.
        $index = new xmldb_index('mdl_tool_curlmanager_plugin', XMLDB_INDEX_NOTUNIQUE, ['plugin']);
        if ($dbman->index_exists($table, $index)) {
            $dbman->drop_index($table, $index);
        }

        $index = new xmldb_index('mdl_tool_curlmanager_host', XMLDB_INDEX_NOTUNIQUE, ['host']);
        if ($dbman->index_exists($table, $index)) {
            $dbman->drop_index($table, $index);
        }

        $index = new xmldb_index('mdl_tool_curlmanager_count', XMLDB_INDEX_NOTUNIQUE, ['count']);
        if ($dbman->index_exists($table, $index)) {
            $dbman->drop_index($table, $index);
        }

        // Curlmanager savepoint reached.
        upgrade_plugin_savepoint(true, 2025020401, 'tool', 'curlmanager');
    }

    return true;
}
