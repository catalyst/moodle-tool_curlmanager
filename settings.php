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
    $ADMIN->add('tools', new admin_category('tool_curlmanager', get_string('pluginname', 'tool_curlmanager')));

    $settings = new admin_settingpage('curlmanager_settings', get_string('curlmanagersettings', 'tool_curlmanager'));
    $ADMIN->add('tool_curlmanager', $settings);

    if (file_exists($CFG->dirroot . '/totara')) {
        $ADMIN->add('tool_curlmanager',
            new admin_externalpage('curlmanager_report',
                get_string('curlmanagerreport', 'tool_curlmanager'),
                new moodle_url('/admin/tool/curlmanager/report.php')
            ));

        $ADMIN->add('tool_curlmanager',
            new admin_externalpage('curlmanager_domain_report',
                get_string('curlmanagerdomainreport', 'tool_curlmanager'),
                new moodle_url('/admin/tool/curlmanager/domain_report.php')
            ));
    } else {
        $ADMIN->add('reports',
            new admin_externalpage('curlmanager_report',
                get_string('curlmanagerreport', 'tool_curlmanager'),
                new moodle_url('/admin/tool/curlmanager/report.php')
            ));

        $ADMIN->add('reports',
            new admin_externalpage('curlmanager_domain_report',
                get_string('curlmanagerdomainreport', 'tool_curlmanager'),
                new moodle_url('/admin/tool/curlmanager/domain_report.php')
            ));
    }
}
