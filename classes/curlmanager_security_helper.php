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

use core\files\curl_security_helper_base;
use core\files\curl_security_helper;

class curlmanager_security_helper extends curl_security_helper_base {
    /**
     * url_is_blocked.
     *
     * @param string $urlstring the URL to check.
     * @return bool true if the URL is blocked or false if the URL is allowed.
     */
    public function url_is_blocked($urlstring) : bool {

        // Log the http request.
        return $this->log_curl_http_requests($urlstring);
    }

    /**
     * log_curl_http_requests.
     *
     * @param string $urlstring the URL to check.
     * @return bool true if blocked. false if url is allowed.
     */
    private function log_curl_http_requests(string $urlstring) : bool {

        global $DB, $CFG;

        // Try to parse the URL to get the 'host' and 'port' components.
        try {
            $url = new \moodle_url($urlstring);
            $host = $url->get_host();
        } catch (\moodle_exception $e) {
            // Moodle exception is thrown if the $urlstring is invalid.
            return true;
        }

        // Check if the host is in allowed list.
        $returnvalue = $this->host_is_allowed($host);

        // Call moodle curl_security_helper method url_is_blocked.
        $curlsecurityhelper = new curl_security_helper();
        $urlblocked = $curlsecurityhelper->url_is_blocked($urlstring);

        // TODO at Some Point™ this will need to be changed when this gets true unit tests.
        // For now, let's stop it from busting core tests.
        $log = get_config('tool_curlmanager', 'loggingenabled') && !PHPUNIT_TEST;
        if ($log) {
            $rootcodepath = '';
            $trace = debug_backtrace();
            $formattedbacktrace = format_backtrace(debug_backtrace(), true);
            $lasttrace = count($trace) - 1;
            if (isset($trace[$lasttrace]['file'])) {
                $rootcodepath = $trace[$lasttrace]['file'];
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
                ['host' => $host, 'plugin' => $plugin],
                '',
                'id, count'
            );

            if (count($record) > 0) {
                $record = current($record);
                $data = new \stdClass();
                $data->id = $record->id;
                $data->count = $record->count + 1;
                $data->codepath = $formattedbacktrace;
                $data->urlallowed = $returnvalue['allowed'] ? 1 : 0;
                $data->urlblocked = $urlblocked ? 1 : 0;
                $data->timeupdated = time();
                $DB->update_record('tool_curlmanager', $data);

            } else {
                $data = new \stdClass();
                $data->plugin = $plugin;
                $data->codepath = $formattedbacktrace;
                $data->url = $urlstring;
                $data->host = $host;
                $data->urlallowed = $returnvalue['allowed'] ? 1 : 0;
                $data->urlblocked = $urlblocked ? 1 : 0;
                $data->count = 1;
                $data->timecreated = time();
                $data->timeupdated = time();
                $DB->insert_record('tool_curlmanager', $data);
            }
        }

        // If allow host is enabled and the host is not in the allowed host list, return true.
        if ($returnvalue['allowhostenabled'] && $returnvalue['allowed'] === false) {
            return true;
        }

        return false;
    }

    /**
     * Check if allowed host settings is enabled and if a host is in allowhost list.
     *
     * @param $host
     * @return array
     * @throws \dml_exception
     */
    private function host_is_allowed($host) {

        $returnvalue = [];

        $settings = get_config('tool_curlmanager');

        if (!$settings->enabled) {
            $returnvalue['allowhostenabled'] = false;
        } else {
            $returnvalue['allowhostenabled'] = true;
        }

        // Get an array of allowed hosts.
        $allowedhosts = $this->get_allowed_hosts($settings->allowedhosts);

        // Check if the host exists in the list of allowed hosts.
        if (in_array($host, $allowedhosts)) {
            $returnvalue['allowed'] = true;
        } else {
            $returnvalue['allowed'] = false;
        }

        return $returnvalue;
    }

    /**
     * get_allowed_hosts.
     *
     * @param $allowedhosts
     * @return array - an array of allowed hosts.
     */
    private function get_allowed_hosts($allowedhosts) {
        if (empty($allowedhosts)) {
            return [];
        }
        return array_filter(array_map('trim', explode("\n", $allowedhosts)), function($entry) {
            return !empty($entry);
        });
    }

    /**
     * Get component name by code path.
     *
     * @param string $codepath
     * @return string $component or bool if component not found.
     * @throws \dml_exception
     */
    private function getcomponentbycodepath(string $codepath) {

        if (empty($codepath)) {
            return false;
        }

        // Remove the file name from code path.
        $codepath = dirname($codepath);

        $componentinfo = helper::get_component_list();

        foreach ($componentinfo as $components) {

            foreach ($components as $componentname => $componentpath) {
                if (!empty($componentpath) && strstr($codepath, $componentpath)) {
                    return $componentname;
                }
            }
        }

        return false;
    }

    /**
     * Returns a string message describing a blocked URL. E.g. 'This URL is blocked'.
     *
     * @return string the string error.
     */
    public function get_blocked_url_string() {
        return get_string('curlsecurityurlblocked', 'admin');
    }
}
