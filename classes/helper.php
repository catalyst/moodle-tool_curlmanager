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
 * curlmanager_security_helper
 *
 * @package   tool_curlmanager
 * @author    Xuan Gui <xuangui@catalyst-au.net>
 * @copyright Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace tool_curlmanager;

use core_component;

defined('MOODLE_INTERNAL') || die;

class helper
{
    /**
     * get_component_list.
     * This is a copy from core component as Totara 12 doesn't have this function required for this plugin to work.
     *
     * Returns a list of frankenstyle component names and their paths, for all components (plugins and subsystems).
     *
     * E.g.
     *  [
     *      'mod' => [
     *          'mod_forum' => FORUM_PLUGIN_PATH,
     *          ...
     *      ],
     *      ...
     *      'core' => [
     *          'core_comment' => COMMENT_SUBSYSTEM_PATH,
     *          ...
     *      ]
     * ]
     *
     * @return array an associative array of components and their corresponding paths.
     */
    public static function get_component_list() : array {
        $components = [];
        // Get all plugins.
        foreach (core_component::get_plugin_types() as $plugintype => $typedir) {
            $components[$plugintype] = [];
            foreach (core_component::get_plugin_list($plugintype) as $pluginname => $plugindir) {
                $components[$plugintype][$plugintype . '_' . $pluginname] = $plugindir;
            }
        }
        // Get all subsystems.
        foreach (core_component::get_core_subsystems() as $subsystemname => $subsystempath) {
            $components['core']['core_' . $subsystemname] = $subsystempath;
        }
        return $components;
    }
}
