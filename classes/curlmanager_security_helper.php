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

use \core\files\curl_security_helper;
use core_component;

defined('MOODLE_INTERNAL') || die;

class curlmanager_security_helper extends curl_security_helper
{

    /**
     * url_is_blocked.
     * Override parent method url_is_blocked.
     * Inject the curl http request logging.
     *
     * @param string $urlstring the URL to check.
     * @return bool true if the URL is blocked or invalid and false if the URL is not blocked.
     */
    public function url_is_blocked($urlstring) {

        // Log the http request.
        if ($this->log_curl_http_requests($urlstring) === false) {
            return true;
        }

        // Call parent method url_is_blocked.
        return parent::url_is_blocked($urlstring);
    }

    /**
     * log_curl_http_requests.
     *
     * @param string $urlstring the URL to check.
     * @return bool true if logged. false if url is invalid.
     */
    private function log_curl_http_requests($urlstring) : bool {

        global $DB;

        // Try to parse the URL to get the 'host' and 'port' components.
        try {
            $url = new \moodle_url($urlstring);
            $host = $url->get_host();
        } catch (\moodle_exception $e) {
            // Moodle exception is thrown if the $urlstring is invalid.
            return false;
        }

        $codepath = '';
        $rootcodepath = '';
        $trace = debug_backtrace();
        if (isset($trace[3]['file'])) {
            $rootcodepath = $trace[3]['file'];
            $codepath = str_replace('/siteroot', '', $trace[3]['file']);
        }

        $plugin = $this->getcomponentbycodepath($rootcodepath);
        if ($plugin === false) {
            $plugin = '';
        }

        // Suggest to deduplicate on host, plugin and codepath.
        // Check if the host, plugin and codepath exists already.
        // Add a new record if not exist.
        // Otherwise update the reocrd with count+1 and timeupdated field.
        $record = $DB->get_records('tool_curlmanager',
                                    ['host' => $host, 'plugin' => $plugin, 'codepath' => $codepath],
                                    '',
                                    'id, count'
                                );

        if (count($record) > 0) {
            $record = current($record);
            $data = new \stdClass();
            $data->id = $record->id;
            $data->count = $record->count + 1;
            $data->timeupdated = time();

            $DB->update_record('tool_curlmanager', $data);

        } else {
            $data = new \stdClass();
            $data->plugin = $plugin;
            $data->codepath = $codepath;
            $data->url = $urlstring;
            $data->host = $host;
            $data->count = 1;
            $data->timecreated = time();
            $data->timeupdated = time();

            $DB->insert_record('tool_curlmanager', $data);
        }

        return true;
    }

    /**
     * Get component name by code path.
     *
     * @param string $codepath
     * @return string $component or bool if component not found.
     * @throws \dml_exception
     */
    private function getcomponentbycodepath(string $codepath) {

        // Remove the file name from code path.
        $codepath = dirname($codepath);

        $componentinfo = core_component::get_component_list();

        foreach ($componentinfo as $components) {

            foreach ($components as $componentname => $componentpath) {
                if (strstr($codepath, $componentpath)) {
                    return $componentname;
                }
            }
        }

        return false;
    }
}
