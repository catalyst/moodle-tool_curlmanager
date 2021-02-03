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

class tool_curlmanager_security_helper extends curl_security_helper
{

    /**
     * log_outbound_http_requests
     *
     * @param string $urlstring the URL to check.
     * @return bool true if the URL is blacklisted or invalid and false if the URL is not blacklisted.
     */
    public function log_outbound_http_requests($urlstring) : bool {

         // Try to parse the URL to get the 'host' and 'port' components.
        try {
            $url = new \moodle_url($urlstring);
            $parsed = [];
            $parsed['host'] = $url->get_host();
        } catch (\moodle_exception $e) {
            // Moodle exception is thrown if the $urlstring is invalid.
            return false;
        }
    }
}
