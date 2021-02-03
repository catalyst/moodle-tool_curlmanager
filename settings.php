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
 * moodle-tool_curlmanager settings.
 *
 * @package   tool_curlmanager
 * @author    Xuan Gui <xuangui@catalyst-au.net>
 * @copyright Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) {
    $ADMIN->add('localplugins', new admin_category('tool_curlmanager', get_string('pluginname', 'tool_curlmanager')));

    $settings = new admin_settingpage('tool_curlmanager_settings', get_string('toolcurlmanagersettings', 'tool_curlmanager'));
    $ADMIN->add('tool_curlmanager', $settings);

    $ADMIN->add('reports',
        new admin_externalpage('tool_curlmanager_report',
            get_string('toolcurlmanagereport', 'tool_curlmanager'),
            new moodle_url('/admin/tool/curlmanager/report.php')
        ));
}
