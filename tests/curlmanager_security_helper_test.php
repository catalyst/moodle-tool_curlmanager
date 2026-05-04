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
final class curlmanager_security_helper_test extends advanced_testcase {
    /**
     * Helper to call the private getcomponentbycodepath method via reflection.
     *
     * @param array $trace synthetic debug_backtrace()-style array
     * @return string|false
     */
    private function call_getcomponentbycodepath(array $trace) {
        $helper = new curlmanager_security_helper();
        $rm = new \ReflectionMethod(curlmanager_security_helper::class, 'getcomponentbycodepath');
        if (PHP_VERSION_ID < 80100) {
            $rm->setAccessible(true);
        }
        return $rm->invoke($helper, $trace);
    }

    /**
     * Data provider for test_getcomponentbycodepath.
     *
     * @return array
     */
    public static function getcomponentbycodepath_provider(): array {
        global $CFG;

        // Use known stable plugin paths relative to dirroot.
        $dirroot = $CFG->dirroot;

        return [
            'empty trace returns false' => [
                'trace'    => [],
                'expected' => false,
            ],
            'frame with no file key returns false' => [
                'trace'    => [['function' => 'some_function']],
                'expected' => false,
            ],
            'core lib file returns false' => [
                'trace'    => [
                    ['file' => $dirroot . '/lib/setup.php'],
                ],
                'expected' => false,
            ],
            'file inside tool_curlmanager detected' => [
                'trace'    => [
                    ['file' => $dirroot . '/admin/tool/curlmanager/classes/curlmanager_security_helper.php'],
                ],
                'expected' => 'tool_curlmanager',
            ],
            'file in subdirectory of plugin detected' => [
                'trace'    => [
                    ['file' => $dirroot . '/admin/tool/curlmanager/tests/curlmanager_security_helper_test.php'],
                ],
                'expected' => 'tool_curlmanager',
            ],
            'outermost plugin in trace wins' => [
                // Entry point (last frame, reversed to first in iteration) is tool_curlmanager;
                // inner frame is tool_task. The outermost (entry point) should be returned.
                'trace'    => [
                    ['file' => $dirroot . '/admin/tool/task/classes/task_logger.php'], // inner (index 0)
                    ['file' => $dirroot . '/admin/tool/curlmanager/lib.php'], // outer (index 1)
                ],
                'expected' => 'tool_curlmanager',
            ],
            'only core entry point with plugin inner frame returns plugin' => [
                // Entry point is a core file, but a plugin is found deeper in.
                'trace'    => [
                    ['file' => $dirroot . '/admin/tool/curlmanager/classes/curlmanager_security_helper.php'], // inner
                    ['file' => $dirroot . '/lib/setup.php'], // entry
                ],
                'expected' => 'tool_curlmanager',
            ],
        ];
    }

    /**
     * Tests getcomponentbycodepath detects the correct plugin from a trace.
     *
     * This covers detection working regardless of dirroot (before and after the
     * /public/ subdirectory move) because both the component paths and the trace
     * file paths are normalised relative to $CFG->dirroot before comparison.
     *
     * @dataProvider getcomponentbycodepath_provider
     * @param array $trace synthetic trace frames
     * @param string|false $expected expected component name or false
     */
    public function test_getcomponentbycodepath(array $trace, $expected): void {
        $this->assertEquals($expected, $this->call_getcomponentbycodepath($trace));
    }

    /**
     * Tests get_reference function
     */
    public function test_get_reference(): void {
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
    public function test_url_is_blocked(
        string $allowlist,
        string $denylist,
        bool $allowlistenabled,
        string $url,
        bool $expectedblocked
    ): void {
        global $CFG;
        $this->resetAfterTest(true);
        set_config('enabled', $allowlistenabled, 'tool_curlmanager');
        set_config('allowedhosts', $allowlist, 'tool_curlmanager');
        $CFG->curlsecurityblockedhosts = $denylist;

        $helper = new curlmanager_security_helper();
        $this->assertEquals($expectedblocked, $helper->url_is_blocked($url));
    }
}
