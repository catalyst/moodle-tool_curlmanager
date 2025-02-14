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

namespace tool_curlmanager;

use advanced_testcase;
use moodle_url;

/**
 * Tests curlmanager_security_helper
 *
 * @package   tool_curlmanager
 * @author    Matthew Hilton <matthewhilton@catalyst-au.net>
 * @copyright Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers    \tool_curlmanager\curlmanager_security_helper
 */
class curlmanager_security_helper_test extends advanced_testcase {
    /**
     * Tests get_reference function
     */
    public function test_get_reference() {
        $url1 = new moodle_url('https://test.localhost/test.php/abc?param=123');
        $url2 = new moodle_url('https://test.localhost/test.php/abc?param=456');

        $codepath = "this is a dummy code path";

        $ref1 = curlmanager_security_helper::get_reference($url1, $codepath, false, false);
        $ref2 = curlmanager_security_helper::get_reference($url2, $codepath, false, false);

        // Because they are the same path (excluding query params)
        // the reference should be the same.
        $this->assertSame($ref1, $ref2);

        // However, if the codepath is different, they are different.
        $ref3 = curlmanager_security_helper::get_reference($url2, $codepath . "extra", false, false);
        $this->assertNotSame($ref1, $ref3);

        // And if the blocking is different, they are different.
        $ref4 = curlmanager_security_helper::get_reference($url2, $codepath, true, false);
        $this->assertNotSame($ref1, $ref4);

        $ref5 = curlmanager_security_helper::get_reference($url2, $codepath, false, true);
        $this->assertNotSame($ref1, $ref5);
    }

    /**
     * Provides blocked URL scenarios
     * @return array
     */
    public static function url_is_blocked_provider(): array {
        return [
            'nothing enabled - not blocked' => [
                'allowlist' => '',
                'denylist' => '',
                'allowlistenabled' => false,
                'url' => 'https://test.localhost',
                'expectedblocked' => false,
            ],
            'not blocked by core denylist' => [
                'allowlist' => '',
                'denylist' => 'other.localhost',
                'allowlistenabled' => false,
                'url' => 'https://test.localhost',
                'expectedblocked' => false,
            ],
            'not blocked by allowlist' => [
                'allowlist' => 'test.localhost',
                'denylist' => '',
                'allowlistenabled' => true,
                'url' => 'https://test.localhost',
                'expectedblocked' => false,
            ],
            'blocked by core denylist' => [
                'allowlist' => '',
                'denylist' => 'test.localhost',
                'allowlistenabled' => false,
                'url' => 'https://test.localhost',
                'expectedblocked' => true,
            ],
            'blocked by allowlist' => [
                'allowlist' => 'other.localhost',
                'denylist' => '',
                'allowlistenabled' => true,
                'url' => 'https://test.localhost',
                'expectedblocked' => true,
            ],
        ];
    }

    /**
     * Tests url_is_blocked function
     * @param string $allowlist
     * @param string $denylist
     * @param bool $allowlistenabled
     * @param string $url
     * @param bool $expectedblocked
     * @dataProvider url_is_blocked_provider
     */
    public function test_url_is_blocked(string $allowlist, string $denylist, bool $allowlistenabled,
        string $url, bool $expectedblocked) {
        global $CFG;
        $this->resetAfterTest(true);
        set_config('enabled', $allowlistenabled, 'tool_curlmanager');
        set_config('allowedhosts', $allowlist, 'tool_curlmanager');
        $CFG->curlsecurityblockedhosts = $denylist;

        $helper = new curlmanager_security_helper();
        $this->assertEquals($expectedblocked, $helper->url_is_blocked($url));
    }
}
