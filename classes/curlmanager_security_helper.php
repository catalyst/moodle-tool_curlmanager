<?php

/**
 * moodle-tool_curlmanager version.
 *
 * @package   tool_curlmanager
 * @author    Xuan Gui <xuangui@catalyst-au.net>
 * @copyright Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace tool_curlmanager;

use \core\files\curl_security_helper;

defined('MOODLE_INTERNAL') || die;

class curlmanager_security_helper extends curl_security_helper
{

    /**
     * url_is_blocked
     * Override parent method url_is_blocked.
     * Inject the curl http request logging.
     *
     * @param string $urlstring the URL to check.
     * @return bool true if the URL is blocked or invalid and false if the URL is not blocked.
     */
    public function url_is_blocked($urlstring) {

        // Log the http request
        if ($this->log_curl_http_requests($urlstring) === false) {
            return true;
        }

        // Call parent method url_is_blocked
        return parent::url_is_blocked($urlstring);
    }

    /**
     * log_curl_http_requests
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
        $trace = debug_backtrace();
        if (isset($trace[3]['file'])) {
            $codepath = str_replace('/siteroot', '', $trace[3]['file']);
        }

        $plugin = $this->getpluginbycodepath($codepath);
        if ($plugin === false) {
            $plugin = '';
        }

        // Suggest to deduplicate on host, plugin and codepath.
        // Check if the host, plugin and codepath exists already.
        // Add a new record if not exist.
        // Otherwise update the reocrd with count+1 and timeupdated field.
        $record = $DB->get_records('tool_curlmanager', ['host' => $host, 'plugin' => $plugin, 'codepath' => $codepath], '', 'id, count');

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

            $DB->insert_record('tool_curlmanager', $data);
        }

        return true;
    }

    /**
     * Get plugin name by code path.
     *
     * @param string $codepath
     * @return string $plugin or bool if plugin not found
     * @throws \dml_exception
     */
    private function getpluginbycodepath(string $codepath) {

        global $DB;

        $folders = array_filter(explode("/", $codepath));
        array_pop($folders);
        $folders = array_values($folders);

        $numberoffolders = count($folders);

        $plugins = [];
        switch ($numberoffolders) {
            case 1:
                $plugins[] = 'core_' .$folders[0];
                break;
            case 2:
                $plugins[] = $folders[0]. '_' . $folders[1];
                $plugins[] = 'core_' .$folders[0];
                break;
            case 3:
                $plugins[] = $folders[0]. '_' . $folders[1];
                $plugins[] = $folders[0]. '_' . $folders[2];
                $plugins[] = $folders[1]. '_' . $folders[2];
                $plugins[] = 'core_' .$folders[0];
                break;
            case 4 || 5:
                $plugins[] = $folders[0]. '_' . $folders[1];
                $plugins[] = $folders[0]. '_' . $folders[2];
                $plugins[] = $folders[0]. '_' . $folders[3];
                $plugins[] = $folders[1]. '_' . $folders[2];
                $plugins[] = $folders[1]. '_' . $folders[3];
                $plugins[] = $folders[2]. '_' . $folders[3];
                $plugins[] = 'core_' .$folders[0];
                break;
            default:
                break;
        }

        // This definitely add overhead to curl request.
        // However if doesn't exits, will not return correct plugin.
        foreach ($plugins as $plugin) {
            if ($DB->record_exists('config_plugins', ['plugin' => $plugin])) {
                return $plugin;
            }
        }

        return false;
    }
}
