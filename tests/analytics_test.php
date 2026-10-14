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
 * Standalone verification tests for local_bbb_insights analytics manager.
 *
 * @package     local_bbb_insights
 * @copyright   2026 Emanuel Dickson Wanyonyi <wanyonyi.d.emanuel@gmail.com>
 * @author      Emanuel Dickson Wanyonyi <wanyonyi.d.emanuel@gmail.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

declare(strict_types=1);

require_once __DIR__ . '/../classes/analytics_manager.php';

function get_site_identifier(): string {
    return 'mock_secret_site_identifier_12345';
}

function get_config(string $plugin, string $name) {
    if ($name === 'storage_quota_gb') {
        return 50;
    }
    return null;
}

function assert_true(bool $condition, string $message): void {
    if (!$condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

// 1. Verify byte formatting.
assert_true(
    \local_bbb_insights\analytics_manager::format_bytes(0) === '0 B',
    '0 bytes should format to 0 B'
);
assert_true(
    \local_bbb_insights\analytics_manager::format_bytes(1024) === '1 KB',
    '1024 bytes should format to 1 KB'
);
assert_true(
    \local_bbb_insights\analytics_manager::format_bytes(1048576) === '1 MB',
    '1048576 bytes should format to 1 MB'
);
assert_true(
    \local_bbb_insights\analytics_manager::format_bytes(1073741824) === '1 GB',
    '1073741824 bytes should format to 1 GB'
);

// 2. Verify duration formatting.
assert_true(
    \local_bbb_insights\analytics_manager::format_duration(0) === '0m',
    '0 seconds should format to 0m'
);
assert_true(
    \local_bbb_insights\analytics_manager::format_duration(180) === '3m',
    '180 seconds should format to 3m'
);
assert_true(
    \local_bbb_insights\analytics_manager::format_duration(3600) === '1h 0m',
    '3600 seconds should format to 1h 0m'
);
assert_true(
    \local_bbb_insights\analytics_manager::format_duration(5400) === '1h 30m',
    '5400 seconds should format to 1h 30m'
);

// 3. Verify download token generation and validation.
$recordingid = 42;
$userid = 7;
$timestamp = time();
$token = hash_hmac('sha256', "rec:{$recordingid}:user:{$userid}:ts:{$timestamp}", get_site_identifier());

assert_true(
    \local_bbb_insights\analytics_manager::validate_secure_download_token($recordingid, $userid, $timestamp, $token),
    'Valid token should pass verification'
);

assert_true(
    !\local_bbb_insights\analytics_manager::validate_secure_download_token($recordingid, $userid, $timestamp - 10000, $token),
    'Expired token should fail verification'
);

assert_true(
    !\local_bbb_insights\analytics_manager::validate_secure_download_token($recordingid, $userid, $timestamp, 'tampered_token'),
    'Tampered token should fail verification'
);

echo "All local_bbb_insights tests passed successfully!\n";

