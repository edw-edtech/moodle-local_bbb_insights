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
 * Main executive overview dashboard for local_bbb_insights.
 *
 * @package     local_bbb_insights
 * @copyright   2026 Emanuel Dickson Wanyonyi <wanyonyi.d.emanuel@gmail.com>
 * @author      Emanuel Dickson Wanyonyi <wanyonyi.d.emanuel@gmail.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');

require_login();
$context = context_system::instance();
require_capability('local/bbb_insights:view', $context);

$PAGE->set_url(new moodle_url('/local/bbb_insights/index.php'));
$PAGE->set_context($context);
$PAGE->set_title(get_string('pluginname', 'local_bbb_insights'));
$PAGE->set_heading(get_string('pluginname', 'local_bbb_insights'));

$action = optional_param('action', '', PARAM_ALPHA);

if ($action === 'sync' && confirm_sesskey()) {
    require_capability('local/bbb_insights:manage', $context);
    \local_bbb_insights\analytics_manager::sync_insights();
    redirect(
        new moodle_url('/local/bbb_insights/index.php'),
        get_string('sync_success', 'local_bbb_insights'),
        null,
        \core\output\notification::NOTIFY_SUCCESS
    );
}

$stats          = \local_bbb_insights\analytics_manager::get_overview_stats();
$recentsessions = \local_bbb_insights\analytics_manager::get_recent_sessions(10);
$topcourses     = \local_bbb_insights\analytics_manager::get_top_courses(5);

/** @var \local_bbb_insights\output\renderer $renderer */
$renderer = $PAGE->get_renderer('local_bbb_insights');

echo $OUTPUT->header();
echo \local_bbb_insights\output\renderer::render_nav_header('index');
echo $renderer->render_dashboard($stats, $recentsessions, $topcourses);
echo $OUTPUT->footer();

