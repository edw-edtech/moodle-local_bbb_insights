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
 * Secure recording download controller for local_bbb_insights.
 *
 * @package     local_bbb_insights
 * @copyright   2026 Emanuel Dickson Wanyonyi <wanyonyi.d.emanuel@gmail.com>
 * @author      Emanuel Dickson Wanyonyi <wanyonyi.d.emanuel@gmail.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');

require_login();
$context = context_system::instance();

// Check if download capability enforcement is enabled.
$enforcecap = get_config('local_bbb_insights', 'enforce_download_capability');
if ($enforcecap) {
    require_capability('local/bbb_insights:downloadrecordings', $context);
}

$id        = required_param('id', PARAM_INT);
$timestamp = required_param('ts', PARAM_INT);
$token     = required_param('token', PARAM_ALPHANUM);

// Validate signed token.
$isvalid = \local_bbb_insights\analytics_manager::validate_secure_download_token(
    $id,
    $USER->id,
    $timestamp,
    $token
);

if (!$isvalid) {
    throw new moodle_exception('invalid_token', 'local_bbb_insights');
}

$recording = $DB->get_record('local_bbb_insights_recordings', ['id' => $id], '*', MUST_EXIST);

// Redirect to verified media URL or playback endpoint.
if (!empty($recording->playurl)) {
    redirect($recording->playurl);
}

// Fallback notice if URL is unavailable.
redirect(
    new moodle_url('/local/bbb_insights/recordings.php'),
    get_string('no_records_found', 'local_bbb_insights'),
    null,
    \core\output\notification::NOTIFY_WARNING
);

