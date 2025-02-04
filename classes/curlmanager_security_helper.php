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
use moodle_url;
use Throwable;

class curlmanager_security_helper extends curl_security_helper_base {
    /**
     * url_is_blocked.
     *
     * @param string $urlstring the URL to check.
     * @return bool true if the URL is blocked or false if the URL is allowed.
     */
    public function url_is_blocked($urlstring): bool {
        try {
            $curlsecurityhelper = new curl_security_helper();
            $blockedbymoodle = $curlsecurityhelper->url_is_blocked($urlstring);

            $url = new \moodle_url($urlstring);
            $blockedbyus = !$this->host_is_allowed($url->get_host());

            // Log the result.
            $this->log_curl_http_requests($url, $blockedbymoodle, $blockedbyus);

            return $blockedbymoodle || $blockedbyus;
        } catch (Throwable $e) {
            return (bool) get_config('tool_curlmanager', 'blockonerror');
        }
    }

    /**
     * Get DB reference to a given log.
     * @param moodle_url $url
     * @param string $codepath the stacktrace that called the above url
     * @param bool $blockedbymoodle if this was blocked by moodles security settings
     * @param bool $blockedbyus if this was blocked by our security settings
     * @return string sha hash reference to used to index record.
     */
    public static function get_reference(moodle_url $url, string $codepath, bool $blockedbymoodle, bool $blockedbyus): string {
        return hash('sha256', 'url:' . self::sanitise_url($url) . 'path:' . $codepath
         . 'blockedbyus:' .  $blockedbyus . 'blockedbymoodle:' . $blockedbymoodle);
    }

    /**
     * Sanitise url. This is mainly to keep URL intact but remove any query params.
     * @param moodle_url $url
     * @return string
     */
    private static function sanitise_url(moodle_url $url): string {
        return $url->get_scheme() . '://' . $url->get_host() . $url->get_path(true);
    }

    /**
     * log_curl_http_requests.
     *
     * @param moodle_url $url URL to log.
     * @param bool $blockedbymoodle if was blocked by moodles denylist
     * @param bool $blockedbyus if was blocked by our allowlist
     */
    private function log_curl_http_requests(moodle_url $url, bool $blockedbymoodle, bool $blockedbyus) {
        global $DB;

        $shoudlog = get_config('tool_curlmanager', 'loggingenabled');
        if (!$shoudlog) {
            return;
        }

        // Build stacktrace.
        $rootcodepath = '';
        $trace = debug_backtrace();
        $formattedbacktrace = format_backtrace(debug_backtrace(), true);
        $lasttrace = count($trace) - 1;
        if (isset($trace[$lasttrace]['file'])) {
            $rootcodepath = $trace[$lasttrace]['file'];
        }

        // Parse plugin.
        $plugin = $this->getcomponentbycodepath($rootcodepath);
        if ($plugin === false) {
            $plugin = '';
        }

        // Get path (without any query params).
        $urlstring = self::sanitise_url($url);
        $reference = self::get_reference($url, $formattedbacktrace, $blockedbymoodle, $blockedbyus);

        // Upsert log using reference to de-dup.
        $record = $DB->get_record('tool_curlmanager', ['reference' => $reference], 'id,count', IGNORE_MISSING);

        if (!empty($record)) {
            $DB->update_record('tool_curlmanager', [
                'id' => $record->id,
                'count' => $record->count + 1,
                'timeupdated' => time(),
            ]);
        } else {
            $DB->insert_record('tool_curlmanager', [
                'reference' => $reference,
                'plugin' => $plugin,
                'codepath' => $formattedbacktrace,
                'url' => $urlstring,
                'host' => $url->get_host(),
                'count' => 1,
                'urlallowed' => !$blockedbyus,
                'urlblocked' => $blockedbymoodle,
                'timeupdated' => time(),
                'timecreated' => time(),
            ]);
        }
    }

    /**
     * Check if allowed host settings is enabled and if a host is in allowhost list.
     *
     * @param string $host
     * @return bool true if is allowed, else false
     */
    private function host_is_allowed($host): bool {
        $settings = get_config('tool_curlmanager');

        if (!$settings->enabled) {
            return true;
        }

        return in_array($host, $this->get_allowed_hosts($settings->allowedhosts));
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
